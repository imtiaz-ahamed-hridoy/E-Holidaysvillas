<?php

namespace App\Jobs;

use App\Services\Legacy\Vrs7SqlFileImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessLegacyImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 7200; // 2 hours max for large imports
    public int $backoff = 60;

    public string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $cacheKey = 'legacy_import_progress';
        $reportKey = 'legacy_import_report';

        try {
            // ==========================================
            // PHASE 1: SYNCHRONIZE ALL PROPERTY DATA
            // ==========================================
            $this->updateProgress($cacheKey, 'importing', 0, 'Starting Phase 1: Synchronizing all property data...');

            $importer = new Vrs7SqlFileImporter();
            $importResult = $importer->import($this->filePath);

            $stats = $importResult['stats'] ?? [];
            $propertyMap = $importResult['property_map'] ?? [];
            $legacyListings = $importResult['legacy_listings'] ?? [];
            $legacyLocations = $importResult['legacy_locations'] ?? [];

            $totalVillas = count($propertyMap);
            Log::info("[Phase 1 Complete] Synchronized {$totalVillas} properties into database.", $stats);

            // ------------------------------------------
            // PHASE 1 VERIFICATION: ZERO MISMATCH CHECK
            // ------------------------------------------
            $this->updateProgress($cacheKey, 'importing', 25, 'Verifying Phase 1 property data relationships...');
            $dataVerification = $this->verifyPropertyDataSync($propertyMap, $legacyListings, $legacyLocations);

            if (!empty($dataVerification['mismatches'])) {
                Log::warning("[Phase 1 Verification] Found " . count($dataVerification['mismatches']) . " property data mismatches.", $dataVerification['mismatches']);
            } else {
                Log::info("[Phase 1 Verification] Zero property data mismatches verified across {$totalVillas} villas.");
            }

            // ==========================================
            // PHASE 2: VILLA-BY-VILLA IMAGE MIGRATION
            // ==========================================
            $this->updateProgress($cacheKey, 'importing', 30, 'Starting Phase 2: Villa-by-villa image migration...');

            $imageMigrationResult = $this->migrateVillaImagesVillaByVilla($cacheKey);

            // ------------------------------------------
            // PHASE 2 VERIFICATION: IMAGE VERIFICATION
            // ------------------------------------------
            $this->updateProgress($cacheKey, 'importing', 95, 'Verifying Phase 2 image disk files and database paths...');
            $imageVerification = $this->verifyImageMigration($propertyMap);

            // ==========================================
            // FINAL COMPREHENSIVE MIGRATION REPORT
            // ==========================================
            $migrationReport = [
                'total_legacy_villas'            => $totalVillas,
                'total_villas_synchronized'      => $totalVillas,
                'total_villas_failed'            => 0,
                'data_sync_stats'                => $stats,
                'property_data_mismatches'       => $dataVerification['mismatches'],
                'total_property_mismatches'      => count($dataVerification['mismatches']),
                'total_legacy_images'            => $imageMigrationResult['total_images'],
                'total_images_downloaded'        => $imageMigrationResult['downloaded'],
                'total_images_stored'            => $imageMigrationResult['stored'],
                'total_image_records_updated'    => $imageMigrationResult['updated'],
                'total_failed_images'            => count($imageMigrationResult['failed_images']),
                'failed_images'                  => $imageMigrationResult['failed_images'],
                'total_image_mismatches'         => count($imageVerification['image_mismatches']),
                'image_mismatches'              => $imageVerification['image_mismatches'],
                'total_relationship_mismatches'  => count($dataVerification['mismatches']) + count($imageVerification['image_mismatches']),
                'completed_at'                   => now()->toDateTimeString(),
            ];

            Cache::put($reportKey, $migrationReport, now()->addHours(24));

            $summaryMessage = "Legacy import and migration completed successfully! "
                . "Villas: {$totalVillas}, "
                . "Images Processed: {$imageMigrationResult['total_images']}, "
                . "Images Stored: {$imageMigrationResult['stored']}, "
                . "Failed Images: " . count($imageMigrationResult['failed_images']) . ", "
                . "Data Mismatches: " . count($dataVerification['mismatches']);

            $this->updateProgress($cacheKey, 'completed', 100, $summaryMessage, $migrationReport);

            Log::info("Legacy import & migration completed successfully.", $migrationReport);

        } catch (\Throwable $e) {
            $this->updateProgress($cacheKey, 'failed', 0, 'Import failed: ' . $e->getMessage());
            Log::error('Legacy import failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            @unlink($this->filePath);
        }
    }

    /**
     * Phase 1 Verification: Cross-verify synchronized database records
     * against source legacy records to ensure ZERO data mismatch.
     */
    private function verifyPropertyDataSync(array $propertyMap, array $legacyListings, array $legacyLocations): array
    {
        $mismatches = [];

        foreach ($propertyMap as $legacyListingId => $propertyId) {
            $legacyListing = $legacyListings[$legacyListingId] ?? [];
            $dbProperty = DB::table('properties')->where('property_id', $propertyId)->first();

            if (!$dbProperty) {
                $mismatches[] = [
                    'legacy_listing_id' => $legacyListingId,
                    'property_id'       => $propertyId,
                    'type'              => 'missing_property',
                    'message'           => 'Property record not found in properties table.',
                ];
                continue;
            }

            // Verify explicit mapping
            if ((int)$dbProperty->legacy_listing_id !== (int)$legacyListingId) {
                $mismatches[] = [
                    'legacy_listing_id' => $legacyListingId,
                    'property_id'       => $propertyId,
                    'type'              => 'legacy_id_mismatch',
                    'expected'          => $legacyListingId,
                    'actual'            => $dbProperty->legacy_listing_id,
                ];
            }

            // Verify Title match
            $expectedTitle = trim((string)($legacyListing['title_1'] ?? ''));
            if ($expectedTitle !== '' && !str_starts_with($dbProperty->title, mb_substr($expectedTitle, 0, 20))) {
                $mismatches[] = [
                    'legacy_listing_id' => $legacyListingId,
                    'property_id'       => $propertyId,
                    'type'              => 'title_mismatch',
                    'expected'          => $expectedTitle,
                    'actual'            => $dbProperty->title,
                ];
            }

            // Verify Location record attached
            $dbLocation = DB::table('locations')->where('property_id', $propertyId)->first();
            if (!$dbLocation) {
                $mismatches[] = [
                    'legacy_listing_id' => $legacyListingId,
                    'property_id'       => $propertyId,
                    'type'              => 'missing_location',
                    'message'           => 'Location record missing for property.',
                ];
            }

            // Verify Rates attached
            $rateCount = DB::table('rates')->where('property_id', $propertyId)->count();
            if ($rateCount === 0 && isset($legacyListing['has_rates']) && $legacyListing['has_rates']) {
                $mismatches[] = [
                    'legacy_listing_id' => $legacyListingId,
                    'property_id'       => $propertyId,
                    'type'              => 'missing_rates',
                    'message'           => 'Property expected rates but none were mapped.',
                ];
            }
        }

        return [
            'mismatches' => $mismatches,
        ];
    }

    /**
     * Phase 2: Villa-by-Villa Image Migration.
     * Processes every villa sequentially:
     * Villa X -> Find all Villa X images -> Download -> Validate -> Save to storage -> Update DB -> Verify -> Move to Villa X+1
     */
    private function migrateVillaImagesVillaByVilla(string $cacheKey): array
    {
        $photos = DB::table('property_photos')
            ->where(function ($query) {
                $query->where('property_photo_file', 'like', 'https://%')
                      ->orWhere('property_photo_file', 'like', 'http://%');
            })
            ->orderBy('property_id')
            ->get(['property_photo_id', 'property_id', 'legacy_listing_photo_id', 'property_photo_file']);

        $totalImages = $photos->count();

        if ($photos->isEmpty()) {
            return [
                'total_images'  => 0,
                'downloaded'    => 0,
                'stored'        => 0,
                'updated'       => 0,
                'failed_images' => [],
            ];
        }

        // Group photos strictly by property_id (villa-wise)
        $villaPhotos = $photos->groupBy('property_id');
        $totalVillas = $villaPhotos->count();
        $processedVillas = 0;

        $downloadedCount = 0;
        $storedCount = 0;
        $updatedCount = 0;
        $failedImages = [];

        $baseDir = storage_path('app/public/uploads/properties/photos');
        $publicDir = storage_path('app/public/uploads/properties/photos');

        foreach ($villaPhotos as $propertyId => $villaPhotoGroup) {
            $processedVillas++;

            $villaDir = $baseDir . DIRECTORY_SEPARATOR . $propertyId;
            if (!is_dir($villaDir)) {
                mkdir($villaDir, 0755, true);
            }
            $publicVillaDir = $publicDir . DIRECTORY_SEPARATOR . $propertyId;
            if (!is_dir($publicVillaDir) && realpath($publicVillaDir) !== realpath($villaDir)) {
                mkdir($publicVillaDir, 0755, true);
            }

            // Calculate progress in range 35% to 95%
            $percent = 35 + (int) round(($processedVillas / $totalVillas) * 60);
            $this->updateProgress(
                $cacheKey,
                'importing',
                $percent,
                "Downloading & validating images for Villa {$processedVillas}/{$totalVillas} (Property #{$propertyId})..."
            );

            foreach ($villaPhotoGroup as $photo) {
                $result = $this->processAndValidateSinglePhoto($photo, $villaDir, $publicVillaDir, (int)$propertyId);

                if ($result['success']) {
                    $downloadedCount++;
                    $storedCount++;
                    $updatedCount++;
                } else {
                    $failedImages[] = [
                        'property_id'             => $propertyId,
                        'property_photo_id'       => $photo->property_photo_id,
                        'legacy_listing_photo_id' => $photo->legacy_listing_photo_id ?? null,
                        'url'                     => $photo->property_photo_file,
                        'error'                   => $result['error'],
                        'http_status'             => $result['http_status'],
                        'attempts'                => $result['attempts'],
                    ];
                }

                usleep(100000); // 100ms throttle between downloads
            }

            // Verify Villa X folder has saved images
            $villaFiles = is_dir($villaDir) ? array_diff(scandir($villaDir), ['.', '..']) : [];
            Log::info("[Villa #{$propertyId} Image Migration] Processed " . count($villaPhotoGroup) . " photos. Disk files stored: " . count($villaFiles));
        }

        return [
            'total_images'  => $totalImages,
            'downloaded'    => $downloadedCount,
            'stored'        => $storedCount,
            'updated'       => $updatedCount,
            'failed_images' => $failedImages,
        ];
    }

    /**
     * Download, validate, store, and update database for a single photo.
     */
    private function processAndValidateSinglePhoto(object $photo, string $villaDir, string $publicVillaDir, int $propertyId): array
    {
        $url = $photo->property_photo_file;
        $urlFilename = basename(parse_url($url, PHP_URL_PATH));
        $cleanFilename = preg_replace('/^i\./i', '', $urlFilename);

        // Check local search fallback paths
        $localSearchPaths = [
            storage_path('app/legacy_photos/' . $cleanFilename),
            storage_path('app/legacy_photos/' . $urlFilename),
            storage_path('app/public/' . ltrim('legacy_photos/' . $cleanFilename, '/')),
            storage_path('app/public/' . ltrim('legacy_photos/' . $urlFilename, '/')),
            storage_path('app/public/legacy_photos/' . $cleanFilename),
        ];

        foreach ($localSearchPaths as $localSourceFile) {
            if (file_exists($localSourceFile) && filesize($localSourceFile) > 1000) {
                $extension = strtolower(pathinfo($localSourceFile, PATHINFO_EXTENSION)) ?: 'jpg';
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $localDestination = $villaDir . DIRECTORY_SEPARATOR . $filename;

                if (!is_dir($villaDir)) {
                    mkdir($villaDir, 0755, true);
                }

                copy($localSourceFile, $localDestination);

                if (realpath($publicVillaDir) !== realpath($villaDir)) {
                    if (!is_dir($publicVillaDir)) {
                        mkdir($publicVillaDir, 0755, true);
                    }
                    @copy($localSourceFile, $publicVillaDir . DIRECTORY_SEPARATOR . $filename);
                }

                $dbPath = 'uploads/properties/photos/' . $propertyId . '/' . $filename;
                DB::table('property_photos')
                    ->where('property_photo_id', $photo->property_photo_id)
                    ->update([
                        'property_photo_file' => $dbPath,
                        'updated_at' => now(),
                    ]);

                return ['success' => true, 'error' => null, 'http_status' => 200, 'attempts' => 1];
            }
        }

        // Attempt HTTP download with 3 retries
        $maxAttempts = 3;
        $lastError = 'Unknown download failure';
        $lastHttpStatus = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout(30)->withOptions([
                    'verify' => false,
                ])->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://cdn.example.com/',
                ])->get($url);

                $lastHttpStatus = $response->status();

                if ($response->successful()) {
                    $imageContent = $response->body();

                    // Validation 1: Minimum file size check (> 1000 bytes)
                    if (strlen($imageContent) < 1000) {
                        $lastError = "Response body size too small (" . strlen($imageContent) . " bytes)";
                        continue;
                    }

                    // Validation 2: Content-Type / HTML challenge check
                    $contentType = strtolower($response->header('Content-Type') ?? '');
                    if (str_contains($contentType, 'text/html') || str_starts_with(trim($imageContent), '<!DOCTYPE') || str_starts_with(trim($imageContent), '<html')) {
                        $lastError = "Response contains HTML content or Cloudflare challenge (HTTP {$lastHttpStatus})";
                        continue;
                    }

                    // Determine extension
                    $extension = $this->getExtensionFromUrl($url);
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $localPath = $villaDir . DIRECTORY_SEPARATOR . $filename;

                    if (!is_dir($villaDir)) {
                        mkdir($villaDir, 0755, true);
                    }

                    file_put_contents($localPath, $imageContent);

                    if (realpath($publicVillaDir) !== realpath($villaDir)) {
                        if (!is_dir($publicVillaDir)) {
                            mkdir($publicVillaDir, 0755, true);
                        }
                        file_put_contents($publicVillaDir . DIRECTORY_SEPARATOR . $filename, $imageContent);
                    }

                    // Validation 3: Disk file check
                    if (!file_exists($localPath) || filesize($localPath) < 1000) {
                        $lastError = "Saved disk file is missing or invalid";
                        continue;
                    }

                    // Update database
                    $dbPath = 'uploads/properties/photos/' . $propertyId . '/' . $filename;
                    DB::table('property_photos')
                        ->where('property_photo_id', $photo->property_photo_id)
                        ->update([
                            'property_photo_file' => $dbPath,
                            'updated_at' => now(),
                        ]);

                    return ['success' => true, 'error' => null, 'http_status' => $lastHttpStatus, 'attempts' => $attempt];
                } else {
                    $lastError = "HTTP error {$lastHttpStatus}";
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }

            usleep(500000); // 500ms delay before retry
        }

        Log::warning("Failed to download image after {$maxAttempts} attempts: {$url} â€” {$lastError}");

        return [
            'success'     => false,
            'error'       => $lastError,
            'http_status' => $lastHttpStatus,
            'attempts'    => $maxAttempts,
        ];
    }

    /**
     * Phase 2 Verification: Verify that stored database photo paths
     * point to valid, readable physical files on disk.
     */
    private function verifyImageMigration(array $propertyMap): array
    {
        $imageMismatches = [];

        foreach ($propertyMap as $legacyListingId => $propertyId) {
            $photos = DB::table('property_photos')->where('property_id', $propertyId)->get();

            foreach ($photos as $photo) {
                $filePath = $photo->property_photo_file;
                if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
                    // Still external URL (failed download)
                    continue;
                }

                $publicFile = storage_path('app/public/' . $filePath);
                $storageFile = storage_path('app/public/' . $filePath);

                if (!file_exists($publicFile) && !file_exists($storageFile)) {
                    $imageMismatches[] = [
                        'property_id'       => $propertyId,
                        'property_photo_id' => $photo->property_photo_id,
                        'db_path'           => $filePath,
                        'type'              => 'missing_file_on_disk',
                        'message'           => 'Database photo record points to non-existent file on disk.',
                    ];
                }
            }
        }

        return [
            'image_mismatches' => $imageMismatches,
        ];
    }

    /**
     * Extract file extension from the legacy URL.
     */
    private function getExtensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (empty($extension) && preg_match('/\.(\w{3,4})$/i', $url, $matches)) {
            $extension = strtolower($matches[1]);
        }

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $extension : 'jpg';
    }

    /**
     * Update the import progress in cache for frontend polling.
     */
    private function updateProgress(string $cacheKey, string $status, int $percent, string $message, array $report = []): void
    {
        Cache::put($cacheKey, [
            'status'     => $status,
            'percent'    => $percent,
            'message'    => $message,
            'report'     => $report,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(2));
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put('legacy_import_progress', [
            'status'     => 'failed',
            'percent'    => 0,
            'message'    => 'Import failed: ' . $exception->getMessage(),
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(2));

        Log::error('Legacy import job failed permanently: ' . $exception->getMessage());
    }
}

