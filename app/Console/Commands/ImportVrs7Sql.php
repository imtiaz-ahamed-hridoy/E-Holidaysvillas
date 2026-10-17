<?php

namespace App\Console\Commands;

use App\Services\Legacy\Vrs7SqlFileImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportVrs7Sql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import-vrs7 {file=legacy_vrs7.sql : SQL dump path relative to the project root}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import properties and related data from the legacy VRS7 SQL dump';

    /**
     * Execute the console command.
     */
    public function handle(Vrs7SqlFileImporter $importer): int
    {
        $file = base_path($this->argument('file'));

        $this->info('Starting legacy VRS7 import...');

        try {
            $result = $importer->import($file);
            $stats = $result['stats'] ?? $result;
        } catch (\Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());

            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }

        $this->info('Import completed successfully.');

        foreach ($stats as $key => $value) {
            $this->line("$key: $value");
        }

        $this->info("\n--- Imported Property Data ---");
        $this->table(['metric', 'value'], [
            ['properties', DB::table('properties')->count()],
            ['locations', DB::table('locations')->count()],
            ['rates', DB::table('rates')->count()],
            ['property_policies', DB::table('property_policies')->count()],
            ['property_facilities', DB::table('property_facilities')->count()],
            ['amenities', DB::table('amenities')->count()],
        ]);

        return self::SUCCESS;
    }
}

