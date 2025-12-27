<?php

namespace Database\Seeders;

use App\Models\AccountChangeRequest;
use App\Models\APERForm;
use App\Models\APERTimeline;
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
     * Creates a complete system with 5 officers per rank (80 total) 
     * with data for all functionalities.
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

        // Step 3: Create 5 officers for each rank
        $this->command->info("\n📋 Step 3: Creating 80 officers (5 per rank)...");
        $officers = $this->createOfficers($commands);

        // Step 4: Create supporting data for all officers
        $this->command->info("\n📋 Step 4: Creating supporting data...");
        $this->createSupportingData($officers, $commands);

        // Step 5: Create functional data
        $this->command->info("\n📋 Step 5: Creating functional data...");
        $this->createFunctionalData($officers, $commands);

        $this->command->info("\n" . str_repeat("=", 62));
        $this->command->info("✅ Complete System Seeding Finished Successfully!");
        $this->command->info("   Created: 80 officers (5 per rank) with complete data");
        $this->command->info(str_repeat("=", 62));
    }

    private function deleteSeededData(): void
    {
        // Delete in reverse order of dependencies (children first, then parents)
        // Note: SQLite enforces foreign keys, so we must delete in correct order
        
        // Delete all records that reference officers (child records first)
        DB::table('account_change_requests')->delete();
        DB::table('aper_forms')->delete();
        DB::table('aper_timelines')->delete();
        DB::table('chat_messages')->delete();
        DB::table('chat_room_members')->delete();
        DB::table('deceased_officers')->delete();
        DB::table('emolument_validations')->delete(); // Delete before assessments (references assessments)
        DB::table('emolument_assessments')->delete();
        DB::table('emoluments')->delete();
        DB::table('emolument_timelines')->delete();
        DB::table('leave_approvals')->delete();
        DB::table('leave_applications')->delete();
        DB::table('manning_request_items')->delete();
        DB::table('manning_requests')->update(['approved_by' => null]); // Clear foreign key
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
        DB::table('release_letters')->delete();
        DB::table('retirement_alerts')->delete();
        DB::table('retirement_list_items')->delete();
        DB::table('roster_assignments')->delete();
        DB::table('duty_rosters')->update(['approved_by' => null]); // Clear foreign key
        DB::table('staff_orders')->delete();
        DB::table('training_results')->delete();
        
        // Delete parent records that don't reference officers
        DB::table('chat_rooms')->whereNotNull('command_id')->delete();
        DB::table('quarters')->delete();
        DB::table('promotion_eligibility_criteria')->delete();
        DB::table('promotion_eligibility_lists')->delete();

        // Delete officers (but keep users for system roles)
        $officerUserIds = Officer::pluck('user_id')->filter()->toArray();
        Officer::query()->delete();
        
        // Delete officer users (but keep system role users)
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
        // Ensure commands exist
        $commands = Command::all();
        if ($commands->isEmpty()) {
            $this->call(ZoneAndCommandSeeder::class);
            $commands = Command::all();
        }

        // Ensure roles exist
        if (Role::count() < 10) {
            $this->call(RoleSeeder::class);
        }

        // Ensure leave types exist
        if (LeaveType::count() < 5) {
            $this->call(LeaveTypeSeeder::class);
        }

        return $commands;
    }

    private function createOfficers($commands): array
    {
        $ranks = [
            'CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC',
            'CSC', 'SC', 'DSC', 'ASC I', 'ASC II',
            'IC', 'AIC', 'CA I', 'CA II', 'CA III',
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

        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();
        $officerRole = Role::where('code', 'OFFICER')->first();
        $officers = [];
        $usedServiceNumbers = [];
        $rankCounts = [];

        foreach ($ranks as $rank) {
            $rankCounts[$rank] = 0;
            
            for ($i = 0; $i < 5; $i++) {
                // Generate unique service number
                do {
                    $serviceNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                    $fullServiceNumber = 'NCS' . $serviceNumber;
                } while (in_array($serviceNumber, $usedServiceNumbers) || Officer::where('service_number', $fullServiceNumber)->exists());
                $usedServiceNumbers[] = $serviceNumber;

                $command = $commands->random();
                $sex = $sexes[array_rand($sexes)];
                $surname = $surnames[array_rand($surnames)];
                $firstName = $firstNames[array_rand($firstNames)];
                $initials = strtoupper($letters[array_rand($letters)] . '.' . $letters[array_rand($letters)]);

                // Calculate dates - vary years in rank for promotion eligibility testing
                $yearsInRank = rand(1, 5) + (rand(0, 100) / 100); // 1.00 to 5.99 years
                $dateOfPresentAppointment = Carbon::now()->subYears((int)$yearsInRank)
                    ->subMonths((int)(($yearsInRank - (int)$yearsInRank) * 12))
                    ->subDays(rand(0, 30));

                $dateOfFirstAppointment = $dateOfPresentAppointment->copy()->subYears(rand(3, 10));
                $dateOfBirth = Carbon::now()->subYears(rand(25, 50))->subDays(rand(0, 365));
                $datePostedToStation = $dateOfPresentAppointment->copy()->subMonths(rand(0, 6));

                // Create User
                $user = User::create([
                    'email' => "officer{$serviceNumber}@ncs.gov.ng",
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'created_by' => $hrdUser->id ?? null,
                ]);

                $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

                // Create Officer
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
                    'created_by' => $hrdUser->id ?? null,
                ]);

                $officers[] = $officer;
                $rankCounts[$rank]++;
            }
        }

        foreach ($rankCounts as $rank => $count) {
            $this->command->info("   ✓ Created {$count} officers for rank: {$rank}");
        }

        return $officers;
    }

    private function createSupportingData($officers, $commands): void
    {
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();
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
            $chatRoom = ChatRoom::firstOrCreate(
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

    private function createFunctionalData($officers, $commands): void
    {
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();
        $testCommand = $commands->first();

        // Get role users
        $assessorRole = Role::where('code', 'ASSESSOR')->first();
        $validatorRole = Role::where('code', 'VALIDATOR')->first();
        $areaControllerRole = Role::where('code', 'AREA_CONTROLLER')->first();
        $dcAdminRole = Role::where('code', 'DC_ADMIN')->first();
        $staffOfficerRole = Role::where('code', 'STAFF_OFFICER')->first();
        $buildingUnitRole = Role::where('code', 'BUILDING_UNIT')->first();

        $assessorUser = $this->getOrCreateRoleUser('ASSESSOR', $testCommand, $assessorRole);
        $validatorUser = $this->getOrCreateRoleUser('VALIDATOR', $testCommand, $validatorRole);
        $areaControllerUser = $this->getOrCreateRoleUser('AREA_CONTROLLER', $testCommand, $areaControllerRole);
        $dcAdminUser = $this->getOrCreateRoleUser('DC_ADMIN', $testCommand, $dcAdminRole);
        $staffOfficerUser = $this->getOrCreateRoleUser('STAFF_OFFICER', $testCommand, $staffOfficerRole);
        $buildingUnitUser = $this->getOrCreateRoleUser('BUILDING_UNIT', $testCommand, $buildingUnitRole);

        // 1. Create Emoluments
        $timeline = EmolumentTimeline::firstOrCreate(
            ['year' => 2025],
            [
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'is_active' => true,
                'created_by' => $hrdUser->id,
            ]
        );

        $emolumentCount = 0;
        foreach (array_slice($officers, 0, 40) as $index => $officer) {
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

            if ($index < 30) {
                $assessment = EmolumentAssessment::create([
                    'emolument_id' => $emolument->id,
                    'assessor_id' => $assessorUser->id,
                    'assessment_status' => 'APPROVED',
                    'comments' => 'Assessment completed',
                ]);
                $emolument->update(['status' => 'ASSESSED']);

                if ($index < 20) {
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

        if ($leaveTypes->isNotEmpty()) {
            foreach (array_slice($officers, 0, 30) as $index => $officer) {
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

        foreach (array_slice($officers, 10, 20) as $index => $officer) {
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
        $this->command->info("   ✓ Created {$passCount} Pass Applications with approvals");

        // 4. Create Manning Requests
        $ranks = ['DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I'];
        $sexOptions = ['M', 'F', 'ANY'];
        $qualifications = ['B.Sc', 'M.Sc', 'LLB'];

        // Get an area controller officer (not user) for approved_by
        $areaControllerOfficer = Officer::whereHas('user.roles', function($q) {
            $q->where('code', 'AREA_CONTROLLER');
        })->first();

        $manningCount = 0;
        foreach ($commands->take(5) as $command) {
            $manningRequest = ManningRequest::create([
                'command_id' => $command->id,
                'requested_by' => $staffOfficerUser->id,
                'status' => 'APPROVED',
                'notes' => 'Manning level request for ' . $command->name,
                'approved_by' => $areaControllerOfficer->id ?? null,
                'submitted_at' => now()->subDays(rand(5, 15)),
            ]);
            $manningCount++;

            for ($i = 0; $i < 3; $i++) {
                ManningRequestItem::create([
                    'manning_request_id' => $manningRequest->id,
                    'rank' => $ranks[array_rand($ranks)],
                    'quantity_needed' => rand(2, 5),
                    'sex_requirement' => $sexOptions[array_rand($sexOptions)],
                    'qualification_requirement' => ($i % 2 == 0) ? $qualifications[array_rand($qualifications)] : null,
                ]);
            }
        }
        $this->command->info("   ✓ Created {$manningCount} Manning Requests");

        // 5. Create Movement Orders
        $movementOrder = MovementOrder::create([
            'order_number' => 'MO-' . date('Y') . '-' . date('md') . '-001',
            'criteria_months_at_station' => 24,
            'created_by' => $hrdUser->id,
            'status' => 'PUBLISHED',
        ]);

        $postingCount = 0;
        foreach (array_slice($officers, 20, 10) as $officer) {
            $toCommand = $commands->where('id', '!=', $officer->present_station)->random();
            OfficerPosting::create([
                'officer_id' => $officer->id,
                'command_id' => $toCommand->id,
                'movement_order_id' => $movementOrder->id,
                'posting_date' => now()->addDays(30),
                'is_current' => false,
            ]);
            $postingCount++;
        }
        $this->command->info("   ✓ Created Movement Orders and {$postingCount} additional postings");

        // 6. Create Quarters
        $quarterCount = 0;
        foreach ($commands->take(10) as $command) {
            for ($i = 1; $i <= rand(5, 10); $i++) {
                Quarter::create([
                    'command_id' => $command->id,
                    'quarter_number' => 'Q' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'quarter_type' => ['Type A', 'Type B', 'Type C'][array_rand(['Type A', 'Type B', 'Type C'])],
                    'is_occupied' => false,
                    'is_active' => true,
                ]);
                $quarterCount++;
            }
        }

        $quarters = Quarter::where('is_occupied', false)->get();
        $allocatedCount = 0;
        foreach (array_slice($officers, 0, 20) as $officer) {
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
        $this->command->info("   ✓ Created {$quarterCount} Quarters and allocated {$allocatedCount} to officers");

        // 7. Create Duty Rosters
        // Get an area controller officer for approved_by (duty_rosters.approved_by references officers)
        $areaControllerOfficer = Officer::whereHas('user.roles', function($q) {
            $q->where('code', 'AREA_CONTROLLER');
        })->first();

        $rosterCount = 0;
        foreach ($commands->take(5) as $command) {
            $roster = DutyRoster::create([
                'command_id' => $command->id,
                'roster_period_start' => now()->startOfMonth(),
                'roster_period_end' => now()->endOfMonth(),
                'prepared_by' => $staffOfficerUser->id,
                'approved_by' => $areaControllerOfficer->id ?? null,
                'status' => 'APPROVED',
            ]);
            $rosterCount++;

            $commandOfficers = Officer::where('present_station', $command->id)->take(10)->get();
            foreach ($commandOfficers as $officer) {
                RosterAssignment::create([
                    'roster_id' => $roster->id,
                    'officer_id' => $officer->id,
                    'duty_date' => now()->addDays(rand(1, 28)),
                    'shift' => ['Morning', 'Afternoon', 'Night'][array_rand(['Morning', 'Afternoon', 'Night'])],
                ]);
            }
        }
        $this->command->info("   ✓ Created {$rosterCount} Duty Rosters with assignments");

        // 8. Create Officer Courses
        $courses = [
            ['name' => 'Advanced Leadership Course', 'type' => 'Leadership'],
            ['name' => 'Customs Procedures Training', 'type' => 'Technical'],
            ['name' => 'Management Development Program', 'type' => 'Management'],
            ['name' => 'Strategic Planning Workshop', 'type' => 'Strategic'],
        ];

        $courseCount = 0;
        foreach (array_slice($officers, 0, 25) as $officer) {
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

        // 9. Create Training Results
        $trainingCount = 0;
        foreach (array_slice($officers, 0, 20) as $index => $officer) {
            TrainingResult::create([
                'appointment_number' => $officer->appointment_number ?? 'APT' . str_pad($officer->id, 6, '0', STR_PAD_LEFT),
                'officer_id' => $officer->id,
                'officer_name' => $officer->initials . ' ' . $officer->surname,
                'training_score' => rand(60, 100) + (rand(0, 99) / 100), // 60.00 to 100.99
                'status' => rand(0, 9) ? 'PASS' : 'FAIL', // 90% pass rate
                'rank' => rand(1, 20), // Rank based on performance
                'service_number' => $officer->service_number,
                'uploaded_by' => $hrdUser->id,
                'uploaded_at' => now()->subMonths(rand(1, 12)),
                'notes' => 'Training completed successfully',
            ]);
            $trainingCount++;
        }
        $this->command->info("   ✓ Created {$trainingCount} Training Results");

        // 10. Create Staff Orders
        $officerForOrder = $officers[0];
        $toCommand = $commands->where('id', '!=', $officerForOrder->present_station)->random();

        StaffOrder::create([
            'order_number' => 'SO-' . date('Y') . '-001',
            'officer_id' => $officerForOrder->id,
            'from_command_id' => $officerForOrder->present_station,
            'to_command_id' => $toCommand->id,
            'effective_date' => now()->addDays(30),
            'order_type' => 'STAFF_ORDER',
            'created_by' => $hrdUser->id,
        ]);
        $this->command->info("   ✓ Created Staff Orders");
    }

    private function getOrCreateRoleUser($roleCode, $command, $role)
    {
        $email = strtolower($roleCode) . '.' . strtolower($command->code) . '@ncs.gov.ng';
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
                'created_by' => $hrdUser->id ?? null,
            ]
        );

        if ($role && !$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id, [
                'command_id' => $command->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]);
        }

        return $user;
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

