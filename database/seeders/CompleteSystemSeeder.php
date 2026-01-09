<?php

namespace Database\Seeders;

use App\Models\AccountChangeRequest;
use App\Models\APERForm;
use App\Models\APERTimeline;
use App\Models\Zone;
use App\Models\InternalStaffOrder;
use App\Models\ReleaseLetter;
use App\Models\OfficerDocument;
use App\Models\Investigation;
use App\Models\NextOfKinChangeRequest;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Command;
use App\Models\DeceasedOfficer;
use App\Models\DutyRoster;
use App\Models\Emolument;
use App\Models\EmolumentAssessment;
use App\Models\EmolumentTimeline;
use App\Models\EmolumentValidation;
use App\Models\LeaveApplication;
use App\Models\LeaveApproval;
use App\Models\LeaveType;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\MovementOrder;
use App\Models\NextOfKin;
use App\Models\Officer;
use App\Models\OfficerCourse;
use App\Models\OfficerPosting;
use App\Models\OfficerQuarter;
use App\Models\PassApplication;
use App\Models\PassApproval;
use App\Models\PromotionEligibilityCriterion;
use App\Models\Quarter;
use App\Models\Query;
use App\Models\Role;
use App\Models\RosterAssignment;
use App\Models\StaffOrder;
use App\Models\TrainingResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a complete system with all roles and functions active
     * Most officers in APAPA command, 1-2 in other commands
     */
    public function run(): void
    {
        $this->command->info("🚀 Starting Complete System Seeding...");
        $this->command->info("=" . str_repeat("=", 60));

        // Step 1: Delete existing seeded data
        $this->command->info("\n📋 Step 1: Cleaning up existing seeded data...");
        $this->deleteSeededData();

        // Step 2: Ensure prerequisites exist
        $this->command->info("\n📋 Step 2: Ensuring prerequisites exist...");
        $commands = $this->ensurePrerequisites();
        
        // Ensure APAPA command exists (required for testing)
        $apapaCommand = Command::where('code', 'APAPA')->first();
        if (!$apapaCommand) {
            $this->command->warn('⚠️  APAPA command not found after ZoneAndCommandSeeder. Creating it now...');
            $zoneA = Zone::where('code', 'ZONE_A')->first();
            if (!$zoneA) {
                $zoneA = Zone::firstOrCreate(
                    ['code' => 'ZONE_A'],
                    ['name' => 'Zone A HQ', 'description' => 'Zone A Headquarters', 'is_active' => true]
                );
                $this->command->info("   ✓ Created ZONE_A");
            }
            $apapaCommand = Command::firstOrCreate(
                ['code' => 'APAPA'],
                [
                    'name' => 'APAPA',
                    'location' => 'Lagos',
                    'zone_id' => $zoneA->id,
                    'is_active' => true,
                ]
            );
            $this->command->info("   ✓ Created APAPA command (ID: {$apapaCommand->id})");
        } else {
            $this->command->info("   ✓ APAPA command found (ID: {$apapaCommand->id})");
        }

        // Step 3: Create users for ALL roles
        $this->command->info("\n📋 Step 3: Creating users for all roles...");
        $roleUsers = $this->createAllRoleUsers($commands, $apapaCommand);

        // Step 4: Create officers (most in APAPA, 1-2 in other commands)
        $this->command->info("\n📋 Step 4: Creating officers (most in APAPA)...");
        $officers = $this->createOfficers($commands, $apapaCommand, $roleUsers['HRD']);

        // Step 5: Create supporting data for all officers
        $this->command->info("\n📋 Step 5: Creating supporting data...");
        $this->createSupportingData($officers, $commands, $roleUsers);

        // Step 6: Create functional data (emoluments, leave, pass, etc.)
        $this->command->info("\n📋 Step 6: Creating functional data...");
        $this->createFunctionalData($officers, $commands, $roleUsers, $apapaCommand);

        $this->command->info("\n" . str_repeat("=", 62));
        $this->command->info("✅ Complete System Seeding Finished Successfully!");
        $this->command->info("   Created: All roles + " . count($officers) . " officers (5 per rank per command across 5 commands)");
        $this->command->info("   All app functions are active!");
        $this->command->info(str_repeat("=", 62));
    }

    private function deleteSeededData(): void
    {
        // Delete in reverse order of dependencies
        DB::table('account_change_requests')->delete();
        DB::table('aper_forms')->delete();
        DB::table('aper_timelines')->delete();
        DB::table('chat_messages')->delete();
        DB::table('chat_room_members')->delete();
        DB::table('deceased_officers')->delete();
        DB::table('emolument_validations')->delete();
        DB::table('emolument_assessments')->delete();
        DB::table('emoluments')->delete();
        DB::table('emolument_timelines')->delete();
        DB::table('leave_approvals')->delete();
        DB::table('leave_applications')->delete();
        DB::table('manning_deployment_assignments')->delete();
        DB::table('manning_deployments')->delete();
        DB::table('manning_request_items')->delete();
        DB::table('manning_requests')->update(['approved_by' => null]);
        DB::table('movement_orders')->delete();
        DB::table('next_of_kin')->delete();
        DB::table('next_of_kin_change_requests')->delete();
        DB::table('officer_courses')->delete();
        DB::table('officer_documents')->delete();
        DB::table('officer_postings')->delete();
        DB::table('officer_quarters')->delete();
        DB::table('pass_approvals')->delete();
        DB::table('pass_applications')->delete();
        DB::table('promotion_eligibility_list_items')->delete();
        DB::table('promotions')->delete();
        DB::table('quarter_requests')->delete();
        DB::table('queries')->delete();
        DB::table('release_letters')->delete();
        DB::table('retirement_alerts')->delete();
        DB::table('retirement_list_items')->delete();
        DB::table('roster_assignments')->delete();
        DB::table('duty_rosters')->update(['approved_by' => null]);
        DB::table('staff_orders')->delete();
        DB::table('training_results')->delete();
        
        DB::table('chat_rooms')->whereNotNull('command_id')->delete();
        DB::table('quarters')->delete();
        DB::table('promotion_eligibility_criteria')->delete();
        DB::table('promotion_eligibility_lists')->delete();

        $officerUserIds = Officer::pluck('user_id')->filter()->toArray();
        Officer::query()->delete();
        
        $systemEmails = ['hrd@ncs.gov.ng', 'officer@ncs.gov.ng', 'cgc@ncs.gov.ng'];
        if (!empty($officerUserIds)) {
            User::whereIn('id', $officerUserIds)
                ->whereNotIn('email', $systemEmails)
                ->delete();
        }
        
        $this->command->info("   ✓ Deleted all seeded officers and related data");
    }

    private function ensurePrerequisites(): \Illuminate\Database\Eloquent\Collection
    {
        // Always ensure zones and commands are up-to-date (even if they exist)
        // This ensures correct zone relationships and command data
        $this->call(ZoneAndCommandSeeder::class);
        $commands = Command::all();

        // Ensure roles exist (will updateOrCreate if needed)
        if (Role::count() < 18) {
            $this->call(RoleSeeder::class);
        }

        // Ensure leave types exist
        if (LeaveType::count() < 5) {
            $this->call(LeaveTypeSeeder::class);
        }

        return $commands;
    }

    private function createAllRoleUsers($commands, $apapaCommand): array
    {
        $roleUsers = [];
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();

        // Get all roles
        $roles = [
            'HRD' => Role::where('code', 'HRD')->first(),
            'ADMIN' => Role::where('code', 'ADMIN')->first(),
            'STAFF_OFFICER' => Role::where('code', 'STAFF_OFFICER')->first(),
            'BUILDING_UNIT' => Role::where('code', 'BUILDING_UNIT')->first(),
            'ESTABLISHMENT' => Role::where('code', 'ESTABLISHMENT')->first(),
            'ACCOUNTS' => Role::where('code', 'ACCOUNTS')->first(),
            'BOARD' => Role::where('code', 'BOARD')->first(),
            'ASSESSOR' => Role::where('code', 'ASSESSOR')->first(),
            'VALIDATOR' => Role::where('code', 'VALIDATOR')->first(),
            'AUDITOR' => Role::where('code', 'AUDITOR')->first(),
            'AREA_CONTROLLER' => Role::where('code', 'AREA_CONTROLLER')->first(),
            'DC_ADMIN' => Role::where('code', 'DC_ADMIN')->first(),
            'WELFARE' => Role::where('code', 'WELFARE')->first(),
            'TRADOC' => Role::where('code', 'TRADOC')->first(),
            'ICT' => Role::where('code', 'ICT')->first(),
            'INVESTIGATION_UNIT' => Role::where('code', 'INVESTIGATION_UNIT')->first(),
            'CGC' => Role::where('code', 'CGC')->first(),
        ];

        // HRD (System-wide)
        if ($roles['HRD']) {
            $user = User::firstOrCreate(
                ['email' => 'hrd@ncs.gov.ng'],
                ['password' => Hash::make('password123'), 'is_active' => true, 'email_verified_at' => now()]
            );
            if (!$user->hasRole('HRD')) {
                $user->roles()->attach($roles['HRD']->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $roleUsers['HRD'] = $user;
            $this->command->info("   ✓ Created HRD user");
        }

        // CGC (System-wide)
        if ($roles['CGC']) {
            $user = User::firstOrCreate(
                ['email' => 'cgc@ncs.gov.ng'],
                ['password' => Hash::make('password123'), 'is_active' => true, 'email_verified_at' => now()]
            );
            if (!$user->hasRole('CGC')) {
                $user->roles()->attach($roles['CGC']->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $roleUsers['CGC'] = $user;
            $this->command->info("   ✓ Created CGC user");
        }

        // System-wide roles (no command assignment)
        $systemWideRoles = ['ESTABLISHMENT', 'ACCOUNTS', 'AUDITOR', 'BOARD', 'WELFARE', 'TRADOC', 'ICT', 'INVESTIGATION_UNIT'];
        foreach ($systemWideRoles as $roleCode) {
            if ($roles[$roleCode]) {
                $email = strtolower($roleCode) . '@ncs.gov.ng';
                $user = User::firstOrCreate(
                    ['email' => $email],
                    ['password' => Hash::make('password123'), 'is_active' => true, 'email_verified_at' => now(), 'created_by' => $hrdUser->id ?? null]
                );
                if (!$user->hasRole($roles[$roleCode]->name)) {
                    $user->roles()->attach($roles[$roleCode]->id, ['is_active' => true, 'assigned_at' => now()]);
                }
                $roleUsers[$roleCode] = $user;
                $this->command->info("   ✓ Created {$roleCode} user");
            }
        }

        // Command-level roles (for APAPA)
        if ($apapaCommand) {
            $commandRoles = [
                'ADMIN' => 'admin.apapa@ncs.gov.ng',
                'STAFF_OFFICER' => 'staff.apapa@ncs.gov.ng',
                'BUILDING_UNIT' => 'building.apapa@ncs.gov.ng',
                'ASSESSOR' => 'assessor.apapa@ncs.gov.ng',
                'VALIDATOR' => 'validator.apapa@ncs.gov.ng',
                'AREA_CONTROLLER' => 'areacontroller.apapa@ncs.gov.ng',
                'DC_ADMIN' => 'dcadmin.apapa@ncs.gov.ng',
            ];

            foreach ($commandRoles as $roleCode => $email) {
                if ($roles[$roleCode]) {
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        ['password' => Hash::make('password123'), 'is_active' => true, 'email_verified_at' => now(), 'created_by' => $hrdUser->id ?? null]
                    );
                    if (!$user->hasRole($roles[$roleCode]->name)) {
                        $user->roles()->attach($roles[$roleCode]->id, [
                            'command_id' => $apapaCommand->id,
                            'is_active' => true,
                            'assigned_at' => now()
                        ]);
                    }
                    $roleUsers[$roleCode] = $user;
                    $this->command->info("   ✓ Created {$roleCode} user for APAPA");
                }
            }
        }

        return $roleUsers;
    }

    private function createOfficers($commands, $apapaCommand, $hrdUser): array
    {
        // Ensure APAPA command exists
        if (!$apapaCommand) {
            $this->command->error('❌ APAPA command is required but not found! Cannot create officers.');
            return [];
        }
        
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

        $surnames = ['Adebayo', 'Okafor', 'Ibrahim', 'Okoro', 'Musa', 'Adeyemi', 'Bello', 'Chukwu', 'Yusuf', 'Obi', 
                     'Ali', 'Adekunle', 'Mohammed', 'Nwankwo', 'Sani', 'Eze', 'Garba', 'Okafor', 'Bello', 'Adeleke'];
        $firstNames = ['John', 'Mary', 'David', 'Sarah', 'Michael', 'Grace', 'James', 'Patricia', 'Robert', 'Linda',
                       'William', 'Elizabeth', 'Richard', 'Jennifer', 'Joseph', 'Maria', 'Thomas', 'Susan', 'Charles', 'Jessica'];
        $states = ['Lagos', 'Abuja', 'Kano', 'Rivers', 'Ogun', 'Kaduna', 'Delta', 'Enugu', 'Plateau', 'Oyo'];
        $lgas = ['Ikeja', 'Mushin', 'Surulere', 'Victoria Island', 'Garki', 'Wuse', 'Kaduna North', 'Port Harcourt', 'Abeokuta', 'Ibadan'];
        $disciplines = ['Accounting', 'Law', 'Computer Science', 'Economics', 'Business Administration', 'Public Administration'];
        $banks = ['Zenith Bank', 'GTBank', 'First Bank', 'UBA', 'Access Bank'];
        $pfas = ['Stanbic IBTC Pension', 'ARM Pension', 'Premium Pension', 'Leadway Pension'];
        $sexes = ['M', 'F'];
        $letters = range('A', 'Z');
        $relationships = ['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Son', 'Daughter'];

        $officerRole = Role::where('code', 'OFFICER')->first();
        $officers = [];
        $usedServiceNumbers = [];
        $rankCounts = [];

        // Get 5 commands to create officers in
        $targetCommands = $commands->where('is_active', true)
            ->take(5)
            ->values();
        
        if ($targetCommands->isEmpty()) {
            $this->command->error('❌ No active commands found! Cannot create officers.');
            return [];
        }

        // Create 5 officers per rank per command (16 ranks × 5 commands × 5 officers = 400 total)
        foreach ($targetCommands as $commandIndex => $command) {
            $this->command->info("   Processing command " . ($commandIndex + 1) . "/{$targetCommands->count()}: {$command->name}");
            
            foreach ($ranks as $rank) {
                $rankCounts[$rank] = ($rankCounts[$rank] ?? 0);
                
                // Create 5 officers of this rank in this command
                for ($i = 1; $i <= 5; $i++) {
                    do {
                        $serviceNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                        $fullServiceNumber = 'NCS' . $serviceNumber;
                    } while (in_array($serviceNumber, $usedServiceNumbers) || Officer::where('service_number', $fullServiceNumber)->exists());
                    $usedServiceNumbers[] = $serviceNumber;
                $sex = $sexes[array_rand($sexes)];
                $surname = $surnames[array_rand($surnames)];
                $firstName = $firstNames[array_rand($firstNames)];
                $initials = strtoupper($letters[array_rand($letters)] . '.' . $letters[array_rand($letters)]);

                $yearsInRank = rand(1, 5) + (rand(0, 100) / 100);
                $dateOfPresentAppointment = Carbon::now()->subYears((int)$yearsInRank)
                    ->subMonths((int)(($yearsInRank - (int)$yearsInRank) * 12))
                    ->subDays(rand(0, 30));

                $dateOfFirstAppointment = $dateOfPresentAppointment->copy()->subYears(rand(3, 10));
                $dateOfBirth = Carbon::now()->subYears(rand(25, 50))->subDays(rand(0, 365));
                $datePostedToStation = $dateOfPresentAppointment->copy()->subMonths(rand(0, 6));

                $user = User::firstOrCreate(
                    ['email' => "officer{$serviceNumber}@ncs.gov.ng"],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'created_by' => $hrdUser->id ?? null,
                    ]
                );

                // Attach role if not already attached
                if (!$user->hasRole('OFFICER')) {
                    $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);
                }

                $officer = Officer::create([
                    'user_id' => $user->id,
                    'service_number' => $serviceNumber,
                    'initials' => $initials,
                    'surname' => $surname,
                    'sex' => $sex,
                    'date_of_birth' => $dateOfBirth,
                    'date_of_first_appointment' => $dateOfFirstAppointment,
                    'date_of_present_appointment' => $dateOfPresentAppointment,
                    'substantive_rank' => $rank,
                    'salary_grade_level' => $this->getSalaryGradeForRank($rank),
                    'state_of_origin' => $states[array_rand($states)],
                    'lga' => $lgas[array_rand($lgas)],
                    'geopolitical_zone' => 'South-West',
                    'marital_status' => rand(0, 1) ? 'Married' : 'Single',
                    'entry_qualification' => ['B.Sc', 'B.A', 'B.Eng', 'HND'][array_rand(['B.Sc', 'B.A', 'B.Eng', 'HND'])],
                    'discipline' => $disciplines[array_rand($disciplines)],
                    'additional_qualification' => rand(0, 3) ? null : ['M.Sc', 'M.B.A', 'LLM'][array_rand(['M.Sc', 'M.B.A', 'LLM'])],
                    'residential_address' => $states[array_rand($states)] . ' State, ' . $lgas[array_rand($lgas)] . ' LGA',
                    'permanent_home_address' => $states[array_rand($states)] . ' State, ' . $lgas[array_rand($lgas)] . ' LGA',
                    'present_station' => $command->id,
                    'date_posted_to_station' => $datePostedToStation,
                    'phone_number' => '080' . rand(10000000, 99999999),
                    'email' => $user->email,
                    'bank_name' => $banks[array_rand($banks)],
                    'bank_account_number' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                    'sort_code' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'pfa_name' => $pfas[array_rand($pfas)],
                    'rsa_number' => 'PEN' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
                    'unit' => 'Unit ' . rand(1, 10),
                    'interdicted' => false,
                    'suspended' => false,
                    'dismissed' => false,
                    'quartered' => false,
                    'is_deceased' => false,
                    'is_active' => true,
                    'profile_picture_url' => 'officers/default.png',
                    'onboarding_status' => 'completed',
                    'onboarding_completed_at' => now()->subDays(rand(1, 30)),
                    'verification_status' => 'verified',
                    'verified_at' => now()->subDays(rand(1, 30)),
                    'created_by' => $hrdUser->id ?? null,
                ]);

                    $officers[] = $officer;
                    $rankCounts[$rank]++;
                }
            }
        }

        $this->command->info("   ✓ Created " . count($officers) . " officers (5 per rank per command)");
        
        // Show distribution by command and rank
        $distribution = collect($officers)->groupBy('present_station')->map(function($group, $commandId) {
            $command = Command::find($commandId);
            $rankDistribution = $group->groupBy('substantive_rank')->map->count();
            return [
                'command' => $command->name ?? 'Unknown', 
                'count' => $group->count(),
                'ranks' => $rankDistribution
            ];
        });
        
        foreach ($distribution as $dist) {
            $this->command->info("      - {$dist['command']}: {$dist['count']} officers (" . $dist['ranks']->count() . " ranks, 5 per rank)");
        }
        return $officers;
    }

    private function createSupportingData($officers, $commands, $roleUsers): void
    {
        $hrdUser = $roleUsers['HRD'] ?? User::where('email', 'hrd@ncs.gov.ng')->first();
        $relationships = ['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Son', 'Daughter'];

        // Create Next of Kin for all officers
        foreach ($officers as $officer) {
            NextOfKin::create([
                'officer_id' => $officer->id,
                'name' => $officer->surname . ' ' . ['Adebayo', 'Okafor', 'Ibrahim'][array_rand(['Adebayo', 'Okafor', 'Ibrahim'])],
                'relationship' => $relationships[array_rand($relationships)],
                'phone_number' => '080' . rand(10000000, 99999999),
                'address' => $officer->residential_address,
                'is_primary' => true,
            ]);
        }
        $this->command->info("   ✓ Created Next of Kin for all officers");

        // Create Chat Rooms and add officers
        foreach ($commands as $command) {
            ChatRoom::firstOrCreate(
                ['command_id' => $command->id, 'room_type' => 'COMMAND'],
                [
                    'name' => $command->name . ' Chat Room',
                    'description' => 'Main chat room for ' . $command->name,
                    'is_active' => true,
                ]
            );
        }

        foreach ($officers as $officer) {
            if ($officer->present_station) {
                $chatRoom = ChatRoom::where('command_id', $officer->present_station)
                    ->where('room_type', 'COMMAND')
                    ->first();
                
                if ($chatRoom) {
                    ChatRoomMember::firstOrCreate([
                        'chat_room_id' => $chatRoom->id,
                        'officer_id' => $officer->id,
                    ], [
                        'added_by' => $hrdUser->id ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        }
        $this->command->info("   ✓ Created Chat Rooms and added officers");

        // Create Officer Postings
        foreach ($officers as $officer) {
            OfficerPosting::create([
                'officer_id' => $officer->id,
                'command_id' => $officer->present_station,
                'posting_date' => $officer->date_posted_to_station ?? now()->subYears(rand(1, 3)),
                'is_current' => true,
            ]);
        }
        $this->command->info("   ✓ Created Officer Postings");
    }

    private function createFunctionalData($officers, $commands, $roleUsers, $apapaCommand): void
    {
        $hrdUser = $roleUsers['HRD'] ?? User::where('email', 'hrd@ncs.gov.ng')->first();
        $assessorUser = $roleUsers['ASSESSOR'] ?? null;
        $validatorUser = $roleUsers['VALIDATOR'] ?? null;
        $areaControllerUser = $roleUsers['AREA_CONTROLLER'] ?? null;
        $dcAdminUser = $roleUsers['DC_ADMIN'] ?? null;
        $staffOfficerUser = $roleUsers['STAFF_OFFICER'] ?? null;
        $buildingUnitUser = $roleUsers['BUILDING_UNIT'] ?? null;

        // 1. Create Emolument Timeline
        $timeline = EmolumentTimeline::firstOrCreate(
            ['year' => 2025],
            [
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'is_active' => true,
                'created_by' => $hrdUser->id,
            ]
        );

        // Create Emoluments for APAPA officers
        $apapaOfficers = collect($officers)->filter(function($o) use ($apapaCommand) {
            return $o->present_station == $apapaCommand->id;
        })->take(40);

        $emolumentCount = 0;
        foreach ($apapaOfficers as $index => $officer) {
            $emolument = Emolument::create([
                'officer_id' => $officer->id,
                'timeline_id' => $timeline->id,
                'year' => 2025,
                'status' => 'RAISED',
                'bank_name' => $officer->bank_name,
                'bank_account_number' => $officer->bank_account_number,
                'pfa_name' => $officer->pfa_name,
                'rsa_pin' => $officer->rsa_number,
                'submitted_at' => now()->subDays(rand(1, 5)),
            ]);
            $emolumentCount++;

            if ($index < 30 && $assessorUser) {
                $assessment = EmolumentAssessment::create([
                    'emolument_id' => $emolument->id,
                    'assessor_id' => $assessorUser->id,
                    'assessment_status' => 'APPROVED',
                    'comments' => 'Assessment completed',
                ]);
                $emolument->update(['status' => 'ASSESSED']);

                if ($index < 20 && $validatorUser) {
                    EmolumentValidation::create([
                        'emolument_id' => $emolument->id,
                        'assessment_id' => $assessment->id,
                        'validator_id' => $validatorUser->id,
                        'validation_status' => 'APPROVED',
                        'comments' => 'Validation completed',
                    ]);
                    $emolument->update(['status' => 'VALIDATED']);
                }
            }
        }
        $this->command->info("   ✓ Created {$emolumentCount} Emoluments with workflow states");

        // 2. Create Leave Applications
        $leaveTypes = LeaveType::all();
        $leaveReasons = ['Annual leave', 'Sick leave', 'Maternity leave', 'Personal reasons', 'Family emergency'];
        $leaveCount = 0;

        if ($leaveTypes->isNotEmpty() && $staffOfficerUser && $dcAdminUser) {
            foreach ($apapaOfficers->take(30) as $index => $officer) {
                $leaveApp = LeaveApplication::create([
                    'officer_id' => $officer->id,
                    'leave_type_id' => $leaveTypes->random()->id,
                    'start_date' => now()->addDays(rand(10, 30)),
                    'end_date' => now()->addDays(rand(35, 50)),
                    'number_of_days' => rand(10, 20),
                    'reason' => $leaveReasons[array_rand($leaveReasons)],
                    'status' => 'PENDING',
                    'submitted_at' => now()->subDays(rand(1, 10)),
                ]);
                $leaveCount++;

                if ($index < 25) {
                    $leaveApp->update(['status' => 'MINUTED']);
                    $leaveApproval = LeaveApproval::create([
                        'leave_application_id' => $leaveApp->id,
                        'staff_officer_id' => $staffOfficerUser->id,
                        'dc_admin_id' => null,
                        'area_controller_id' => null,
                        'approval_status' => 'MINUTED',
                        'minuted_at' => now()->subDays(rand(1, 5)),
                    ]);

                    if ($index < 15) {
                        $leaveApproval->update([
                            'dc_admin_id' => $dcAdminUser->id,
                            'approval_status' => 'APPROVED',
                            'approved_at' => now()->subDays(rand(0, 3)),
                        ]);
                        $leaveApp->update(['status' => 'APPROVED']);
                    }
                }
            }
        }
        $this->command->info("   ✓ Created {$leaveCount} Leave Applications with workflow states");

        // 3. Create Pass Applications
        $passReasons = ['Personal visit', 'Family event', 'Medical appointment', 'Official business'];
        $passCount = 0;

        if ($staffOfficerUser && $dcAdminUser) {
            foreach ($apapaOfficers->skip(10)->take(20) as $index => $officer) {
                $passApp = PassApplication::create([
                    'officer_id' => $officer->id,
                    'start_date' => now()->addDays(rand(5, 10)),
                    'end_date' => now()->addDays(rand(11, 15)),
                    'number_of_days' => rand(2, 5),
                    'reason' => $passReasons[array_rand($passReasons)],
                    'status' => 'PENDING',
                    'submitted_at' => now()->subDays(rand(1, 5)),
                ]);
                $passCount++;

                if ($index < 15) {
                    $passApproval = PassApproval::create([
                        'pass_application_id' => $passApp->id,
                        'staff_officer_id' => $staffOfficerUser->id,
                        'dc_admin_id' => $dcAdminUser->id,
                        'area_controller_id' => null,
                        'approval_status' => 'APPROVED',
                        'minuted_at' => now()->subDays(rand(1, 3)),
                        'approved_at' => now()->subDays(rand(0, 2)),
                    ]);
                    $passApp->update(['status' => 'APPROVED']);
                }
            }
        }
        $this->command->info("   ✓ Created {$passCount} Pass Applications with approvals");

        // 4. Create Quarters and Allocations
        if ($apapaCommand && $buildingUnitUser) {
            for ($i = 1; $i <= 20; $i++) {
                $quarter = Quarter::create([
                    'command_id' => $apapaCommand->id,
                    'quarter_number' => 'Q' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'quarter_type' => ['Type A', 'Type B', 'Type C'][array_rand(['Type A', 'Type B', 'Type C'])],
                    'is_occupied' => false,
                    'is_active' => true,
                ]);
            }

            $quarters = Quarter::where('command_id', $apapaCommand->id)->where('is_occupied', false)->get();
            $allocatedCount = 0;
            foreach ($apapaOfficers->take(15) as $officer) {
                if ($quarters->isNotEmpty()) {
                    $quarter = $quarters->random();
                    OfficerQuarter::create([
                        'officer_id' => $officer->id,
                        'quarter_id' => $quarter->id,
                        'allocated_date' => now()->subDays(rand(30, 365)),
                        'is_current' => true,
                        'allocated_by' => $buildingUnitUser->id,
                    ]);
                    $quarter->update(['is_occupied' => true]);
                    $quarters = $quarters->reject(function($q) use ($quarter) {
                        return $q->id === $quarter->id;
                    });
                    $allocatedCount++;
                }
            }
            $this->command->info("   ✓ Created Quarters and allocated {$allocatedCount} to officers");
        }

        // 5. Create Duty Rosters with OIC and 2IC
        if ($apapaCommand && $staffOfficerUser) {
            // Get an officer for approval (Area Controller should be an officer)
            $areaControllerOfficer = null;
            if ($areaControllerUser) {
                $areaControllerOfficer = Officer::where('user_id', $areaControllerUser->id)->first();
            }
            // If no area controller officer, use first officer from APAPA
            if (!$areaControllerOfficer) {
                $areaControllerOfficer = collect($officers)->filter(function($o) use ($apapaCommand) {
                    return $o->present_station == $apapaCommand->id;
                })->first();
            }
            
            // Get APAPA officers directly from database
            $commandOfficers = Officer::where('present_station', $apapaCommand->id)->take(10)->get();
            
            // Select OIC and 2IC (must be different officers)
            $oicOfficer = $commandOfficers->count() > 0 ? $commandOfficers->first() : null;
            $secondInCommandOfficer = $commandOfficers->count() > 1 ? $commandOfficers->skip(1)->first() : null;
            
            $rosterData = [
                'command_id' => $apapaCommand->id,
                'roster_period_start' => now()->startOfMonth(),
                'roster_period_end' => now()->endOfMonth(),
                'prepared_by' => $staffOfficerUser->id,
                'approved_by' => $areaControllerOfficer->id ?? null,
                'status' => 'APPROVED',
            ];
            
            if ($oicOfficer) {
                $rosterData['oic_officer_id'] = $oicOfficer->id;
            }
            if ($secondInCommandOfficer) {
                $rosterData['second_in_command_officer_id'] = $secondInCommandOfficer->id;
            }
            
            $roster = DutyRoster::create($rosterData);

            // Create assignments for all officers (excluding OIC and 2IC from regular assignments)
            foreach ($commandOfficers as $index => $officer) {
                // Skip OIC and 2IC from regular assignments as they're already assigned
                if ($oicOfficer && $secondInCommandOfficer && 
                    $officer->id !== $oicOfficer->id && $officer->id !== $secondInCommandOfficer->id) {
                    RosterAssignment::create([
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'duty_date' => now()->addDays(rand(1, 28)),
                        'shift' => ['Morning', 'Afternoon', 'Night'][array_rand(['Morning', 'Afternoon', 'Night'])],
                    ]);
                } elseif (!$oicOfficer || !$secondInCommandOfficer) {
                    // If OIC/2IC not set, create assignments for all
                    RosterAssignment::create([
                        'roster_id' => $roster->id,
                        'officer_id' => $officer->id,
                        'duty_date' => now()->addDays(rand(1, 28)),
                        'shift' => ['Morning', 'Afternoon', 'Night'][array_rand(['Morning', 'Afternoon', 'Night'])],
                    ]);
                }
            }
            $this->command->info("   ✓ Created Duty Rosters with OIC, 2IC, and assignments");
        }

        // 6. Create Officer Courses
        $courses = [
            ['name' => 'Advanced Leadership Course', 'type' => 'Leadership'],
            ['name' => 'Customs Procedures Training', 'type' => 'Technical'],
            ['name' => 'Management Development Program', 'type' => 'Management'],
            ['name' => 'Strategic Planning Workshop', 'type' => 'Strategic'],
        ];

        $courseCount = 0;
        foreach ($apapaOfficers->take(25) as $officer) {
            $course = $courses[array_rand($courses)];
            $startDate = now()->subMonths(rand(1, 12));
            $endDate = $startDate->copy()->addDays(rand(7, 30));
            OfficerCourse::create([
                'officer_id' => $officer->id,
                'course_name' => $course['name'],
                'course_type' => $course['type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_completed' => true,
                'completion_date' => $endDate,
                'nominated_by' => $hrdUser->id,
                'notes' => 'Course completed successfully',
            ]);
            $courseCount++;
        }
        $this->command->info("   ✓ Created {$courseCount} Officer Courses");

        // 7. Create Training Results
        $trainingCount = 0;
        foreach ($apapaOfficers->take(20) as $index => $officer) {
            TrainingResult::create([
                'appointment_number' => $officer->appointment_number ?? 'APT' . str_pad($officer->id, 6, '0', STR_PAD_LEFT),
                'officer_id' => $officer->id,
                'officer_name' => $officer->initials . ' ' . $officer->surname,
                'training_score' => rand(60, 100) + (rand(0, 99) / 100),
                'status' => rand(0, 9) ? 'PASS' : 'FAIL',
                'rank' => rand(1, 20),
                'service_number' => $officer->service_number,
                'uploaded_by' => $hrdUser->id,
                'uploaded_at' => now()->subMonths(rand(1, 12)),
                'notes' => 'Training completed successfully',
            ]);
            $trainingCount++;
        }
        $this->command->info("   ✓ Created {$trainingCount} Training Results");

        // 8. Create APER Timeline
        $aperTimeline = APERTimeline::firstOrCreate(
            ['year' => 2025],
            [
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'is_active' => true,
                'created_by' => $hrdUser->id,
            ]
        );
        $this->command->info("   ✓ Created APER Timeline");

        // 9. Create Manning Requests (Staff Officer creates, Area Controller approves)
        if ($apapaCommand && $staffOfficerUser) {
            $areaControllerOfficer = null;
            if ($areaControllerUser) {
                $areaControllerOfficer = Officer::where('user_id', $areaControllerUser->id)->first();
            }
            if (!$areaControllerOfficer) {
                $areaControllerOfficer = collect($officers)->filter(function($o) use ($apapaCommand) {
                    return $o->present_station == $apapaCommand->id;
                })->first();
            }

            $ranks = ['DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I'];
            $sexOptions = ['M', 'F', 'ANY'];
            $qualifications = ['B.Sc', 'M.Sc', 'LLB'];

            $manningRequest = ManningRequest::create([
                'command_id' => $apapaCommand->id,
                'requested_by' => $staffOfficerUser->id,
                'status' => 'APPROVED',
                'notes' => 'Manning level request for ' . $apapaCommand->name,
                'approved_by' => $areaControllerOfficer->id ?? null,
                'submitted_at' => now()->subDays(rand(5, 15)),
            ]);

            for ($i = 0; $i < 3; $i++) {
                ManningRequestItem::create([
                    'manning_request_id' => $manningRequest->id,
                    'rank' => $ranks[array_rand($ranks)],
                    'quantity_needed' => rand(2, 5),
                    'sex_requirement' => $sexOptions[array_rand($sexOptions)],
                    'qualification_requirement' => ($i % 2 == 0) ? $qualifications[array_rand($qualifications)] : null,
                ]);
            }
            $this->command->info("   ✓ Created Manning Requests");
        }

        // 10. Create Staff Orders (HRD creates)
        if ($apapaCommand && count($officers) > 0) {
            $officerForOrder = collect($officers)->filter(function($o) use ($apapaCommand) {
                return $o->present_station == $apapaCommand->id;
            })->first();
            
            if ($officerForOrder) {
                $otherCommand = $commands->where('id', '!=', $apapaCommand->id)->first();
                if ($otherCommand) {
                    StaffOrder::create([
                        'order_number' => 'SO-' . date('Y') . '-' . date('md') . '-001',
                        'officer_id' => $officerForOrder->id,
                        'from_command_id' => $apapaCommand->id,
                        'to_command_id' => $otherCommand->id,
                        'effective_date' => now()->addDays(30),
                        'order_type' => 'POSTING',
                        'status' => 'PUBLISHED',
                        'created_by' => $hrdUser->id,
                    ]);
                }
            }
            $this->command->info("   ✓ Created Staff Orders");
        }

        // 11. Create Movement Orders (HRD creates)
        $movementOrder = MovementOrder::create([
            'order_number' => 'MO-' . date('Y') . '-' . date('md') . '-001',
            'criteria_months_at_station' => 24,
            'created_by' => $hrdUser->id,
            'status' => 'PUBLISHED',
        ]);
        $this->command->info("   ✓ Created Movement Orders");

        // 12. Create Queries (Staff Officer issues, Officer responds, Staff Officer accepts/rejects)
        if ($apapaCommand && $staffOfficerUser) {
            $queryReasons = [
                'Late reporting to duty',
                'Absence without leave',
                'Insubordination',
                'Poor performance',
                'Violation of dress code',
                'Unauthorized absence',
            ];
            $queryCount = 0;
            $acceptedCount = 0;
            $rejectedCount = 0;

            foreach ($apapaOfficers->take(15) as $index => $officer) {
                $query = Query::create([
                    'officer_id' => $officer->id,
                    'issued_by_user_id' => $staffOfficerUser->id,
                    'reason' => $queryReasons[array_rand($queryReasons)],
                    'status' => 'PENDING_RESPONSE',
                    'issued_at' => now()->subDays(rand(5, 20)),
                    'response_deadline' => now()->addDays(rand(1, 7)),
                ]);
                $queryCount++;

                // Some officers have responded
                if ($index < 12) {
                    $query->update([
                        'response' => 'I acknowledge the query and will ensure compliance going forward.',
                        'status' => 'PENDING_REVIEW',
                        'responded_at' => now()->subDays(rand(1, 5)),
                    ]);

                    // Staff Officer reviews and makes decision
                    if ($index < 8) {
                        // Accept query (becomes part of disciplinary record)
                        $query->update([
                            'status' => 'ACCEPTED',
                            'reviewed_at' => now()->subDays(rand(0, 2)),
                        ]);
                        $acceptedCount++;
                    } else {
                        // Reject query (doesn't go into record)
                        $query->update([
                            'status' => 'REJECTED',
                            'reviewed_at' => now()->subDays(rand(0, 2)),
                        ]);
                        $rejectedCount++;
                    }
                }
            }
            $this->command->info("   ✓ Created {$queryCount} Queries ({$acceptedCount} accepted, {$rejectedCount} rejected)");
        }

        // 13. Create APER Forms (Reporting Officer creates, Counter Signing Officer countersigns, Officer accepts/rejects)
        if ($apapaCommand && $aperTimeline) {
            $aperCount = 0;
            $acceptedCount = 0;
            $rejectedCount = 0;

            // Get officers who can be Reporting Officers (OIC/2IC from duty roster)
            $reportingOfficers = collect($officers)->filter(function($o) use ($apapaCommand) {
                return $o->present_station == $apapaCommand->id && $o->user_id;
            })->take(5);

            foreach ($apapaOfficers->take(10) as $index => $officer) {
                $reportingOfficer = $reportingOfficers->random();
                $counterSigningOfficer = $reportingOfficers->where('id', '!=', $reportingOfficer->id)->first();

                if ($counterSigningOfficer && $reportingOfficer->user_id && $counterSigningOfficer->user_id) {
                    // Fill comprehensive APER form data
                    $aperForm = APERForm::create([
                        'officer_id' => $officer->id,
                        'timeline_id' => $aperTimeline->id,
                        'year' => 2025,
                        'status' => 'REPORTING_OFFICER',
                        'reporting_officer_id' => $reportingOfficer->user_id,
                        'countersigning_officer_id' => $counterSigningOfficer->user_id,
                        // Part 1: Personal Information
                        'service_number' => $officer->service_number,
                        'title' => $officer->sex === 'M' ? 'Mr' : 'Mrs',
                        'surname' => $officer->surname,
                        'forenames' => $officer->initials,
                        'department_area' => $officer->unit ?? 'Operations',
                        'cadre' => $officer->discipline ?? 'General',
                        'unit' => $officer->unit ?? 'Unit 1',
                        'zone' => $officer->geopolitical_zone ?? 'South-West',
                        'date_of_first_appointment' => $officer->date_of_first_appointment,
                        'date_of_present_appointment' => $officer->date_of_present_appointment,
                        'rank' => $officer->substantive_rank,
                        'hapass' => 'N/A',
                        'date_of_birth' => $officer->date_of_birth,
                        'state_of_origin' => $officer->state_of_origin,
                        'qualifications' => json_encode([
                            $officer->entry_qualification,
                            $officer->additional_qualification
                        ]),
                        // Part 2: Performance Details
                        'sick_leave_records' => json_encode(['No sick leave taken']),
                        'maternity_leave_records' => json_encode([]),
                        'annual_casual_leave_records' => json_encode(['Annual leave: 14 days']),
                        'division_targets' => json_encode(['Target 1: Complete assigned tasks', 'Target 2: Maintain high standards']),
                        'individual_targets' => json_encode(['Individual target 1', 'Individual target 2']),
                        'project_cost' => 'N/A',
                        'completion_time' => 'On time',
                        'quantity_conformity' => 'Excellent',
                        'quality_conformity' => 'Very Good',
                        'main_duties' => '1. Perform assigned customs duties and responsibilities
2. Ensure compliance with customs regulations
3. Maintain accurate records and documentation
4. Coordinate with team members on operational matters
5. Report any irregularities or issues to supervisors',
                        'joint_discussion' => 'YES',
                        'properly_equipped' => 'YES',
                        'equipment_difficulties' => 'None',
                        'difficulties_encountered' => 'No significant difficulties encountered',
                        'supervisor_assistance_methods' => 'Regular guidance and support provided',
                        'periodic_review' => 'Monthly reviews conducted',
                        'performance_measure_up' => 'YES',
                        'solution_admonition' => 'N/A',
                        'final_evaluation' => 'Officer has performed satisfactorily in all assigned duties',
                        'adhoc_duties' => 'Assigned various adhoc duties as required',
                        'adhoc_affected_duties' => 'NO',
                        'schedule_duty_from' => now()->subMonths(6),
                        'schedule_duty_to' => now(),
                        'served_under_supervisor' => 'Yes',
                        // Part 3: Assessment Grades (using standard grades A-F)
                        'targets_agreed' => 'YES',
                        'other_comments' => 'Officer demonstrates commitment and professionalism',
                        'targets_agreement_details' => 'All targets were agreed upon at the beginning of the period',
                        'duties_agreed' => 'YES',
                        'duties_agreement_details' => 'Duties were clearly defined and agreed',
                        'job_understanding_grade' => 'A',
                        'job_understanding_comment' => 'Shows excellent understanding of job requirements',
                        'knowledge_application_grade' => 'A',
                        'knowledge_application_comment' => 'Applies knowledge effectively in daily operations',
                        'accomplishment_grade' => 'B',
                        'accomplishment_comment' => 'Accomplishes assigned tasks satisfactorily',
                        'judgement_grade' => 'A',
                        'judgement_comment' => 'Demonstrates sound judgment in decision making',
                        'work_speed_accuracy_grade' => 'B',
                        'work_speed_accuracy_comment' => 'Maintains good balance between speed and accuracy',
                        'written_expression_grade' => 'B',
                        'written_expression_comment' => 'Clear and concise written communication',
                        'oral_expression_grade' => 'A',
                        'oral_expression_comment' => 'Effective verbal communication skills',
                        'staff_relations_grade' => 'A',
                        'staff_relations_comment' => 'Maintains excellent working relationships',
                        'public_relations_grade' => 'A',
                        'public_relations_comment' => 'Professional interaction with the public',
                        'staff_management_grade' => 'B',
                        'staff_management_comment' => 'Effective in managing assigned staff',
                        'quality_of_work_grade' => 'A',
                        'quality_of_work_comment' => 'Consistently produces high quality work',
                        'productivity_grade' => 'B',
                        'productivity_comment' => 'Maintains good productivity levels',
                        'effective_use_of_data_grade' => 'B',
                        'effective_use_of_data_comment' => 'Uses data effectively in decision making',
                        'initiative_grade' => 'A',
                        'initiative_comment' => 'Shows initiative in problem solving',
                        'dependability_grade' => 'A',
                        'dependability_comment' => 'Highly dependable and reliable',
                        'loyalty_grade' => 'A',
                        'loyalty_comment' => 'Demonstrates strong loyalty to the service',
                        'honesty_grade' => 'A',
                        'honesty_comment' => 'Maintains high standards of honesty and integrity',
                        'reliability_under_pressure_grade' => 'A',
                        'reliability_under_pressure_comment' => 'Performs well under pressure',
                        'sense_of_responsibility_grade' => 'A',
                        'sense_of_responsibility_comment' => 'Shows strong sense of responsibility',
                        'appearance_grade' => 'A',
                        'appearance_comment' => 'Maintains professional appearance',
                        'punctuality_grade' => 'A',
                        'punctuality_comment' => 'Always punctual',
                        'attendance_grade' => 'A',
                        'attendance_comment' => 'Excellent attendance record',
                        'drive_determination_grade' => 'A',
                        'drive_determination_comment' => 'Shows strong drive and determination',
                        'resource_utilization_grade' => 'B',
                        'resource_utilization_comment' => 'Utilizes resources effectively',
                        'disciplinary_action' => 'NO',
                        'disciplinary_action_details' => 'No disciplinary actions',
                        'special_commendation' => 'NO',
                        'special_commendation_details' => 'N/A',
                        'encourage_standards_grade' => 'A',
                        'encourage_standards_comment' => 'Encourages high standards among colleagues',
                        'train_subordinates_grade' => 'B',
                        'train_subordinates_comment' => 'Provides training to subordinates',
                        'good_example_grade' => 'A',
                        'good_example_comment' => 'Sets a good example for others',
                        'suggestions_improvements_grade' => 'B',
                        'suggestions_improvements_comment' => 'Provides constructive suggestions',
                        'training_courses' => json_encode(['Leadership Course', 'Customs Procedures']),
                        'training_enhanced_performance' => 'Yes, training has enhanced performance',
                        'satisfactory_jobs' => 'All assigned jobs completed satisfactorily',
                        'success_failure_causes' => 'Success due to dedication and hard work',
                        'training_needs' => 'Advanced management training recommended',
                        'effective_use_capabilities' => 'YES',
                        'better_use_abilities' => 'Could benefit from additional responsibilities',
                        'job_satisfaction' => 'YES',
                        'job_satisfaction_causes' => 'Finds work challenging and rewarding',
                        'overall_assessment' => 'A',
                        'training_needs_assessment' => 'Recommend advanced training in management',
                        'general_remarks' => 'Officer is an asset to the organization',
                        'suggest_different_job' => 'NO',
                        'different_job_details' => 'N/A',
                        'suggest_transfer' => 'NO',
                        'transfer_details' => 'N/A',
                        'promotability' => 'A',
                        'officer_comments' => 'I acknowledge the assessment',
                        'reporting_officer_declaration' => 'I certify that the above assessment is accurate',
                        'countersigning_officer_declaration' => 'I have reviewed and countersigned this assessment',
                        'submitted_at' => now()->subDays(rand(1, 10)),
                    ]);
                    $aperCount++;

                    // Reporting Officer completes
                    if ($index < 8) {
                        $aperForm->update([
                            'status' => 'COUNTERSIGNING_OFFICER',
                            'reporting_officer_completed_at' => now()->subDays(rand(1, 5)),
                            'reporting_officer_signed_at' => now()->subDays(rand(1, 5)),
                            'reporting_officer_user_id' => $reportingOfficer->user_id,
                        ]);

                        // Counter Signing Officer completes
                        if ($index < 6) {
                            $aperForm->update([
                                'status' => 'OFFICER_REVIEW',
                                'countersigning_officer_completed_at' => now()->subDays(rand(1, 3)),
                                'countersigning_officer_signed_at' => now()->subDays(rand(1, 3)),
                                'countersigning_officer_user_id' => $counterSigningOfficer->user_id,
                                'officer_reviewed_at' => now()->subDays(rand(0, 2)),
                            ]);

                            // Officer accepts or rejects
                            if ($index < 4) {
                                // Officer accepts
                                $aperForm->update([
                                    'status' => 'ACCEPTED',
                                    'accepted_at' => now()->subDays(rand(0, 1)),
                                    'officer_signed_at' => now()->subDays(rand(0, 1)),
                                ]);
                                $acceptedCount++;
                            } else {
                                // Officer rejects
                                $aperForm->update([
                                    'is_rejected' => true,
                                    'rejection_reason' => 'I disagree with the assessment provided.',
                                    'rejected_by_role' => 'OFFICER',
                                    'rejected_at' => now()->subDays(rand(0, 1)),
                                ]);
                                $rejectedCount++;
                            }
                        }
                    }
                }
            }
            $this->command->info("   ✓ Created {$aperCount} APER Forms ({$acceptedCount} accepted, {$rejectedCount} rejected)");
        }

        // 14. Create Release Letters (Staff Officer creates)
        if ($apapaCommand && $staffOfficerUser) {
            $releaseCount = 0;
            foreach ($apapaOfficers->take(5) as $officer) {
                ReleaseLetter::create([
                    'officer_id' => $officer->id,
                    'command_id' => $apapaCommand->id,
                    'letter_number' => 'RL-' . date('Y') . '-' . str_pad($releaseCount + 1, 4, '0', STR_PAD_LEFT),
                    'release_date' => now()->addDays(rand(10, 30)),
                    'reason' => 'End of service period',
                    'prepared_by' => $staffOfficerUser->id,
                ]);
                $releaseCount++;
            }
            $this->command->info("   ✓ Created {$releaseCount} Release Letters");
        }

        // 15. Create Officer Documents (Staff Officer documents officers)
        if ($apapaCommand && $staffOfficerUser) {
            $documentTypes = ['Appointment Letter', 'Promotion Letter', 'Posting Order', 'Training Certificate', 'Qualification Certificate'];
            $documentCount = 0;
            foreach ($apapaOfficers->take(20) as $officer) {
                OfficerDocument::create([
                    'officer_id' => $officer->id,
                    'document_type' => $documentTypes[array_rand($documentTypes)],
                    'file_name' => 'document_' . $officer->service_number . '.pdf',
                    'file_path' => 'documents/' . $officer->service_number . '/document.pdf',
                    'file_size' => rand(100000, 5000000),
                    'mime_type' => 'application/pdf',
                    'uploaded_by' => $staffOfficerUser->id,
                ]);
                $documentCount++;
            }
            $this->command->info("   ✓ Created {$documentCount} Officer Documents");
        }

        // 16. Create Investigation Records (Investigation Unit)
        $investigationUser = $roleUsers['INVESTIGATION_UNIT'] ?? null;
        if ($investigationUser) {
            $investigationStatuses = ['INVITED', 'ONGOING_INVESTIGATION', 'INTERDICTED', 'SUSPENDED'];
            $invitationMessages = [
                'You are hereby invited for investigation regarding alleged misconduct.',
                'Investigation invitation for violation of service rules.',
                'You are required to appear for investigation.',
            ];
            $investigationCount = 0;
            
            foreach ($apapaOfficers->take(5) as $index => $officer) {
                $status = $investigationStatuses[$index % count($investigationStatuses)];
                $investigation = Investigation::create([
                    'officer_id' => $officer->id,
                    'investigation_officer_id' => $investigationUser->id,
                    'invitation_message' => $invitationMessages[array_rand($invitationMessages)],
                    'status' => $status,
                    'invited_at' => now()->subDays(rand(10, 30)),
                    'status_changed_at' => now()->subDays(rand(1, 10)),
                ]);
                $investigationCount++;

                // Update officer status based on investigation
                if ($status === 'INTERDICTED') {
                    $officer->update(['interdicted' => true]);
                } elseif ($status === 'SUSPENDED') {
                    $officer->update(['suspended' => true]);
                } elseif ($status === 'ONGOING_INVESTIGATION') {
                    $officer->update(['ongoing_investigation' => true]);
                }
            }
            $this->command->info("   ✓ Created {$investigationCount} Investigation Records");
        }

        // 17. Create Account Change Requests (Officer submits, Accounts verifies)
        $accountsUser = $roleUsers['ACCOUNTS'] ?? null;
        if ($accountsUser) {
            $accountChangeCount = 0;
            $approvedCount = 0;
            
            foreach ($apapaOfficers->take(10) as $index => $officer) {
                $changeType = ($index % 2 == 0) ? 'account_number' : 'rsa_pin';
                $request = AccountChangeRequest::create([
                    'officer_id' => $officer->id,
                    'change_type' => $changeType,
                    'new_account_number' => $changeType === 'account_number' ? str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT) : null,
                    'new_rsa_pin' => $changeType === 'rsa_pin' ? 'PEN' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT) : null,
                    'current_account_number' => $officer->bank_account_number,
                    'current_rsa_pin' => $officer->rsa_number,
                    'current_bank_name' => $officer->bank_name,
                    'status' => 'PENDING',
                    'reason' => 'Bank account change request',
                ]);
                $accountChangeCount++;

                // Some requests are approved
                if ($index < 7) {
                    $request->update([
                        'status' => 'APPROVED',
                        'verified_by' => $accountsUser->id,
                        'verified_at' => now()->subDays(rand(0, 3)),
                    ]);
                    $approvedCount++;
                }
            }
            $this->command->info("   ✓ Created {$accountChangeCount} Account Change Requests ({$approvedCount} approved)");
        }

        // 18. Create Next of Kin Change Requests (Officer submits, Welfare verifies)
        $welfareUser = $roleUsers['WELFARE'] ?? null;
        if ($welfareUser) {
            $nokChangeCount = 0;
            $approvedCount = 0;
            
            foreach ($apapaOfficers->take(8) as $index => $officer) {
                $nok = NextOfKin::where('officer_id', $officer->id)->first();
                if ($nok) {
                    $request = NextOfKinChangeRequest::create([
                        'officer_id' => $officer->id,
                        'action_type' => 'edit',
                        'next_of_kin_id' => $nok->id,
                        'name' => $nok->name . ' Updated',
                        'relationship' => $nok->relationship,
                        'phone_number' => '080' . rand(10000000, 99999999),
                        'address' => $nok->address . ' Updated',
                        'email' => 'nok' . $officer->id . '@example.com',
                        'is_primary' => true,
                        'status' => 'PENDING',
                    ]);
                    $nokChangeCount++;

                    // Some requests are approved
                    if ($index < 5) {
                        $request->update([
                            'status' => 'APPROVED',
                            'verified_by' => $welfareUser->id,
                            'verified_at' => now()->subDays(rand(0, 3)),
                        ]);
                        $approvedCount++;
                    }
                }
            }
            $this->command->info("   ✓ Created {$nokChangeCount} Next of Kin Change Requests ({$approvedCount} approved)");
        }

        $this->command->info("   ✓ All app functions are now active!");
    }

    private function getSalaryGradeForRank($rank): int
    {
        $rankGrades = [
            'CGC' => 18, 'DCG' => 17, 'ACG' => 16, 'CC' => 15, 'DC' => 14, 'AC' => 13,
            'CSC' => 12, 'SC' => 11, 'DSC' => 10, 'ASC I' => 9, 'ASC II' => 8,
            'IC' => 7, 'AIC' => 6, 'CA I' => 5, 'CA II' => 4, 'CA III' => 3,
        ];

        return $rankGrades[$rank] ?? 5;
    }
}
