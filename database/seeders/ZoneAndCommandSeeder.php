<?php

namespace Database\Seeders;

use App\Models\Zone;
use App\Models\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Officer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ZoneAndCommandSeeder extends Seeder
{
    public function run(): void
    {
        // Create Zones
        $zones = [
            [
                'code' => 'HEADQUARTERS',
                'name' => 'HEADQUARTERS',
                'description' => 'Nigeria Customs Service Headquarters',
                'is_active' => true,
            ],
            [
                'code' => 'ZONE_A',
                'name' => 'Zone A HQ',
                'description' => 'Zone A Headquarters',
                'is_active' => true,
            ],
            [
                'code' => 'ZONE_B',
                'name' => 'Zone B HQTRS',
                'description' => 'Zone B Headquarters',
                'is_active' => true,
            ],
            [
                'code' => 'ZONE_C',
                'name' => 'Zone C',
                'description' => 'Zone C',
                'is_active' => true,
            ],
            [
                'code' => 'ZONE_D',
                'name' => 'Zone D',
                'description' => 'Zone D',
                'is_active' => true,
            ],
        ];

        $zoneModels = [];
        foreach ($zones as $zoneData) {
            // Use updateOrCreate to ensure zones are updated if they already exist
            $zoneModels[$zoneData['code']] = Zone::updateOrCreate(
                ['code' => $zoneData['code']],
                $zoneData
            );
        }
        $this->command->info('✅ Created/Updated ' . count($zoneModels) . ' zones');

        // Define Commands by Zone
        $commandsByZone = [
            'HEADQUARTERS' => [
                ['code' => 'CGC_OFFICE', 'name' => 'CGC OFFICE', 'location' => 'Abuja'],
                ['code' => 'FATS_HQTRS', 'name' => 'FATS-HQTRS', 'location' => 'Abuja'],
                ['code' => 'SRP_HQTRS', 'name' => 'SR&P-HQTRS', 'location' => 'Abuja'],
                ['code' => 'HRD_HQTRS', 'name' => 'HRD-HQTRS', 'location' => 'Abuja'],
                ['code' => 'EI_I_HQTRS', 'name' => 'E I & I-HQTRS', 'location' => 'Abuja'],
                ['code' => 'EXCISE_FTZ_HQTRS', 'name' => 'EXCISE & FTZ-HQTRS', 'location' => 'Abuja'],
                ['code' => 'TRADOC', 'name' => 'TRADOC', 'location' => 'Abuja'],
                ['code' => 'LEGAL_UNIT', 'name' => 'LEGAL UNIT', 'location' => 'Abuja'],
                ['code' => 'ICT_MOD_HQTRS', 'name' => 'ICT-MOD-HQTRS', 'location' => 'Abuja'],
            ],
            'ZONE_A' => [
                ['code' => 'APAPA', 'name' => 'APAPA', 'location' => 'Lagos'],
                ['code' => 'TCIP', 'name' => 'TCIP', 'location' => 'Lagos'],
                ['code' => 'MMIA', 'name' => 'MMIA', 'location' => 'Lagos'],
                ['code' => 'MMAC', 'name' => 'MMAC', 'location' => 'Lagos'],
                ['code' => 'KLTC', 'name' => 'KLTC', 'location' => 'Lagos'],
                ['code' => 'LAGOS_INDUSTRIAL', 'name' => 'LAGOS INDUSTRIAL', 'location' => 'Lagos'],
                ['code' => 'SEME', 'name' => 'SEME', 'location' => 'Lagos'],
                ['code' => 'OGUN_I', 'name' => 'OGUN I', 'location' => 'Ogun'],
                ['code' => 'OGUN_II', 'name' => 'OGUN II', 'location' => 'Ogun'],
                ['code' => 'OYO_OSUN', 'name' => 'OYO - OSUN', 'location' => 'Oyo'],
                ['code' => 'ONDO_EKITI', 'name' => 'ONDO EKITI', 'location' => 'Ondo'],
                ['code' => 'PTML', 'name' => 'PTML', 'location' => 'Lagos'],
                ['code' => 'PCA_ZONE_A', 'name' => 'PCA Zone A', 'location' => 'Lagos'],
                ['code' => 'LEKKI_FTZ', 'name' => 'LEKKI FREE TRADE ZONE', 'location' => 'Lagos'],
                ['code' => 'LILYPOND', 'name' => 'LILYPOND EXPORT COMMAND', 'location' => 'Lagos'],
                ['code' => 'WMC', 'name' => 'WMC', 'location' => 'Lagos'],
                ['code' => 'IKORODU', 'name' => 'IKORODU', 'location' => 'Lagos'],
                ['code' => 'FOU_A', 'name' => 'FOU A', 'location' => 'Lagos'],
            ],
            'ZONE_B' => [
                ['code' => 'KADUNA', 'name' => 'KADUNA', 'location' => 'Kaduna'],
                ['code' => 'KANO_JIGAWA', 'name' => 'KANO JIGAWA', 'location' => 'Kano'],
                ['code' => 'SOKOTO_ZAMFARA', 'name' => 'SOKOTO ZAMFARA', 'location' => 'Sokoto'],
                ['code' => 'NIGER_KOGI', 'name' => 'NIGER KOGI', 'location' => 'Niger'],
                ['code' => 'FCT', 'name' => 'FCT', 'location' => 'Abuja'],
                ['code' => 'KWARA', 'name' => 'KWARA', 'location' => 'Kwara'],
                ['code' => 'KEBBI', 'name' => 'KEBBI', 'location' => 'Kebbi'],
                ['code' => 'NWM', 'name' => 'NWM', 'location' => 'North West'],
                ['code' => 'PT_NA_BE', 'name' => 'PT NA BE', 'location' => 'North'],
                ['code' => 'PCA_ZONE_B', 'name' => 'PCA Zone B', 'location' => 'Kaduna'],
                ['code' => 'FOU_B', 'name' => 'FOU B', 'location' => 'Kaduna'],
            ],
            'ZONE_C' => [
                ['code' => 'AN_EB_EN', 'name' => 'AN EB EN', 'location' => 'Anambra'],
                ['code' => 'IMO_ABIA', 'name' => 'IMO ABIA', 'location' => 'Imo'],
                ['code' => 'PH_I_BAYELSA', 'name' => 'PH I BAYELSA', 'location' => 'Port Harcourt'],
                ['code' => 'PH_II_ONNE', 'name' => 'PH II ONNE', 'location' => 'Port Harcourt'],
                ['code' => 'EDO_DELTA', 'name' => 'EDO DELTA', 'location' => 'Edo'],
                ['code' => 'CR_AK', 'name' => 'CR AK', 'location' => 'Cross River'],
                ['code' => 'EMC', 'name' => 'EMC', 'location' => 'Enugu'],
                ['code' => 'PCA_ZONE_C', 'name' => 'PCA Zone C', 'location' => 'Port Harcourt'],
                ['code' => 'FOU_C', 'name' => 'FOU C', 'location' => 'Port Harcourt'],
            ],
            'ZONE_D' => [
                ['code' => 'BA_GM', 'name' => 'BA GM', 'location' => 'Bauchi'],
                ['code' => 'AD_TR', 'name' => 'AD TR', 'location' => 'Adamawa'],
                ['code' => 'BN_YB', 'name' => 'BN YB', 'location' => 'Benue'],
                ['code' => 'FOU_D', 'name' => 'FOU D', 'location' => 'Bauchi'],
                ['code' => 'PCA_ZONE_D', 'name' => 'PCA Zone D', 'location' => 'Bauchi'],
            ],
        ];

        $commandModels = [];
        $totalCommands = 0;
        foreach ($commandsByZone as $zoneCode => $commands) {
            $zone = $zoneModels[$zoneCode];
            foreach ($commands as $commandData) {
                // Use updateOrCreate to ensure commands are updated with correct zone_id
                $command = Command::updateOrCreate(
                    ['code' => $commandData['code']],
                    [
                        'name' => $commandData['name'],
                        'location' => $commandData['location'],
                        'zone_id' => $zone->id,
                        'is_active' => true,
                    ]
                );
                $commandModels[$commandData['code']] = $command;
                $totalCommands++;
            }
        }
        $this->command->info('✅ Created/Updated ' . $totalCommands . ' commands across all zones');

        // Create Test Users
        $this->createTestUsers($commandModels);
    }

    private function createTestUsers($commandModels)
    {
        // Get Roles
        $hrdRole = Role::where('name', 'HRD')->first();
        $zoneCoordinatorRole = Role::where('name', 'Zone Coordinator')->first();
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        $officerRole = Role::where('name', 'Officer')->first();
        $assessorRole = Role::where('name', 'Assessor')->first();
        $validatorRole = Role::where('name', 'Validator')->first();
        $areaControllerRole = Role::where('name', 'Area Controller')->first();

        // Create HRD User (if not exists)
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

        // Create Zone Coordinator for Zone A
        if ($zoneCoordinatorRole && isset($commandModels['APAPA'])) {
            $zoneCoordA = User::firstOrCreate(
                ['email' => 'zonecoord.a@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$zoneCoordA->hasRole('Zone Coordinator')) {
                $zoneCoordA->roles()->attach($zoneCoordinatorRole->id, [
                    'command_id' => $commandModels['APAPA']->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Create Zone Coordinator for Zone B
        if ($zoneCoordinatorRole && isset($commandModels['KADUNA'])) {
            $zoneCoordB = User::firstOrCreate(
                ['email' => 'zonecoord.b@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$zoneCoordB->hasRole('Zone Coordinator')) {
                $zoneCoordB->roles()->attach($zoneCoordinatorRole->id, [
                    'command_id' => $commandModels['KADUNA']->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Create Zone Coordinator for Zone C
        if ($zoneCoordinatorRole && isset($commandModels['PH_I_BAYELSA'])) {
            $zoneCoordC = User::firstOrCreate(
                ['email' => 'zonecoord.c@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$zoneCoordC->hasRole('Zone Coordinator')) {
                $zoneCoordC->roles()->attach($zoneCoordinatorRole->id, [
                    'command_id' => $commandModels['PH_I_BAYELSA']->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Create Zone Coordinator for Zone D
        if ($zoneCoordinatorRole && isset($commandModels['BA_GM'])) {
            $zoneCoordD = User::firstOrCreate(
                ['email' => 'zonecoord.d@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$zoneCoordD->hasRole('Zone Coordinator')) {
                $zoneCoordD->roles()->attach($zoneCoordinatorRole->id, [
                    'command_id' => $commandModels['BA_GM']->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Create Staff Officer for APAPA
        if ($staffOfficerRole && isset($commandModels['APAPA'])) {
            $staffOfficer = User::firstOrCreate(
                ['email' => 'staff.apapa@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
            if (!$staffOfficer->hasRole('Staff Officer')) {
                $staffOfficer->roles()->attach($staffOfficerRole->id, [
                    'command_id' => $commandModels['APAPA']->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
        }

        // Create Test Officers with different grade levels
        $this->createTestOfficers($commandModels, $officerRole);
    }

    private function createTestOfficers($commandModels, $officerRole)
    {
        if (!$officerRole) return;

        // Grade levels for distribution
        $gradeLevels = ['GL05', 'GL06', 'GL07', 'GL08'];
        $sexOptions = ['M', 'F'];
        // Use standard rank abbreviations matching manning request form
        $ranks = [
            'CGC',
            'DCG',
            'ACG',
            'CC',
            'DC',
            'AC',
            'CSC',
            'SC',
            'DSC',
            'ASC I',
            'ASC II',
            'IC',
            'AIC',
            'CA I',
            'CA II',
            'CA III',
        ];
        
        // Counter for service numbers
        $serviceNumberCounter = 50001;
        
        // Create officers for ALL commands
        foreach ($commandModels as $commandCode => $command) {
            // Create 2-3 officers per command (varying by command type)
            $officersPerCommand = in_array($commandCode, ['APAPA', 'FCT', 'KADUNA', 'PH_I_BAYELSA']) ? 3 : 2;
            
            for ($i = 1; $i <= $officersPerCommand; $i++) {
                $serviceNumber = 'NCS' . str_pad($serviceNumberCounter++, 5, '0', STR_PAD_LEFT);
                $gradeLevel = $gradeLevels[($serviceNumberCounter - 50001) % count($gradeLevels)];
                $sex = $sexOptions[($serviceNumberCounter - 50001) % count($sexOptions)];
                $rank = $ranks[($serviceNumberCounter - 50001) % count($ranks)];
                
                // Generate initials and surname based on command
                $initials = $this->generateInitials($commandCode, $i);
                $surname = $this->generateSurname($commandCode, $i);
                $email = 'officer.' . strtolower($commandCode) . '.' . $i . '@ncs.gov.ng';
                
                // Create or get officer
                $officer = Officer::firstOrCreate(
                    ['service_number' => $serviceNumber],
                    [
                        'initials' => $initials,
                        'surname' => $surname,
                        'sex' => $sex,
                        'date_of_birth' => '198' . (5 + ($serviceNumberCounter % 10)) . '-0' . (1 + ($serviceNumberCounter % 9)) . '-' . str_pad(1 + ($serviceNumberCounter % 28), 2, '0', STR_PAD_LEFT),
                        'date_of_first_appointment' => '201' . ($serviceNumberCounter % 5) . '-01-01',
                        'date_of_present_appointment' => '202' . ($serviceNumberCounter % 4) . '-01-01',
                        'substantive_rank' => $rank,
                        'salary_grade_level' => $gradeLevel,
                        'state_of_origin' => $this->getStateForCommand($commandCode),
                        'lga' => 'Test LGA',
                        'geopolitical_zone' => $this->getGeopoliticalZone($commandCode),
                        'marital_status' => ($serviceNumberCounter % 2 == 0) ? 'Married' : 'Single',
                        'entry_qualification' => 'B.Sc',
                        'present_station' => $command->id,
                        'date_posted_to_station' => now()->subDays(rand(30, 365)),
                        'residential_address' => $command->location . ' Test Address',
                        'permanent_home_address' => $command->location . ' Permanent Address',
                        'phone_number' => '080' . str_pad($serviceNumberCounter, 8, '0', STR_PAD_LEFT),
                        'email' => $email,
                        'is_active' => true,
                    ]
                );

                // Create user for officer if not exists
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                    ]
                );

                // Link officer to user
                if (!$officer->user_id) {
                    $officer->update(['user_id' => $user->id]);
                }

                // Assign officer role
                if (!$user->hasRole('Officer')) {
                    $user->roles()->attach($officerRole->id, [
                        'command_id' => $command->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
                
                // Ensure present_station is set
                if (!$officer->present_station || $officer->present_station != $command->id) {
                    $officer->update(['present_station' => $command->id]);
                }
            }
        }
    }
    
    private function generateInitials($commandCode, $index)
    {
        $prefixes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $suffixes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $first = $prefixes[($index - 1) % count($prefixes)];
        $second = $suffixes[(ord($commandCode[0]) + $index) % count($suffixes)];
        return $first . '.' . $second . '.';
    }
    
    private function generateSurname($commandCode, $index)
    {
        $surnames = ['Adeyemi', 'Bello', 'Chukwu', 'Danjuma', 'Eze', 'Falana', 'Garba', 'Hassan', 'Ibrahim', 'Johnson', 
                     'Kalu', 'Lawal', 'Mohammed', 'Nwosu', 'Okafor', 'Patel', 'Quadri', 'Raji', 'Suleiman', 'Tunde',
                     'Umar', 'Victor', 'Williams', 'Yusuf', 'Zakari'];
        $baseIndex = (ord($commandCode[0]) + $index) % count($surnames);
        return $surnames[$baseIndex] . ' ' . $commandCode;
    }
    
    private function getStateForCommand($commandCode)
    {
        $stateMap = [
            // HEADQUARTERS
            'CGC_OFFICE' => 'Abuja', 'FATS_HQTRS' => 'Abuja', 'SRP_HQTRS' => 'Abuja',
            'HRD_HQTRS' => 'Abuja', 'EI_I_HQTRS' => 'Abuja', 'EXCISE_FTZ_HQTRS' => 'Abuja',
            'TRADOC' => 'Abuja', 'LEGAL_UNIT' => 'Abuja', 'ICT_MOD_HQTRS' => 'Abuja',
            // ZONE A
            'APAPA' => 'Lagos', 'TCIP' => 'Lagos', 'MMIA' => 'Lagos', 'MMAC' => 'Lagos',
            'KLTC' => 'Lagos', 'LAGOS_INDUSTRIAL' => 'Lagos', 'SEME' => 'Lagos',
            'OGUN_I' => 'Ogun', 'OGUN_II' => 'Ogun', 'OYO_OSUN' => 'Oyo',
            'ONDO_EKITI' => 'Ondo', 'PTML' => 'Lagos', 'LEKKI_FTZ' => 'Lagos',
            'LILYPOND' => 'Lagos', 'WMC' => 'Lagos', 'IKORODU' => 'Lagos', 'FOU_A' => 'Lagos',
            'PCA_ZONE_A' => 'Lagos',
            // ZONE B
            'KADUNA' => 'Kaduna', 'KANO_JIGAWA' => 'Kano', 'SOKOTO_ZAMFARA' => 'Sokoto',
            'NIGER_KOGI' => 'Niger', 'FCT' => 'Abuja', 'KWARA' => 'Kwara', 'KEBBI' => 'Kebbi',
            'NWM' => 'Kaduna', 'PT_NA_BE' => 'Kaduna', 'FOU_B' => 'Kaduna',
            'PCA_ZONE_B' => 'Kaduna',
            // ZONE C
            'PH_I_BAYELSA' => 'Rivers', 'PH_II_ONNE' => 'Rivers', 'AN_EB_EN' => 'Anambra',
            'IMO_ABIA' => 'Imo', 'EDO_DELTA' => 'Edo', 'CR_AK' => 'Cross River',
            'EMC' => 'Enugu', 'FOU_C' => 'Rivers', 'PCA_ZONE_C' => 'Rivers',
            // ZONE D
            'BA_GM' => 'Bauchi', 'AD_TR' => 'Adamawa', 'BN_YB' => 'Benue', 'FOU_D' => 'Bauchi',
            'PCA_ZONE_D' => 'Bauchi',
        ];
        
        return $stateMap[$commandCode] ?? 'Abuja';
    }
    
    private function getGeopoliticalZone($commandCode)
    {
        $zoneMap = [
            // HEADQUARTERS
            'CGC_OFFICE' => 'North Central', 'FATS_HQTRS' => 'North Central', 'SRP_HQTRS' => 'North Central',
            'HRD_HQTRS' => 'North Central', 'EI_I_HQTRS' => 'North Central', 'EXCISE_FTZ_HQTRS' => 'North Central',
            'TRADOC' => 'North Central', 'LEGAL_UNIT' => 'North Central', 'ICT_MOD_HQTRS' => 'North Central',
            // ZONE A
            'APAPA' => 'South West', 'TCIP' => 'South West', 'MMIA' => 'South West',
            'MMAC' => 'South West', 'KLTC' => 'South West', 'LAGOS_INDUSTRIAL' => 'South West',
            'SEME' => 'South West', 'OGUN_I' => 'South West', 'OGUN_II' => 'South West',
            'OYO_OSUN' => 'South West', 'ONDO_EKITI' => 'South West', 'PTML' => 'South West',
            'LEKKI_FTZ' => 'South West', 'LILYPOND' => 'South West', 'WMC' => 'South West',
            'IKORODU' => 'South West', 'FOU_A' => 'South West', 'PCA_ZONE_A' => 'South West',
            // ZONE B
            'KADUNA' => 'North West', 'KANO_JIGAWA' => 'North West', 'SOKOTO_ZAMFARA' => 'North West',
            'NIGER_KOGI' => 'North Central', 'FCT' => 'North Central', 'KWARA' => 'North Central',
            'KEBBI' => 'North West', 'NWM' => 'North West', 'PT_NA_BE' => 'North Central',
            'FOU_B' => 'North West', 'PCA_ZONE_B' => 'North West',
            // ZONE C
            'PH_I_BAYELSA' => 'South South', 'PH_II_ONNE' => 'South South', 'AN_EB_EN' => 'South East',
            'IMO_ABIA' => 'South East', 'EDO_DELTA' => 'South South', 'CR_AK' => 'South South',
            'EMC' => 'South East', 'FOU_C' => 'South South', 'PCA_ZONE_C' => 'South South',
            // ZONE D
            'BA_GM' => 'North East', 'AD_TR' => 'North East', 'BN_YB' => 'North Central',
            'FOU_D' => 'North East', 'PCA_ZONE_D' => 'North East',
        ];
        
        return $zoneMap[$commandCode] ?? 'North Central';
    }
}

