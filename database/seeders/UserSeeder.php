<?php

namespace Database\Seeders;

use App\Models\Officer;
use App\Models\Command;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get Roles
        $hrdRole = Role::where('name', 'HRD')->first();
        $zoneCoordinatorRole = Role::where('name', 'Zone Coordinator')->first();
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        $officerRole = Role::where('name', 'Officer')->first();
        $assessorRole = Role::where('name', 'Assessor')->first();
        $validatorRole = Role::where('name', 'Validator')->first();
        $areaControllerRole = Role::where('name', 'Area Controller')->first();
        $dcAdminRole = Role::where('name', 'DC Admin')->first();

        // Get Commands (they should exist from ZoneAndCommandSeeder)
        $apapaCommand = Command::where('code', 'APAPA')->first();
        $kadunaCommand = Command::where('code', 'KADUNA')->first();
        $phBayelsaCommand = Command::where('code', 'PH_I_BAYELSA')->first();
        $baGmCommand = Command::where('code', 'BA_GM')->first();
        $fctCommand = Command::where('code', 'FCT')->first();

        // Create HRD Admin User
        $hrdUser = User::firstOrCreate(
            ['email' => 'hrd@ncs.gov.ng'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        if ($hrdRole && !$hrdUser->hasRole('HRD')) {
            $hrdUser->roles()->attach($hrdRole->id, [
                'is_active' => true,
                'assigned_at' => now(),
            ]);
        }

        // Create Zone Coordinators
        if ($zoneCoordinatorRole) {
            // Zone A Coordinator
            if ($apapaCommand) {
                $zoneCoordA = User::firstOrCreate(
                    ['email' => 'zonecoord.a@ncs.gov.ng'],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                    ]
                );
                if (!$zoneCoordA->hasRole('Zone Coordinator')) {
                    $zoneCoordA->roles()->attach($zoneCoordinatorRole->id, [
                        'command_id' => $apapaCommand->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Zone B Coordinator
            if ($kadunaCommand) {
                $zoneCoordB = User::firstOrCreate(
                    ['email' => 'zonecoord.b@ncs.gov.ng'],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                    ]
                );
                if (!$zoneCoordB->hasRole('Zone Coordinator')) {
                    $zoneCoordB->roles()->attach($zoneCoordinatorRole->id, [
                        'command_id' => $kadunaCommand->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Zone C Coordinator
            if ($phBayelsaCommand) {
                $zoneCoordC = User::firstOrCreate(
                    ['email' => 'zonecoord.c@ncs.gov.ng'],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                    ]
                );
                if (!$zoneCoordC->hasRole('Zone Coordinator')) {
                    $zoneCoordC->roles()->attach($zoneCoordinatorRole->id, [
                        'command_id' => $phBayelsaCommand->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Zone D Coordinator
            if ($baGmCommand) {
                $zoneCoordD = User::firstOrCreate(
                    ['email' => 'zonecoord.d@ncs.gov.ng'],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                    ]
                );
                if (!$zoneCoordD->hasRole('Zone Coordinator')) {
                    $zoneCoordD->roles()->attach($zoneCoordinatorRole->id, [
                        'command_id' => $baGmCommand->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
            }
        }

        // Create Staff Officer
        if ($staffOfficerRole && $apapaCommand) {
            $staffOfficer = User::firstOrCreate(
                ['email' => 'staff.apapa@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$staffOfficer->hasRole('Staff Officer')) {
                $staffOfficer->roles()->attach($staffOfficerRole->id, [
                    'command_id' => $apapaCommand->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Note: All test officers are now created by ZoneAndCommandSeeder
        // This seeder only creates admin users (HRD, Zone Coordinators, Staff Officers)
    }
}
