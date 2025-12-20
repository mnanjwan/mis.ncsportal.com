<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateTRADOCICTUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get TRADOC role
        $tradocRole = Role::where('code', 'TRADOC')->first();
        if (!$tradocRole) {
            $this->command->error('TRADOC role not found. Please run RoleSeeder first.');
            return;
        }

        // Get ICT role
        $ictRole = Role::where('code', 'ICT')->first();
        if (!$ictRole) {
            $this->command->error('ICT role not found. Please run RoleSeeder first.');
            return;
        }

        // Create TRADOC user
        $tradocUser = User::firstOrCreate(
            ['email' => 'tradoc@ncs.gov.ng'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign TRADOC role if not already assigned
        if (!$tradocUser->hasRole('TRADOC')) {
            $tradocUser->roles()->attach($tradocRole->id, [
                'is_active' => true,
                'assigned_at' => now(),
            ]);
            $this->command->info("Created/Updated TRADOC user: tradoc@ncs.gov.ng (password: password123)");
        } else {
            $this->command->info("TRADOC user already exists: tradoc@ncs.gov.ng");
        }

        // Create ICT user
        $ictUser = User::firstOrCreate(
            ['email' => 'ict@ncs.gov.ng'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign ICT role if not already assigned
        if (!$ictUser->hasRole('ICT')) {
            $ictUser->roles()->attach($ictRole->id, [
                'is_active' => true,
                'assigned_at' => now(),
            ]);
            $this->command->info("Created/Updated ICT user: ict@ncs.gov.ng (password: password123)");
        } else {
            $this->command->info("ICT user already exists: ict@ncs.gov.ng");
        }
    }
}
