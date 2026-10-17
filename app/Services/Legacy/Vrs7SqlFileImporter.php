<?php

namespace App\Services\Legacy;

use App\Models\Currency;
use App\Models\PropertyCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Vrs7SqlFileImporter
{
    private const LEGACY_TABLES = [
        'vrs7_listings',
        'vrs7_listing_photos',
        'vrs7_listing_types',
        'vrs7_listing_videos',
        'vrs7_locations',
        'vrs7_rates',
        'vrs7_bathroom_options',
        'vrs7_bedroom_options',
        'vrs7_access_options',
        'vrs7_utilities',
        'vrs7_reviews',
        'vrs7_users',
        'vrs7_bookings',
    ];

    public function import(string $filePath): array
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        ini_set('pcre.backtrack_limit', '500000000');
        ini_set('pcre.recursion_limit', '500000000');

        if (!is_readable($filePath)) {
            throw new \RuntimeException('Uploaded SQL file cannot be read.');
        }

        $this->assertRequiredLegacyTablesInFile($filePath);

        $db = DB::connection();

        $fallbackUserId = $this->resolveFallbackUserId();
        // Fallback functions for Category and Currency are replaced:
        // Euro is strictly enforced for all imported properties.
        // Property categories map 1:1 with listing types.

        $stats = [
            'property_types' => 0,
            'property_categories' => 0,
            'properties' => 0,
            'locations' => 0,
            'property_photos' => 0,
            'property_videos' => 0,
            'rates' => 0,
            'property_facilities' => 0,
            'amenities' => 0,
            'property_policies' => 0,
            'reviews' => 0,
        ];

        $propertyMap = [];
        $listingLocationMap = [];
        $legacyListings = [];

        // Build mapping dictionaries for options
        $bathroomOptionsList = [];
        $bedroomOptionsList = [];
        $accessOptionsList = [];
        $utilitiesList = [];
        $facilitiesDict = [];
        $activitiesDict = [];
        $holidayTypesDict = [];

        $legacyLocations = [];

        DB::transaction(function () use (
            $db,
            $filePath,
            $fallbackUserId,
            &$stats,
            &$propertyMap,
            &$listingLocationMap,
            &$legacyListings,
            &$legacyLocations,
            &$bathroomOptionsList,
            &$bedroomOptionsList,
            &$accessOptionsList,
            &$utilitiesList,
            &$facilitiesDict,
            &$activitiesDict,
            &$holidayTypesDict,
            &$legacyUsersMap
        ) {
            $euroCurrencyId = $this->resolveEuroCurrencyId($db);
            $this->forEachTableRow($filePath, 'vrs7_bathroom_options', function ($row) use (&$bathroomOptionsList) {
                $bathroomOptionsList[(int)$row['bathroom_option_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_bedroom_options', function ($row) use (&$bedroomOptionsList) {
                $bedroomOptionsList[(int)$row['bedroom_option_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_access_options', function ($row) use (&$accessOptionsList) {
                $accessOptionsList[(int)$row['access_option_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_utilities', function ($row) use (&$utilitiesList) {
                $utilitiesList[(int)$row['utility_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_facilities', function ($row) use (&$facilitiesDict) {
                $facilitiesDict[(int)$row['facility_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_activities', function ($row) use (&$activitiesDict) {
                $activitiesDict[(int)$row['activity_id']] = $row['title_1'] ?? '';
            });
            $this->forEachTableRow($filePath, 'vrs7_holiday_types', function ($row) use (&$holidayTypesDict) {
                $holidayTypesDict[(int)$row['holiday_type_id']] = $row['title_1'] ?? '';
            });

            $mapUserCallback = function (array $row) use (&$legacyUsersMap) {
                // Check all possible ID variants
                $legacyUserId = (int) ($row['user_id'] ?? $row['booker_id'] ?? $row['id'] ?? 0);
                if ($legacyUserId > 0) {
                    // Gather possible name fields
                    $nameCandidate = $this->nonEmpty($row['name'] ?? null)
                        ?: $this->nonEmpty($row['username'] ?? null)
                        ?: $this->nonEmpty($row['display_name'] ?? null)
                        ?: $this->nonEmpty($row['full_name'] ?? null)
                        ?: trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

                    // Only set if we haven't found a better name or if currently empty
                    // to prevent overwriting a good name with a bad booking name.
                    if ($this->nonEmpty($nameCandidate) && empty($legacyUsersMap[$legacyUserId])) {
                        $legacyUsersMap[$legacyUserId] = $nameCandidate;
                    }
                }
            };
            
            // Aggressively harvest names from all tables that could contain users/guests
            $this->forEachTableRow($filePath, 'vrs7_users', $mapUserCallback);
            $this->forEachTableRow($filePath, 'vrs7_bookings', $mapUserCallback);

            $this->forEachTableRow($filePath, 'vrs7_listing_types', function (array $row) use ($db, &$stats) {
                $legacyTypeId = (int) ($row['listing_type_id'] ?? 0);
                if ($legacyTypeId <= 0) {
                    return;
                }

                $payload = [
                    'legacy_listing_type_id' => $legacyTypeId,
                    'title' => $this->nonEmpty($row['title_1'] ?? null) ?: ('Legacy Type ' . $legacyTypeId),
                    'slug' => $this->safeSlug($this->nonEmpty($row['slug_1'] ?? null), $this->nonEmpty($row['title_1'] ?? null), $legacyTypeId),
                    'status' => ((int) ($row['active'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                // Property Types
                $typePayload = $payload;
                $typePayload['marker_file'] = $this->nullIfEmpty($row['marker_file'] ?? null);
                $typePayload['marker'] = $this->nullIfEmpty($row['marker'] ?? null);
                
                $existsType = $db->table('property_types')->where('legacy_listing_type_id', $legacyTypeId)->exists();
                if (!$existsType) $typePayload['created_at'] = now();
                $db->table('property_types')->updateOrInsert(['legacy_listing_type_id' => $legacyTypeId], $typePayload);
                $stats['property_types']++;

                // Property Categories
                $categoryPayload = [
                    'legacy_listing_type_id' => $legacyTypeId,
                    'title' => $payload['title'],
                    'slug' => $payload['slug'],
                    'status' => $payload['status'],
                    'updated_at' => now(),
                ];
                $existsCat = $db->table('property_categories')->where('legacy_listing_type_id', $legacyTypeId)->exists();
                if (!$existsCat) {
                    $categoryPayload['created_at'] = now();
                }
                $db->table('property_categories')->updateOrInsert(['legacy_listing_type_id' => $legacyTypeId], $categoryPayload);
                $stats['property_categories']++;
            });

            $listingTypeMap = $db->table('property_types')
                ->whereNotNull('legacy_listing_type_id')
                ->pluck('property_type_id', 'legacy_listing_type_id');

            $categoryMap = $db->table('property_categories')
                ->whereNotNull('legacy_listing_type_id')
                ->pluck('property_category_id', 'legacy_listing_type_id');

            $this->forEachTableRow($filePath, 'vrs7_listings', function (array $row) use (
                $db,
                $euroCurrencyId,
                $listingTypeMap,
                $categoryMap,
                $fallbackUserId,
                &$stats,
                &$propertyMap,
                &$listingLocationMap,
                &$legacyListings
            ) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                if ($legacyListingId <= 0) {
                    return;
                }

                $legacyTypeId = (int) ($row['listing_type_id'] ?? 0);
                $legacyUnitTypeId = (int) ($row['unit_type_id'] ?? 0);
                $legacyUserId = (int) ($row['user_id'] ?? 0);

                $propertyTypeId = isset($listingTypeMap[$legacyTypeId])
                    ? (int) $listingTypeMap[$legacyTypeId]
                    : $this->firstId($db, 'property_types', 'property_type_id');
                
                $propertyCategoryId = isset($categoryMap[$legacyTypeId])
                    ? (int) $categoryMap[$legacyTypeId]
                    : $this->firstId($db, 'property_categories', 'property_category_id');

                $userId = $db->table('users')->where('id', $legacyUserId)->exists() ? $legacyUserId : $fallbackUserId;
                $unitTypeId = $db->table('unit_types')->where('unit_type_id', $legacyUnitTypeId)->exists() ? $legacyUnitTypeId : null;

                $propertyStatus = $this->mapPropertyStatus((string) ($row['listing_status'] ?? ''));
                $payload = [
                    'legacy_listing_id' => $legacyListingId,
                    'legacy_location_id' => (int) ($row['location_id'] ?? 0) ?: null,
                    'user_id' => $userId,
                    'property_status' => $propertyStatus,
                    'property_main_type' => $this->mapPropertyMainType((string) ($row['listing_main_type'] ?? '')),
                    'property_type_id' => $propertyTypeId,
                    'unit_type_id' => $unitTypeId,
                    'property_category_id' => $propertyCategoryId,
                    'currency_id' => $euroCurrencyId,

                    'discount_id' => null,
                    'addon_id' => null,
                    'title' => $this->truncate($this->nonEmpty($row['title_1'] ?? null) ?: ('Legacy Property #' . $legacyListingId), 100),
                    'description' => $this->nonEmpty($row['description_1'] ?? null) ?: ($this->nonEmpty($row['summary_1'] ?? null) ?: 'Imported from legacy SQL.'),
                    'stars' => $this->mapStars($row['stars'] ?? 0),
                    'available_from' => $this->parseLegacyDate($row['available_from'] ?? null, now()->toDateString()),
                    'no_bedrooms' => $this->toTinyInt($row['no_bedrooms'] ?? 0),
                    'no_bathrooms' => $this->toTinyInt($row['no_bathrooms'] ?? 0),
                    'no_floors' => $this->toTinyInt($row['no_floors'] ?? 0),
                    'no_guests' => $this->toTinyInt(((int) ($row['max_guests'] ?? 0)) > 0 ? ($row['max_guests'] ?? 0) : ($row['min_guests'] ?? 0)),
                    'min_no_of_stay' => $this->toTinyInt($row['min_min_stay'] ?? 0),
                    'parking' => ((int) ($row['parking'] ?? 0)) > 0,
                    'car_essential' => ((int) ($row['car_essential'] ?? 0)) > 0,
                    'total_square_feet' => $this->toDecimal($row['total_square'] ?? ($row['live_square'] ?? 0)),
                    'allow_bookings' => ((int) ($row['allow_bookings'] ?? 0)) > 0,
                    'property_meta_title' => $this->truncate($this->nullIfEmpty($row['seo_title_1'] ?? null), 255),
                    'property_meta_description' => $this->truncate($this->nullIfEmpty($row['seo_description_1'] ?? null), 255),
                    'property_meta_keywords' => $this->truncate($this->nullIfEmpty($row['seo_keywords_1'] ?? null), 255),
                    'status' => $propertyStatus === 'active' ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                $exists = $db->table('properties')->where('legacy_listing_id', $legacyListingId)->exists();
                if (!$exists) {
                    $payload['created_at'] = now();
                }

                $db->table('properties')->updateOrInsert(['legacy_listing_id' => $legacyListingId], $payload);

                $propertyId = (int) $db->table('properties')
                    ->where('legacy_listing_id', $legacyListingId)
                    ->value('property_id');

                $propertyMap[$legacyListingId] = $propertyId;
                $listingLocationMap[$legacyListingId] = (int) ($row['location_id'] ?? 0);
                $legacyListings[$legacyListingId] = $row;
                $stats['properties']++;
            });

            $legacyLocations = [];
            $this->forEachTableRow($filePath, 'vrs7_locations', function (array $row) use (&$legacyLocations) {
                $locationId = (int) ($row['location_id'] ?? 0);
                if ($locationId > 0) {
                    $legacyLocations[$locationId] = $row;
                }
            });

            foreach ($propertyMap as $legacyListingId => $propertyId) {
                $legacyLocationId = $listingLocationMap[$legacyListingId] ?? 0;
                $legacyLocation = $legacyLocations[$legacyLocationId] ?? null;
                $legacyListing = $legacyListings[$legacyListingId] ?? [];
                $parts = $this->extractLocationParts($legacyLocation, $legacyLocations);

                $payload = [
                    'legacy_location_id' => $legacyLocationId ?: null,
                    'country' => $parts['country'] ?: 'Unknown',
                    'region' => $parts['region'],
                    'city' => $parts['city'],
                    'area' => $parts['area'],
                    'landmark' => null,
                    'address' => $this->nullIfEmpty($legacyListing['address'] ?? null),
                    'zip_code' => $this->nullIfEmpty($legacyListing['zip_code'] ?? null) ?: $this->nullIfEmpty($legacyLocation['zip_code'] ?? null),
                    'lat' => $this->formatLatLng($legacyListing['lat'] ?? null, $legacyLocation['lat'] ?? null),
                    'lng' => $this->formatLatLng($legacyListing['lng'] ?? null, $legacyLocation['lng'] ?? null),
                    'zoom' => max(1, (int) ($legacyListing['zoom'] ?? 12)),
                    'country_code' => $this->normalizeCountryCode($legacyLocation['country_code'] ?? null),
                    'updated_at' => now(),
                ];

                $exists = $db->table('locations')->where('property_id', $propertyId)->exists();
                if (!$exists) {
                    $payload['created_at'] = now();
                }

                $db->table('locations')->updateOrInsert(['property_id' => $propertyId], $payload);
                $stats['locations']++;

                // Process Property Policies (from vrs7_listings terms and conditions, policies, etc)
                $policiesDict = [
                    'Lease Details 1' => $legacyListing['lease_details_1'] ?? null,
                    'Lease Details 2' => $legacyListing['lease_details_2'] ?? null,
                    'Lease Details 3' => $legacyListing['lease_details_3'] ?? null,
                    'Lease Details 4' => $legacyListing['lease_details_4'] ?? null,
                    'Lease Details 5' => $legacyListing['lease_details_5'] ?? null,
                    'Terms & Conditions 1' => $legacyListing['terms_and_conditions_1'] ?? null,
                    'Terms & Conditions 2' => $legacyListing['terms_and_conditions_2'] ?? null,
                    'Terms & Conditions 3' => $legacyListing['terms_and_conditions_3'] ?? null,
                    'Policies 1' => $legacyListing['policies_1'] ?? null,
                    'Policies 2' => $legacyListing['policies_2'] ?? null,
                    'Policies 3' => $legacyListing['policies_3'] ?? null,
                    'Check In' => $this->nonEmpty($legacyListing['check_in'] ?? null) ? "Check in time: {$legacyListing['check_in']}" : null,
                    'Check Out' => $this->nonEmpty($legacyListing['check_out'] ?? null) ? "Check out time: {$legacyListing['check_out']}" : null,
                    'Security Deposit Description' => $legacyListing['security_deposit_description_1'] ?? null,
                    'Suitability' => $this->formatSuitability($legacyListing['suitability'] ?? null),
                    'Suitability Description' => $legacyListing['suitability_description_1'] ?? null,
                ];

                foreach ($policiesDict as $policyTitle => $policyDescription) {
                    if ($this->nonEmpty($policyDescription)) {
                        $db->table('property_policies')->updateOrInsert([
                            'property_id' => $propertyId,
                            'title' => $this->truncate($policyTitle, 255),
                        ], [
                            'description' => $policyDescription,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $stats['property_policies']++;
                    }
                }

                // Parse and map facilities from strings: e.g. "bathroomoption:2:1", "bathroomoption:3", "bedroomoption:..."
                $facilitiesKeys = [
                    'bathroom_options' => $bathroomOptionsList,
                    'bedroom_options' => $bedroomOptionsList,
                    'access_options' => $accessOptionsList
                ];

                foreach ($facilitiesKeys as $columnName => $titleMap) {
                    $val = $legacyListing[$columnName] ?? '';
                    if ($this->nonEmpty($val)) {
                        $parts = explode('/', $val);
                        foreach ($parts as $part) {
                            $segments = explode(':', $part);
                            if (count($segments) >= 2) {
                                $legacyId = (int)$segments[1];
                                $title = $titleMap[$legacyId] ?? null;
                                if ($title) {
                                    $db->table('property_facilities')->updateOrInsert([
                                        'property_id' => $propertyId,
                                        'title' => $this->truncate($title, 255)
                                    ], [
                                        'description' => null,
                                        'status' => 'active',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    $stats['property_facilities']++;
                                }
                            }
                        }
                    }
                }

                // Utilities -> Amenities
                $val = $legacyListing['utilities'] ?? '';
                if ($this->nonEmpty($val)) {
                    $parts = explode('/', $val);
                    foreach ($parts as $part) {
                        $segments = explode(':', $part);
                        if (count($segments) >= 2) {
                            $legacyId = (int)$segments[1];
                            $title = $utilitiesList[$legacyId] ?? null;
                            if ($title) {
                                $db->table('amenities')->updateOrInsert([
                                    'property_id' => $propertyId,
                                    'title' => $this->truncate($title, 255)
                                ], [
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $stats['amenities']++;
                            }
                        }
                    }
                }
            }

            $this->forEachTableRow($filePath, 'vrs7_listings_facilities', function (array $row) use ($db, $propertyMap, $facilitiesDict, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $facilityId = (int) ($row['facility_id'] ?? 0);
                if (!isset($propertyMap[$legacyListingId]) || !isset($facilitiesDict[$facilityId])) return;
                $propertyId = $propertyMap[$legacyListingId];
                $title = $facilitiesDict[$facilityId];
                if ($this->nonEmpty($title)) {
                    $db->table('property_facilities')->updateOrInsert([
                        'property_id' => $propertyId,
                        'title' => $this->truncate($title, 255)
                    ], [
                        'description' => null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['property_facilities']++;
                }
            });

            $this->forEachTableRow($filePath, 'vrs7_listings_utilities', function (array $row) use ($db, $propertyMap, $utilitiesList, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $utilityId = (int) ($row['utility_id'] ?? 0);
                if (!isset($propertyMap[$legacyListingId]) || !isset($utilitiesList[$utilityId])) return;
                $propertyId = $propertyMap[$legacyListingId];
                $title = $utilitiesList[$utilityId];
                if ($this->nonEmpty($title)) {
                    $db->table('amenities')->updateOrInsert([
                        'property_id' => $propertyId,
                        'title' => $this->truncate($title, 255)
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['amenities']++;
                }
            });

            $this->forEachTableRow($filePath, 'vrs7_listings_activities', function (array $row) use ($db, $propertyMap, $activitiesDict, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $activityId = (int) ($row['activity_id'] ?? 0);
                if (!isset($propertyMap[$legacyListingId]) || !isset($activitiesDict[$activityId])) return;
                $propertyId = $propertyMap[$legacyListingId];
                $title = $activitiesDict[$activityId];
                if ($this->nonEmpty($title)) {
                    $db->table('property_facilities')->updateOrInsert([
                        'property_id' => $propertyId,
                        'title' => $this->truncate($title, 255)
                    ], [
                        'description' => 'Activity',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['property_facilities']++;
                }
            });

            $this->forEachTableRow($filePath, 'vrs7_listings_holiday_types', function (array $row) use ($db, $propertyMap, $holidayTypesDict, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $holidayTypeId = (int) ($row['holiday_type_id'] ?? 0);
                if (!isset($propertyMap[$legacyListingId]) || !isset($holidayTypesDict[$holidayTypeId])) return;
                $propertyId = $propertyMap[$legacyListingId];
                $title = $holidayTypesDict[$holidayTypeId];
                if ($this->nonEmpty($title)) {
                    $db->table('amenities')->updateOrInsert([
                        'property_id' => $propertyId,
                        'title' => $this->truncate($title, 255)
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['amenities']++;
                }
            });

            $this->forEachTableRow($filePath, 'vrs7_listing_photos', function (array $row) use ($db, $propertyMap, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $legacyPhotoId = (int) ($row['listing_photo_id'] ?? 0);
                if (!$legacyPhotoId || !isset($propertyMap[$legacyListingId])) {
                    return;
                }

                $propertyId = $propertyMap[$legacyListingId];

                // Check existing photo record for this legacy_listing_photo_id
                $existingPhoto = $db->table('property_photos')->where('legacy_listing_photo_id', $legacyPhotoId)->first();
                if ($existingPhoto && !str_starts_with($existingPhoto->property_photo_file, 'http://') && !str_starts_with($existingPhoto->property_photo_file, 'https://')) {
                    $localPublic = public_path($existingPhoto->property_photo_file);
                    $localStorage = storage_path('app/public/' . $existingPhoto->property_photo_file);
                    if ((file_exists($localPublic) && filesize($localPublic) > 1000) || (file_exists($localStorage) && filesize($localStorage) > 1000)) {
                        // Already downloaded locally and file exists on disk â€” keep local path intact
                        return;
                    }
                }

                // Build full example.com CDN URL for the photo
                $photoFile = (string) ($row['listing_photo_file'] ?? '');
                $photoUrl = $this->buildLegacyPhotoUrl($photoFile, $row['ctime'] ?? null);

                $payload = [
                    'legacy_listing_photo_id' => $legacyPhotoId,
                    'property_id' => $propertyId,
                    'property_photo_file' => $this->truncate($photoUrl, 255),
                    'status' => ((int) ($row['active'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                $exists = $existingPhoto !== null;
                if (!$exists) {
                    $payload['created_at'] = now();
                }

                $db->table('property_photos')->updateOrInsert(['legacy_listing_photo_id' => $legacyPhotoId], $payload);
                $stats['property_photos']++;
            });

            $this->forEachTableRow($filePath, 'vrs7_listing_videos', function (array $row) use ($db, $propertyMap, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $legacyVideoId = (int) ($row['listing_video_id'] ?? 0);
                if (!$legacyVideoId || !isset($propertyMap[$legacyListingId])) {
                    return;
                }

                $videoType = (string) ($row['video_type'] ?? 'embed');
                if (!in_array($videoType, ['upload', 'embed'], true)) {
                    $videoType = 'embed';
                }

                // Build example.com URLs for video files
                $videoFile = $this->nullIfEmpty($row['listing_video_file'] ?? null);
                if ($videoFile && $videoType === 'upload') {
                    $videoFile = $this->buildLegacyMediaUrl('listing_videos', $videoFile, $row['ctime'] ?? null);
                }
                $posterFile = $this->nullIfEmpty($row['poster_file'] ?? null);
                if ($posterFile) {
                    $posterFile = $this->buildLegacyMediaUrl('listing_videos', $posterFile, $row['ctime'] ?? null);
                }

                $payload = [
                    'legacy_listing_video_id' => $legacyVideoId,
                    'property_id' => $propertyMap[$legacyListingId],
                    'video_type' => $videoType,
                    'video_file' => $videoFile,
                    'poster_file' => $posterFile,
                    'embed' => $this->nullIfEmpty($row['embed_1'] ?? null),
                    'status' => ((int) ($row['active'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                $exists = $db->table('property_videos')->where('legacy_listing_video_id', $legacyVideoId)->exists();
                if (!$exists) {
                    $payload['created_at'] = now();
                }

                $db->table('property_videos')->updateOrInsert(['legacy_listing_video_id' => $legacyVideoId], $payload);
                $stats['property_videos']++;
            });

            $this->forEachTableRow($filePath, 'vrs7_rates', function (array $row) use ($db, $propertyMap, &$stats) {
                $legacyListingId = (int) ($row['listing_id'] ?? 0);
                $legacyRateId = (int) ($row['rate_id'] ?? 0);
                if (!$legacyRateId || !isset($propertyMap[$legacyListingId])) {
                    return;
                }

                $legacyUnitTypeId = (int) ($row['unit_type_id'] ?? 0);
                $unitTypeId = $db->table('unit_types')->where('unit_type_id', $legacyUnitTypeId)->exists() ? $legacyUnitTypeId : null;

                $payload = [
                    'legacy_rate_id' => $legacyRateId,
                    'property_id' => $propertyMap[$legacyListingId],
                    'unit_type_id' => $unitTypeId,
                    'start_date' => $this->parseLegacyDate($row['start_date'] ?? null, now()->toDateString()),
                    'end_date' => $this->parseLegacyDate($row['end_date'] ?? null, now()->toDateString()),
                    'nightly_rate' => $this->toDecimal($row['nightly_rate'] ?? 0),
                    'nightly_rate_weekend' => ((float) ($row['nightly_rate_weekend'] ?? 0)) > 0 ? $this->toDecimal($row['nightly_rate_weekend']) : null,
                    'min_stay' => max(1, (int) ($row['min_stay'] ?? 1)),
                    'changeover_day' => $this->mapChangeoverDay($row['changeover_day'] ?? null),
                    'persons' => max(1, (int) ($row['persons'] ?? 1)),
                    'extra_person_price_night' => $this->toDecimal($row['extra_person_price_night'] ?? 0),
                    'status' => ((int) ($row['active'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'is_special_offer' => ((int) ($row['special_offer'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'is_rate_overwrite' => ((int) ($row['rate_overwrite'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'updated_at' => now(),
                ];

                $exists = $db->table('rates')->where('legacy_rate_id', $legacyRateId)->exists();
                if (!$exists) {
                    $payload['created_at'] = now();
                }

                $db->table('rates')->updateOrInsert(['legacy_rate_id' => $legacyRateId], $payload);
                $stats['rates']++;
            });
            $this->forEachTableRow($filePath, 'vrs7_reviews', function (array $row) use ($db, $propertyMap, &$stats, &$legacyUsersMap) {
                $legacyReviewId = (int) ($row['review_id'] ?? 0);
                $legacyListingId = (int) ($row['listing_id'] ?? 0);

                if (!$legacyReviewId || !isset($propertyMap[$legacyListingId])) {
                    return;
                }

                // Reviews can be attached via user_id or reviewer_id in heritage systems
                $reviewUserId = (int) ($row['user_id'] ?? 0);
                $reviewerId = (int) ($row['reviewer_id'] ?? 0);

                // Attempt to fetch name from our heavily populated cross-table mapped users list
                // We prefer reviewerId if valid, as it's often the direct author link in vrs7_reviews
                $userName = null;
                if ($reviewerId > 0 && isset($legacyUsersMap[$reviewerId])) {
                    $userName = $legacyUsersMap[$reviewerId];
                } elseif ($reviewUserId > 0 && isset($legacyUsersMap[$reviewUserId])) {
                    $userName = $legacyUsersMap[$reviewUserId];
                }

                // Fallbacks: If all memory maps fail, scrape ANY string identifier off the review
                if (!$userName) {
                    $userName = current(array_filter([
                        $this->nonEmpty($row['name'] ?? null),
                        $this->nonEmpty($row['display_name'] ?? null),
                        $this->nonEmpty($row['reviewer_name'] ?? null),
                        $this->nonEmpty($row['title'] ?? null), // Extreme fallback sometimes users put name in title
                        'Anonymous'
                    ]));
                }

                $payload = [
                    'review_id' => $legacyReviewId,
                    'property_id' => $propertyMap[$legacyListingId],
                    'avatar' => null,
                    'name' => $this->truncate($userName, 80),
                    'details' => $this->nonEmpty($row['body'] ?? null),
                    'rating_stars' => (int) ($row['rating_stars'] ?? 0),
                    'status' => ((int) ($row['active'] ?? 0)) === 1 ? 'active' : 'inactive',
                    'updated_at' => $this->parseLegacyDateTime($row['mtime'] ?? null, now()->toDateTimeString()),
                ];

                $exists = $db->table('reviews')->where('review_id', $legacyReviewId)->exists();
                if (!$exists) {
                    $payload['created_at'] = $this->parseLegacyDateTime($row['ctime'] ?? null, now()->toDateTimeString());
                }

                $db->table('reviews')->updateOrInsert(['review_id' => $legacyReviewId], $payload);
                $stats['reviews']++;
            });
        });

        return [
            'stats' => $stats,
            'property_map' => $propertyMap,
            'legacy_listings' => $legacyListings,
            'legacy_locations' => $legacyLocations,
        ];
    }

    private function assertRequiredLegacyTablesInFile(string $filePath): void
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded SQL file for validation.');
        }

        $found = [];

        try {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\s*(CREATE\s+TABLE|INSERT\s+INTO)\s+`?([a-zA-Z0-9_]+)`?/i', $line, $matches) !== 1) {
                    continue;
                }

                $table = strtolower($matches[2]);
                if (in_array($table, self::LEGACY_TABLES, true)) {
                    $found[$table] = true;
                }

                if (count($found) === count(self::LEGACY_TABLES)) {
                    break;
                }
            }
        } finally {
            fclose($handle);
        }

        $missing = array_values(array_diff(self::LEGACY_TABLES, array_keys($found)));
        if (!empty($missing)) {
            throw new \RuntimeException('SQL file is missing required legacy tables: ' . implode(', ', $missing));
        }
    }

    private function resolveFallbackUserId(): int
    {
        $user = User::query()->where('role', 'Admin')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        if (!$user) {
            throw new \RuntimeException('No user found. Please create at least one admin/user before import.');
        }

        return (int) $user->id;
    }

    private function resolveEuroCurrencyId($db): int
    {
        $euro = $db->table('currencies')->where('currency_code', 'EUR')->first();
        if ($euro) {
            return (int) $euro->currency_id;
        }

        // Check if there's any default currency already
        $hasDefault = $db->table('currencies')->where('is_default', 'yes')->exists();

        // Auto-create EUR currency
        $id = $db->table('currencies')->insertGetId([
            'title'         => 'Euro',
            'currency_code' => 'EUR',
            'currency_rate' => 1.00,
            'is_default'    => $hasDefault ? 'no' : 'yes',
            'status'        => 'active',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return (int) $id;
    }


    /**
     * Build a full example.com CDN URL for a listing photo.
     * Format: https://cdn.example.com/images/w.1280/h.853/c.1/mr.1/d.listing_photos/sd.{YYYY-MM}/i.{filename}
     */
    private function buildLegacyPhotoUrl(string $filename, ?string $ctime): string
    {
        if (empty($filename)) {
            return '';
        }

        $dateSegment = $this->extractDateSegment($ctime);

        return 'https://cdn.example.com/images/w.1280/h.853/c.1/mr.1/d.listing_photos/sd.' . $dateSegment . '/i.' . $filename;
    }

    /**
     * Build a full example.com CDN URL for any media type (videos, posters, etc.).
     */
    private function buildLegacyMediaUrl(string $directory, string $filename, ?string $ctime): string
    {
        if (empty($filename)) {
            return '';
        }

        $dateSegment = $this->extractDateSegment($ctime);

        return 'https://cdn.example.com/images/w.1280/h.853/c.1/mr.1/d.' . $directory . '/sd.' . $dateSegment . '/i.' . $filename;
    }

    /**
     * Extract YYYY-MM from a datetime string for the sd. URL segment.
     */
    private function extractDateSegment(?string $datetime): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return date('Y-m'); // fallback to current month
        }

        try {
            return Carbon::parse($datetime)->format('Y-m');
        } catch (\Throwable $e) {
            return date('Y-m');
        }
    }

    private function forEachTableRow(string $filePath, string $table, callable $callback): void
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded SQL file.');
        }

        // These limits are now set once at the beginning of the import() method.

        $capturing = false;
        $statement = '';

        try {
            while (($line = fgets($handle)) !== false) {
                if (!$capturing) {
                    if (preg_match('/^\s*INSERT\s+INTO\s+`?' . preg_quote($table, '/') . '`?/i', $line) === 1) {
                        $capturing = true;
                        $statement = $line;

                        if (str_ends_with(trim($line), ';')) {
                            $this->dispatchStatementRows($statement, $table, $callback);
                            $capturing = false;
                            $statement = '';
                        }
                    }

                    continue;
                }

                $statement .= $line;
                if (str_ends_with(trim($line), ';')) {
                    $this->dispatchStatementRows($statement, $table, $callback);
                    $capturing = false;
                    $statement = '';
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function dispatchStatementRows(string $statement, string $table, callable $callback): void
    {
        $rows = $this->parseInsertStatement($statement, $table);
        foreach ($rows as $row) {
            $callback($row);
        }
    }

    private function parseInsertStatement(string $statement, string $table): array
    {
        $statement = trim($statement);
        $pattern = '/INSERT\s+INTO\s+`?' . preg_quote($table, '/') . '`?(?:\s*\((.*?)\))?\s*VALUES\s*(.*);\s*$/is';

        if (preg_match($pattern, $statement, $matches) !== 1) {
            return [];
        }

        $columnsPart = trim($matches[1] ?? '');
        if ($columnsPart !== '') {
            $columns = array_map(function ($column) {
                return trim($column, " \t\n\r\0\x0B`");
            }, explode(',', $columnsPart));
        } else {
            $columns = $this->getLegacyTableColumns($table);
            if (empty($columns)) {
                return [];
            }
        }

        $tuples = $this->extractTuples($matches[2] ?? '');
        $rows = [];

        foreach ($tuples as $tuple) {
            $values = $this->parseTupleValues($tuple);
            if (count($values) !== count($columns)) {
                continue;
            }

            $rows[] = array_combine($columns, $values);
        }

        return $rows;
    }

    private function getLegacyTableColumns(string $table): array
    {
        $columnsMap = [
            'vrs7_rates' => ['rate_id', 'ctime', 'mtime', 'title_1', 'title_2', 'title_3', 'title_4', 'title_5', 'start_date', 'end_date', 'nightly_rate', 'nightly_rate_weekend', 'weekly_rate', 'monthly_rate', 'min_stay', 'listing_id', 'active', 'special_offer', 'changeover_day', 'persons', 'extra_person_price_night', 'extra_person_price_week', 'extra_person_price_month', 'unit_type_id', 'rate_overwrite'],
            'vrs7_reviews' => ['review_id', 'ctime', 'mtime', 'title', 'body', 'body_reply', 'user_id', 'listing_id', 'name', 'email', 'location', 'age', 'display_name', 'active', 'rating_stars', 'reviewer_id', 'votes_up', 'votes_down', 'arrival_date', 'stay_month', 'stay_year'],
        ];

        return $columnsMap[$table] ?? [];
    }

    private function extractTuples(string $valuesPart): array
    {
        $tuples = [];
        $length = strlen($valuesPart);
        $depth = 0;
        $inQuote = false;
        $escaped = false;
        $buffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesPart[$i];

            if ($inQuote) {
                $buffer .= $char;
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === "'") {
                    $inQuote = false;
                }
                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                $buffer .= $char;
                continue;
            }

            if ($char === '(') {
                $depth++;
                if ($depth === 1) {
                    $buffer = '';
                    continue;
                }
            }

            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $tuples[] = $buffer;
                    $buffer = '';
                    continue;
                }
            }

            if ($depth >= 1) {
                $buffer .= $char;
            }
        }

        return $tuples;
    }

    private function parseTupleValues(string $tuple): array
    {
        $values = [];
        $length = strlen($tuple);
        $inQuote = false;
        $escaped = false;
        $buffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $tuple[$i];

            if ($inQuote) {
                $buffer .= $char;
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === "'") {
                    $inQuote = false;
                }
                continue;
            }

            if ($char === "'") {
                $inQuote = true;
                $buffer .= $char;
                continue;
            }

            if ($char === ',') {
                $values[] = $this->parseSqlLiteral($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $values[] = $this->parseSqlLiteral($buffer);

        return $values;
    }

    private function parseSqlLiteral(string $value)
    {
        $value = trim($value);

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        $length = strlen($value);
        if ($length >= 2 && $value[0] === "'" && $value[$length - 1] === "'") {
            $inner = substr($value, 1, -1);
            $inner = str_replace(["\\\\", "\\'", "\\n", "\\r", "\\t", "\\0"], ["\\", "'", "\n", "\r", "\t", "\0"], $inner);
            return $inner;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }

    private function extractLocationParts(?array $legacyLocation, array $allLocations): array
    {
        if (!$legacyLocation) {
            return ['country' => null, 'region' => null, 'city' => null, 'area' => null];
        }

        $titles = [];
        $current = $legacyLocation;
        $maxDepth = 12;

        while ($current && $maxDepth-- > 0) {
            $title = $this->nonEmpty($current['title_1'] ?? null);
            if ($title) {
                $titles[] = $title;
            }

            $parentId = (int) ($current['location_parent_id'] ?? 0);
            if ($parentId <= 0 || !isset($allLocations[$parentId])) {
                break;
            }

            $current = $allLocations[$parentId];
        }

        $titles = array_values(array_reverse(array_unique($titles)));

        return [
            'country' => $titles[0] ?? null,
            'region' => $titles[1] ?? null,
            'city' => $titles[2] ?? ($titles[1] ?? null),
            'area' => $this->nullIfEmpty(end($titles) ?: null),
        ];
    }

    private function normalizeCountryCode($code): ?string
    {
        $code = strtoupper(trim((string) $code));

        return strlen($code) === 2 ? $code : null;
    }

    private function safeSlug(?string $slug, ?string $title, int $id): string
    {
        $candidate = $slug ?: ($title ?: ('legacy-type-' . $id));
        return Str::slug($candidate);
    }

    private function mapPropertyStatus(string $legacyStatus): string
    {
        $legacyStatus = strtolower($legacyStatus);

        return match ($legacyStatus) {
            'inactive' => 'inactive',
            'active' => 'active',
            'pending' => 'pending',
            'sold' => 'sold',
            'withdrawn' => 'withdrawn',
            default => 'others',
        };
    }

    private function mapPropertyMainType(string $legacyType): string
    {
        $legacyType = strtolower($legacyType);

        return match ($legacyType) {
            'sale' => 'sale',
            'rent' => 'rent',
            default => 'others',
        };
    }

    private function mapStars($legacyStars): string
    {
        $stars = (int) $legacyStars;
        $stars = max(1, min(5, $stars));

        return $stars . ($stars === 1 ? ' Star' : ' Stars');
    }

    private function mapChangeoverDay($legacyDay): ?string
    {
        $day = (int) $legacyDay;

        return [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ][$day] ?? null;
    }

    private function parseLegacyDate($value, string $fallback): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function parseLegacyDateTime($value, string $fallback): string
    {
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return $fallback;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function formatSuitability(?string $suitability): ?string
    {
        if (empty($suitability)) {
            return null;
        }

        $parts = explode('/', $suitability);
        $result = [];

        foreach ($parts as $part) {
            $segments = explode(':', $part);
            if (count($segments) !== 2) continue;

            $key = $segments[0];
            $value = $segments[1];

            switch ($key) {
                case 'longterm':
                    $result[] = 'Long term rentals : ' . ($value == '1' ? 'Yes' : 'No');
                    break;
                case 'children':
                    if ($value == '0') $valStr = 'Not suitable';
                    elseif ($value == '1') $valStr = 'Yes, great for kids';
                    else $valStr = 'Yes ( children over 5 yo. )';
                    $result[] = 'Children : ' . $valStr;
                    break;
                case 'eldery':
                    $result[] = 'Elderly or infirm : ' . ($value == '1' ? 'Yes' : 'Not suitable');
                    break;
                case 'wheelchair':
                    $result[] = 'Wheelchair : ' . ($value == '1' ? 'Yes' : 'No');
                    break;
                case 'pets':
                    if ($value == '0') $valStr = 'No';
                    elseif ($value == '1') $valStr = 'Yes, pets welcome';
                    else $valStr = 'Some pets, please enquire';
                    $result[] = 'Pets : ' . $valStr;
                    break;
                case 'smoke':
                    if ($value == '0') $valStr = 'No smoking';
                    else $valStr = 'Yes, smokers welcome';
                    $result[] = 'Smoke : ' . $valStr;
                    break;
            }
        }

        return empty($result) ? null : implode(' , ', $result);
    }

    private function toTinyInt($value): int
    {
        $number = (int) $value;
        return max(0, min(255, $number));
    }

    private function toDecimal($value): float
    {
        return round((float) $value, 2);
    }

    private function formatLatLng($primary, $fallback): ?string
    {
        $primary = trim((string) $primary);
        if ($primary !== '' && (float) $primary !== 0.0) {
            return $primary;
        }

        $fallback = trim((string) $fallback);
        if ($fallback !== '' && (float) $fallback !== 0.0) {
            return $fallback;
        }

        return null;
    }

    private function firstId($db, string $table, string $idColumn): int
    {
        $id = $db->table($table)->orderBy($idColumn)->value($idColumn);
        if (!$id) {
            throw new \RuntimeException("Missing required data in [$table].");
        }

        return (int) $id;
    }

    private function nonEmpty($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullIfEmpty($value): ?string
    {
        return $this->nonEmpty($value);
    }

    private function truncate(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::limit($value, $length, '');
    }
}

