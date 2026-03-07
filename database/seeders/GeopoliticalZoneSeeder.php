<?php

namespace Database\Seeders;

use App\Models\GeopoliticalZone;
use App\Models\State;
use Illuminate\Database\Seeder;

class GeopoliticalZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'North Central', 'sort_order' => 1, 'states' => ['Benue', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Plateau', 'FCT']],
            ['name' => 'North East', 'sort_order' => 2, 'states' => ['Adamawa', 'Bauchi', 'Borno', 'Gombe', 'Taraba', 'Yobe']],
            ['name' => 'North West', 'sort_order' => 3, 'states' => ['Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Sokoto', 'Zamfara']],
            ['name' => 'South East', 'sort_order' => 4, 'states' => ['Abia', 'Anambra', 'Ebonyi', 'Enugu', 'Imo']],
            ['name' => 'South South', 'sort_order' => 5, 'states' => ['Akwa Ibom', 'Bayelsa', 'Cross River', 'Delta', 'Edo', 'Rivers']],
            ['name' => 'South West', 'sort_order' => 6, 'states' => ['Ekiti', 'Lagos', 'Ogun', 'Ondo', 'Osun', 'Oyo']],
        ];

        foreach ($zones as $idx => $zoneData) {
            $zone = GeopoliticalZone::firstOrCreate(
                ['name' => $zoneData['name']],
                ['sort_order' => $zoneData['sort_order'], 'is_active' => true]
            );

            foreach ($zoneData['states'] as $i => $stateName) {
                $state = State::firstOrCreate(
                    [
                        'geopolitical_zone_id' => $zone->id,
                        'name' => $stateName,
                    ],
                    ['sort_order' => $i + 1, 'is_active' => true]
                );
            }
        }
    }
}
