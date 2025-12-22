<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateCGCUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get CGC role
        $cgcRole = Role::where('code', 'CGC')->first();
        if (!$cgcRole) {
            $this->command->error('CGC role not found. Please run RoleSeeder first.');
            return;
        }

        // Create CGC user
        $cgcUser = User::firstOrCreate(
            ['email' => 'cgc@ncs.gov.ng'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign CGC role if not already assigned
        if (!$cgcUser->hasRole('CGC')) {
            $cgcUser->roles()->attach($cgcRole->id, [
                'is_active' => true,
                'assigned_at' => now(),
            ]);
            $this->command->info("✅ Created/Updated CGC user: cgc@ncs.gov.ng (password: password123)");
        } else {
            $this->command->info("ℹ️  CGC user already exists: cgc@ncs.gov.ng");
        }

        $this->command->info("CGC User Details:");
        $this->command->info("  Email: cgc@ncs.gov.ng");
        $this->command->info("  Password: password123");
        $this->command->info("  Role: CGC (Comptroller General of Customs)");
    }
}
