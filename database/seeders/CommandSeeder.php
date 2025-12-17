<?php

namespace Database\Seeders;

use App\Models\Command;
use Illuminate\Database\Seeder;

class CommandSeeder extends Seeder
{
    public function run(): void
    {
        $commands = [
            [
                'code' => 'HQ',
                'name' => 'Headquarters',
                'location' => 'Abuja',
                'is_active' => true,
            ],
            [
                'code' => 'LAGOS',
                'name' => 'Lagos Command',
                'location' => 'Lagos',
                'is_active' => true,
            ],
            [
                'code' => 'KANO',
                'name' => 'Kano Command',
                'location' => 'Kano',
                'is_active' => true,
            ],
            [
                'code' => 'RIVERS',
                'name' => 'Rivers Command',
                'location' => 'Port Harcourt',
                'is_active' => true,
            ],
            [
                'code' => 'ABIA',
                'name' => 'Abia Command',
                'location' => 'Umuahia',
                'is_active' => true,
            ],
        ];

        foreach ($commands as $command) {
            Command::create($command);
        }
    }
}

