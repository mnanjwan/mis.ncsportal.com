<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationDataSeeder extends Seeder
{
    /**
     * Seed all Nigerian location data: geopolitical zones, states, and LGAs.
     * Run after GeopoliticalZoneSeeder or call it from here.
     */
    public function run(): void
    {
        $this->call(GeopoliticalZoneSeeder::class);

        $path = database_path('seeders/data/nigeria_lgas.json');
        if (! is_file($path)) {
            $this->command->warn('nigeria_lgas.json not found. Skipping LGA seed.');
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (! is_array($data)) {
            $this->command->warn('Invalid nigeria_lgas.json. Skipping LGA seed.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($data as $stateName => $lgaNames) {
            $state = State::where('name', $stateName)->first();
            if (! $state) {
                $this->command->warn("State not found: {$stateName}. Skipping its LGAs.");
                continue;
            }

            foreach ($lgaNames as $sortOrder => $lgaName) {
                $lgaName = trim($lgaName);
                if ($lgaName === '') {
                    continue;
                }

                $lga = Lga::firstOrCreate(
                    [
                        'state_id' => $state->id,
                        'name' => $lgaName,
                    ],
                    [
                        'sort_order' => $sortOrder + 1,
                        'is_active' => true,
                    ]
                );

                if ($lga->wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->command->info("Location data seeded. LGAs created: {$created}, already existing: {$skipped}.");
    }
}
