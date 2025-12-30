<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Use TestDataSeeder for comprehensive test data (100 officers)
        // Or use individual seeders for development
        if (app()->environment('testing') || env('USE_TEST_SEEDER', false)) {
            $this->call([
                RoleSeeder::class,
                LeaveTypeSeeder::class,
                TestDataSeeder::class,
            ]);
        } else {
            $this->call([
                RoleSeeder::class,
                LeaveTypeSeeder::class,
                ZoneAndCommandSeeder::class, // New seeder with zones and all commands
                CompleteSystemSeeder::class, // Complete system with all roles and functions active
            ]);
        }
        
        // Seed HRD test data (does NOT delete existing data)
        if (env('SEED_HRD_TEST_DATA', false)) {
            $this->call([
                HRDTestDataSeeder::class,
            ]);
        }
    }
}
