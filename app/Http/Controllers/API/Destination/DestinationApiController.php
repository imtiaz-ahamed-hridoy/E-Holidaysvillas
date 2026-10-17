<?php

namespace App\Http\Controllers\API\Destination;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class DestinationApiController extends Controller
{
    /**
     * Get list of destinations (grouped by city) with popular areas.
     */
    public function cities(Request $request): JsonResponse
    {
        // 1. Get properties with relations
        $properties = Property::with([
            'location',
            'propertyType',
            'photos' => function ($query) {
                $query->where('status', 'active');
            }
        ])
            ->where('status', 'active')
            ->whereHas('location', function ($query) {
                $query->whereNotNull('city')->where('city', '!=', '');
            })
            ->get();

        // 2. Group by city name ONLY for a flat world-wide list
        $citiesGrouped = $properties->groupBy(function ($property) {
            return trim($property->location->city);
        });

        $destinations = [];

        foreach ($citiesGrouped as $cityName => $propertiesInCity) {
            // A. Derive Subtitle (e.g., "65 Luxury Villas")
            $typeCounts = $propertiesInCity->groupBy('property_type_id')->map(function ($group) {
                return [
                    'title' => $group->first()->propertyType->title ?? 'Property',
                    'count' => $group->count()
                ];
            })->sortByDesc('count');

            $topType = $typeCounts->first();
            $typeName = $topType['title'] ?? 'Property';
            $totalCount = $propertiesInCity->count();

            // Subtitle pluralization
            $pluralName = $typeName;
            if ($totalCount > 1) {
                if (strtolower($typeName) === 'property') {
                    $pluralName = 'Properties';
                } elseif (!str_ends_with(strtolower($typeName), 's')) {
                    $pluralName .= 's';
                }
            }
            $subtitle = $totalCount . ' ' . $pluralName;

            // B. Extract Tags (top 3 unique meta keywords)
            $tags = $propertiesInCity->pluck('property_meta_keywords')
                ->flatMap(function ($keywords) {
                    return $keywords ? explode(',', $keywords) : [];
                })
                ->map(fn($tag) => trim($tag))
                ->filter()
                ->unique()
                ->take(3)
                ->values()
                ->toArray();

            // C. Find popular areas (San Antonio 22 VILLAS format)
            $areasData = $propertiesInCity->groupBy(function ($prop) {
                return $prop->location->area;
            })->filter(function ($areaGroup, $areaName) {
                return !empty($areaName);
            })->map(function ($areaGroup, $areaName) use ($typeName) {
                $count = $areaGroup->count();
                $label = $typeName;
                if ($count > 1) {
                    if (strtolower($typeName) === 'property') {
                        $label = 'Properties';
                    } elseif (!str_ends_with(strtolower($typeName), 's')) {
                        $label .= 's';
                    }
                }
                return [
                    'name' => $areaName,
                    'count' => $count,
                    'label' => strtoupper($count . ' ' . $label) // e.g. "22 VILLAS"
                ];
            })->sortByDesc('count')->take(4)->values()->toArray();

            // D. Get cover image and description
            $image = null;
            $description = null;

            foreach ($propertiesInCity as $prop) {
                if (!$image && $prop->photos->isNotEmpty()) {
                    $image = \App\Helpers\Helper::imageUrl($prop->photos->first()->property_photo_file);
                }
                if (!$description && !empty($prop->description)) {
                    $description = strip_tags($prop->description);
                    $description = strlen($description) > 160 ? substr($description, 0, 157) . '...' : $description;
                }
                if ($image && $description) break;
            }

            $destinations[] = [
                'city' => $cityName,
                'subtitle' => $subtitle,
                'description' => $description ?? 'Discover beautiful properties in ' . $cityName . '.',
                'tags' => $tags,
                'image' => $image,
                'areas' => $areasData,
                'total_properties' => $totalCount
            ];
        }

        // Sort by popularity (most properties first)
        usort($destinations, function ($a, $b) {
            return $b['total_properties'] <=> $a['total_properties'];
        });

        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        $offset = ($page * $perPage) - $perPage;

        // Use array_values to ensure clean sequential numeric keys for JSON array conversion
        $paginatedData = array_values(array_slice($destinations, $offset, $perPage));

        $paginated = new LengthAwarePaginator(
            $paginatedData,
            count($destinations),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($paginated->total() > 0) {
            return response()->json([
                'status' => 'success',
                'data' => $paginated
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No destinations found.'
            ], 404);
        }
    }

    /**
     * Get details for a specific city, including its areas and properties.
     */
    public function cityDetail(Request $request, string $city): JsonResponse
    {
        // Decode city name in case it's URL encoded
        $decodedCity = urldecode($city);

        // 1. Fetch ALL active properties in this city to get accurate Area list and total counts
        $allProperties = Property::with(['location', 'photos'])
            ->where('status', 'active')
            ->whereHas('location', function ($query) use ($decodedCity) {
                $query->where('city', 'LIKE', $decodedCity);
            })
            ->get();

        if ($allProperties->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No properties found for this city.'
            ], 404);
        }

        // 2. Group by Area to build the detailed area section
        $areas = $allProperties->groupBy(function ($prop) {
            return $prop->location->area;
        })->filter(function ($areaGroup, $areaName) {
            return !empty($areaName);
        })->map(function ($areaGroup, $areaName) {
            // Find a cover image and description for the area
            $coverImage = null;
            $description = null;

            foreach ($areaGroup as $prop) {
                if (!$coverImage && $prop->photos->isNotEmpty()) {
                    $coverImage = \App\Helpers\Helper::imageUrl($prop->photos->first()->property_photo_file);
                }
                if (!$description && !empty($prop->description)) {
                    $description = strip_tags($prop->description);
                    $description = strlen($description) > 150 ? substr($description, 0, 147) . '...' : $description;
                }
                if ($coverImage && $description) {
                    break;
                }
            }

            return [
                'title' => $areaName,
                'image' => $coverImage,
                'short_description' => $description ?? 'Fantastic properties located in ' . $areaName . '.',
                'property_count' => $areaGroup->count()
            ];
        })->values()->sortByDesc('property_count')->toArray();

