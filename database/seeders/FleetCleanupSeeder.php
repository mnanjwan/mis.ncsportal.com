<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FleetCleanupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Delete Requests and related data
        DB::table('fleet_request_steps')->delete();
        DB::table('fleet_request_fulfillments')->delete();
        DB::table('fleet_vehicle_assignments')->delete();
        DB::table('fleet_requests')->delete();

        // 2. Delete Test Vehicles (those with reg_no matching our pattern or just all for test)
        DB::table('fleet_vehicles')->delete();

        // 3. Delete Test Users created (optional - be careful not to delete real users)
        // \App\Models\User::where('email', 'like', 'tl_user_%')->delete();

        $this->command->info('Fleet test data cleaned up successfully!');
    }
}
