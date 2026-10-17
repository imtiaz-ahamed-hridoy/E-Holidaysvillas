<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WorldLocationSeeder extends Seeder
{
    /**
     * Seed the world_countries, world_states, and world_cities tables.
     *
     * Uses JSON data from dr5hn/countries-states-cities-database.
     * countries.json and states.json are stored in database/data/
     * cities.json is downloaded at seed-time if not present (too large for Git).
     */
    public function run(): void
    {
        $dataPath = database_path('data');

        // Truncate old data (order matters due to FKs)
        Schema::disableForeignKeyConstraints();
        DB::table('world_cities')->truncate();
        DB::table('world_states')->truncate();
        DB::table('world_countries')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('Seeding world countries...');
        $this->seedCountries($dataPath);

        $this->command->info('Seeding world states/regions...');
        $this->seedStates($dataPath);

        $this->command->info('Seeding world cities (this may take a moment)...');
        $this->seedCities($dataPath);

        $this->command->info('World location seeding complete!');
    }

    private function seedCountries(string $dataPath): void
    {
        $json = File::get($dataPath . '/countries.json');
        $countries = json_decode($json, true);

        $rows = [];
        foreach ($countries as $country) {
            $rows[] = [
                'id'   => $country['id'],
                'name' => $country['name'],
                'iso2' => $country['iso2'],
            ];
        }

        // Chunk insert to avoid memory issues
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('world_countries')->insert($chunk);
        }

        $this->command->info('  → ' . count($rows) . ' countries seeded.');
    }

    private function seedStates(string $dataPath): void
    {
        $json = File::get($dataPath . '/states.json');
        $states = json_decode($json, true);

        $rows = [];
        foreach ($states as $state) {
            $rows[] = [
                'id'         => $state['id'],
                'country_id' => $state['country_id'],
                'name'       => $state['name'],
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('world_states')->insert($chunk);
        }

        $this->command->info('  → ' . count($rows) . ' states/regions seeded.');
    }

    private function seedCities(string $dataPath): void
    {
        $citiesFile = $dataPath . '/cities.json';

        // Check for compressed version first
        $gzFile = $dataPath . '/cities.json.gz';
        if (!File::exists($citiesFile) && File::exists($gzFile)) {
            $this->command->info('  → Decompressing cities.json.gz...');
            $gz = gzopen($gzFile, 'rb');
            $out = fopen($citiesFile, 'wb');
            while (!gzeof($gz)) {
                fwrite($out, gzread($gz, 4096));
            }
            gzclose($gz);
            fclose($out);
            $this->command->info('  → Decompressed successfully.');
        }

        if (!File::exists($citiesFile)) {
            $this->command->error('  → cities.json not found.');
            $this->command->error('    Please run: php database/data/download_cities.php');
            $this->command->error('    Or place cities.json in: ' . $dataPath);
            return;
        }

        $this->command->info('  → Stream-parsing cities.json...');

        DB::disableQueryLog();

        $handle = fopen($citiesFile, 'r');
        if (!$handle) {
            $this->command->error('  → Failed to open cities.json.');
            return;
        }

        $batch = [];
        $count = 0;
        $buffer = '';
        $depth = 0;
        $inObject = false;

        while (!feof($handle)) {
            $chunk = fread($handle, 65536); // Read 64KB at a time
            $len = strlen($chunk);

            for ($i = 0; $i < $len; $i++) {
                $char = $chunk[$i];

                if ($char === '{') {
                    $depth++;
                    $inObject = true;
                    $buffer .= $char;
                } elseif ($char === '}') {
                    $buffer .= $char;
                    $depth--;
                    if ($depth === 0 && $inObject) {
                        // We have a complete JSON object
                        $city = json_decode($buffer, true);
                        if ($city && isset($city['id'], $city['state_id'], $city['country_id'], $city['name'])) {
                            $batch[] = [
                                'id'         => $city['id'],
                                'state_id'   => $city['state_id'],
                                'country_id' => $city['country_id'],
                                'name'       => mb_substr($city['name'], 0, 150),
                            ];

                            if (count($batch) >= 2000) {
                                try {
                                    DB::table('world_cities')->insert($batch);
                                } catch (\Exception $e) {
                                    // Skip duplicates
                                }
                                $count += count($batch);
                                $batch = [];

                                if ($count % 20000 === 0) {
                                    $this->command->info("    → {$count} cities inserted...");
                                }
                            }
                        }
                        $buffer = '';
                        $inObject = false;
                    }
                } elseif ($inObject) {
                    $buffer .= $char;
                }
            }
        }

        fclose($handle);

        // Insert remaining
        if (!empty($batch)) {
            try {
                DB::table('world_cities')->insert($batch);
            } catch (\Exception $e) {
                // Skip duplicates
            }
            $count += count($batch);
        }

        $this->command->info("  → {$count} cities seeded.");
    }
}