        // 3. Paginate properties for this city
        $paginatedProperties = Property::with([
            'location:location_id,property_id,city,region,country,area',
            'propertyType:property_type_id,title',
            'photos' => function ($query) {
                $query->select('property_photo_id', 'property_id', 'property_photo_file')
                      ->where('status', 'active')
                      ->limit(1);
            },
            'rates' => function ($query) {
                $query->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                      ->where('status', 'active');
            }
        ])
            ->where('status', 'active')
            ->whereHas('location', function ($query) use ($decodedCity) {
                $query->where('city', 'LIKE', $decodedCity);
            })
            ->latest()
            ->paginate((int) $request->get('per_page', 20));

        // 4. Format properties (ensure full asset URLs, truncate description, add price)
        $paginatedProperties->getCollection()->transform(function ($prop) {
            $item = $prop->toArray();

            if (!empty($item['description'])) {
                $stripped = strip_tags($item['description']);
                $item['description'] = mb_strimwidth($stripped, 0, 150, '...');
            }

            if (!empty($item['photos'])) {
                foreach ($item['photos'] as &$photo) {
                    if (!empty($photo['property_photo_file'])) {
                        $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                    }
                }
            }

            $item['price'] = $prop->price;
            unset($item['rates']);

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'city' => $decodedCity,
                'country' => $allProperties->first()->location->country ?? 'Unknown',
                'total_properties' => $allProperties->count(),
                'areas' => array_values($areas),
                'properties' => $paginatedProperties
            ]
        ], 200);
    }

    /**
     * Get all properties for a specific area within a city, with pagination.
     *
     * URL: /destinations/{city}/{area}
     */
    public function areaDetail(Request $request, string $city, string $area): JsonResponse
    {
        $decodedCity = urldecode($city);
        $decodedArea = urldecode($area);

        // 1. Count total properties in this area (for area metadata)
        $totalInArea = Property::where('status', 'active')
            ->whereHas('location', function ($query) use ($decodedCity, $decodedArea) {
                $query->where('city', 'LIKE', $decodedCity)
                      ->where('area', 'LIKE', $decodedArea);
            })
            ->count();

        if ($totalInArea === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No properties found in ' . $decodedArea . ', ' . $decodedCity . '.'
            ], 404);
        }

        // 2. Get area cover image and short description from first property with a photo
        $coverProperty = Property::with(['photos' => function ($q) {
                $q->where('status', 'active')->limit(1);
            }])
            ->where('status', 'active')
            ->whereHas('location', function ($query) use ($decodedCity, $decodedArea) {
                $query->where('city', 'LIKE', $decodedCity)
                      ->where('area', 'LIKE', $decodedArea);
            })
            ->whereHas('photos', function ($q) {
                $q->where('status', 'active');
            })
            ->first();

        $coverImage = null;
        $shortDescription = null;

        if ($coverProperty) {
            $photo = $coverProperty->photos->first();
            if ($photo) {
                $coverImage = \App\Helpers\Helper::imageUrl($photo->property_photo_file);
            }
            if (!empty($coverProperty->description)) {
                $stripped = strip_tags($coverProperty->description);
                $shortDescription = mb_strimwidth($stripped, 0, 150, '...');
            }
        }

        // 3. Build area metadata
        $areaInfo = [
            'title' => $decodedArea,
            'image' => $coverImage,
            'short_description' => $shortDescription ?? 'Fantastic properties located in ' . $decodedArea . '.',
            'property_count' => $totalInArea,
        ];

        // 4. Paginate properties in this area (real pagination)
        $paginatedProperties = Property::with([
                'location:location_id,property_id,city,region,country,area',
                'propertyType:property_type_id,title',
                'photos' => function ($query) {
                    $query->select('property_photo_id', 'property_id', 'property_photo_file')
                          ->where('status', 'active')
                          ->limit(1);
                },
                'rates' => function ($query) {
                    $query->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                          ->where('status', 'active');
                }
            ])
            ->where('status', 'active')
            ->whereHas('location', function ($query) use ($decodedCity, $decodedArea) {
                $query->where('city', 'LIKE', $decodedCity)
                      ->where('area', 'LIKE', $decodedArea);
            })
            ->latest()
            ->paginate((int) $request->get('per_page', 20));

        // 5. Transform each item: add computed price, fix asset URLs, strip raw rates
        $paginatedProperties->getCollection()->transform(function ($prop) {
            $item = $prop->toArray();

            // Truncate description for listing view
            if (!empty($item['description'])) {
                $stripped = strip_tags($item['description']);
                $item['description'] = mb_strimwidth($stripped, 0, 150, '...');
            }

            // Fix photo asset URLs
            if (!empty($item['photos'])) {
                foreach ($item['photos'] as &$photo) {
                    if (!empty($photo['property_photo_file'])) {
                        $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                    }
                }
            }

            // Inject computed price, strip raw rates
            $item['price'] = $prop->price;
            unset($item['rates']);

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'city' => $decodedCity,
                'area' => $areaInfo,
                'properties' => $paginatedProperties
            ]
        ], 200);
    }
}
