<?php

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Command;
use App\Models\Zone;
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
use App\Models\PassApplication;
use App\Models\PassApproval;
use App\Models\Quarter;
use App\Models\OfficerQuarter;
use App\Models\Role;
use App\Models\RosterAssignment;
use App\Models\StaffOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Roles, Zones and Commands exist (always ensure they're up-to-date)
        // Use ZoneAndCommandSeeder to ensure zones/commands are correct
        if (Command::count() == 0 || Zone::count() == 0) {
            $this->call(ZoneAndCommandSeeder::class);
        }
        $commands = Command::all();

        // 2. Create Users for each Role
        $roles = Role::all();
        $roleUsers = [];
        
        // Get or create a system admin user for created_by (first role user becomes the system admin)
        $systemAdmin = null;

        foreach ($roles as $role) {
            $email = strtolower(str_replace(' ', '.', $role->name)) . '@ncs.gov.ng';

            // Special case for Staff Officer to match login instructions if needed, 
            // but let's stick to a pattern: hrd@ncs.gov.ng, staff.officer@ncs.gov.ng, etc.
            if ($role->code === 'STAFF_OFFICER')
                $email = 'staff@ncs.gov.ng';
            if ($role->code === 'HRD')
                $email = 'hrd@ncs.gov.ng';

            // Use first user as system admin for created_by field
            if (!$systemAdmin && $role->code === 'HRD') {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'created_by' => null, // First user has no creator
                    ]
                );
                $systemAdmin = $user;
            } else {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                        'email_verified_at' => now(),
                        'created_by' => $systemAdmin->id ?? null,
                    ]
                );
            }

            if (!$user->roles->contains($role->id)) {
                $user->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
            }

            $roleUsers[$role->code] = $user;
            $this->command->info("Created/Found User: {$email} with Role: {$role->name}");
        }

        // 3. Create Users for all roles with proper command assignments
        $this->createRoleUsersWithCommands($commands, $roleUsers);
        
        // 4. Create Chat Rooms for all commands (before officers are created)
        $this->createChatRooms($commands);
        
        // 5. Create ~50 Officers
        $ranks = ['ASC II', 'ASC I', 'DSC', 'SC', 'CSC', 'AC', 'DC', 'CC', 'ACG', 'DCG'];
        $surnames = ['Adebayo', 'Okafor', 'Ibrahim', 'Okoro', 'Musa', 'Adeyemi', 'Bello', 'Chukwu', 'Yusuf', 'Obi', 'Ali', 'Adekunle', 'Mohammed', 'Nwankwo', 'Sani'];
        $states = ['Lagos', 'Abuja', 'Kano', 'Rivers', 'Ogun', 'Kaduna', 'Delta', 'Enugu', 'Plateau', 'Oyo'];
        $lgas = ['Ikeja', 'Mushin', 'Surulere', 'Victoria Island', 'Garki', 'Wuse', 'Kaduna North', 'Port Harcourt', 'Abeokuta', 'Ibadan'];
        $disciplines = ['Accounting', 'Law', 'Computer Science', 'Economics'];
        $banks = ['Zenith Bank', 'GTBank', 'First Bank', 'UBA'];
        $pfas = ['Stanbic IBTC Pension', 'ARM Pension', 'Premium Pension'];
        $zones = ['North-Central', 'North-East', 'North-West', 'South-East', 'South-South', 'South-West'];
        $maritalStatuses = ['Single', 'Married'];
        $sexes = ['M', 'F'];
        $letters = range('A', 'Z');
        $relationships = ['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Son', 'Daughter', 'Uncle', 'Aunt'];
        $officers = [];
        $usedServiceNumbers = [];

        for ($i = 0; $i < 50; $i++) {
            $command = $commands->random();
            $rank = $ranks[array_rand($ranks)];
            
            // Generate unique service number (format: NCS + 5 digits)
            // Check both the array and the database to ensure uniqueness
            do {
                $serviceNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                $fullServiceNumber = 'NCS' . $serviceNumber;
            } while (in_array($serviceNumber, $usedServiceNumbers) || Officer::where('service_number', $fullServiceNumber)->exists());
            $usedServiceNumbers[] = $serviceNumber;

            // Get HRD user for created_by field
            $hrdUser = $roleUsers['HRD'] ?? User::where('email', 'hrd@ncs.gov.ng')->first();

            // Create User for Officer
            $user = User::create([
                'email' => "officer{$serviceNumber}@ncs.gov.ng",
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
                'created_by' => $hrdUser->id ?? null,
            ]);

            // Attach Officer Role
            $officerRole = Role::where('code', 'OFFICER')->first();
            $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

            // Generate data with or without Faker
            $initials = strtoupper($letters[array_rand($letters)] . '.' . $letters[array_rand($letters)]);
            $surname = $surnames[array_rand($surnames)];
            $sex = $sexes[array_rand($sexes)];
            $dateOfBirth = Carbon::now()->subYears(rand(25, 50))->subDays(rand(0, 365));
            $dateOfFirstAppointment = Carbon::now()->subYears(rand(5, 20))->subDays(rand(0, 365));
            $dateOfPresentAppointment = Carbon::now()->subYears(rand(0, 5))->subDays(rand(0, 365));
            $salaryGrade = rand(8, 16);
            $state = $states[array_rand($states)];
            $lga = $lgas[array_rand($lgas)];
            $zone = $zones[array_rand($zones)];
            $maritalStatus = $maritalStatuses[array_rand($maritalStatuses)];
            $discipline = $disciplines[array_rand($disciplines)];
            $address = $state . ' State, ' . $lga . ' LGA';
            $datePostedToStation = Carbon::now()->subYears(rand(0, 2))->subDays(rand(0, 365));
            $phoneNumber = '080' . rand(10000000, 99999999);
            $bankName = $banks[array_rand($banks)];
            $bankAccountNumber = str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $sortCode = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            $pfaName = $pfas[array_rand($pfas)];
            $rsaNumber = 'PEN' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
            $unit = 'Unit ' . rand(1, 10);
            
            // Additional qualification (optional, set for some officers)
            $additionalQualifications = ['M.Sc', 'M.B.A', 'LLM', 'Ph.D', 'PGD', null, null, null];
            $additionalQualification = $additionalQualifications[array_rand($additionalQualifications)];

            // Create Officer Profile with ALL fields
            $officer = Officer::create([
                'user_id' => $user->id,
                'service_number' => $serviceNumber, // Mutator will add NCS prefix automatically
                'initials' => $initials,
                'surname' => $surname,
                'sex' => $sex,
                'date_of_birth' => $dateOfBirth,
                'date_of_first_appointment' => $dateOfFirstAppointment,
                'date_of_present_appointment' => $dateOfPresentAppointment,
                'substantive_rank' => $rank,
                'salary_grade_level' => $salaryGrade,
                'state_of_origin' => $state,
                'lga' => $lga,
                'geopolitical_zone' => $zone,
                'marital_status' => $maritalStatus,
                'entry_qualification' => 'B.Sc',
                'discipline' => $discipline,
                'additional_qualification' => $additionalQualification,
                'residential_address' => $address,
                'permanent_home_address' => $address,
                'present_station' => $command->id,
                'date_posted_to_station' => $datePostedToStation,
                'phone_number' => $phoneNumber,
                'email' => $user->email,
                'bank_name' => $bankName,
                'bank_account_number' => $bankAccountNumber,
                'sort_code' => $sortCode,
                'pfa_name' => $pfaName,
                'rsa_number' => $rsaNumber,
                'unit' => $unit,
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
                'quartered' => false,
                'is_deceased' => false,
                'is_active' => true,
                'profile_picture_url' => 'officers/default.png', // Set placeholder to mark onboarding as complete
                'onboarding_status' => 'completed',
                'onboarding_completed_at' => now()->subDays(rand(1, 30)), // Completed 1-30 days ago
                'verification_status' => 'verified',
                'verified_at' => now()->subDays(rand(1, 30)),
                'created_by' => $hrdUser->id ?? null,
            ]);

            $officers[] = $officer;
        }

        $this->command->info("Created 50 Officers");

        // 5a. Create Next of Kin for all officers (required for emolument)
        foreach ($officers as $officer) {
            NextOfKin::create([
                'officer_id' => $officer->id,
                'name' => $surnames[array_rand($surnames)] . ' ' . $officer->surname,
                'relationship' => $relationships[array_rand($relationships)],
                'phone_number' => '080' . rand(10000000, 99999999),
                'address' => $officer->residential_address,
                'is_primary' => true,
            ]);
        }
        $this->command->info("Created Next of Kin for all officers");

        // 5b. Add officers to their command chat rooms
        $this->addOfficersToChatRooms($officers);

        // Link the test officer user to the first officer profile (if not already linked)
        $testOfficerUser = $roleUsers['OFFICER'];
        $firstOfficer = $officers[0];
        
        // Check if user is already linked to another officer
        $existingOfficer = Officer::where('user_id', $testOfficerUser->id)->first();
        if (!$existingOfficer && !$firstOfficer->user_id) {
            $firstOfficer->update(['user_id' => $testOfficerUser->id]);
            $this->command->info("Linked officer@ncs.gov.ng to Officer: {$firstOfficer->service_number}");
        } else {
            $this->command->info("Officer user already linked or first officer already has a user");
        }

        // 6. Create Emolument Timeline
        $timeline = EmolumentTimeline::firstOrCreate(
            ['year' => 2025],
            [
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'is_active' => true,
                'created_by' => $roleUsers['HRD']->id,
            ]
        );

        // 7. Create Emoluments with proper workflow
        $emoluments = [];
        $assessorRole = Role::where('code', 'ASSESSOR')->first();
        $validatorRole = Role::where('code', 'VALIDATOR')->first();
        $areaControllerRole = Role::where('code', 'AREA_CONTROLLER')->first();
        $dcAdminRole = Role::where('code', 'DC_ADMIN')->first();
        $staffOfficerRole = Role::where('code', 'STAFF_OFFICER')->first();
        
        // Get role users (create if needed) - using a common command for consistency
        $testCommand = $commands->first();
        $assessorUser = $this->getOrCreateRoleUser('ASSESSOR', $testCommand, $assessorRole);
        $validatorUser = $this->getOrCreateRoleUser('VALIDATOR', $testCommand, $validatorRole);
        $areaControllerUser = $this->getOrCreateRoleUser('AREA_CONTROLLER', $testCommand, $areaControllerRole);
        $dcAdminUser = $this->getOrCreateRoleUser('DC_ADMIN', $testCommand, $dcAdminRole);
        $staffOfficerUser = $this->getOrCreateRoleUser('STAFF_OFFICER', $testCommand, $staffOfficerRole);
        
        foreach (array_slice($officers, 0, 20) as $index => $officer) {
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
            $emoluments[] = $emolument;
            
            // Create assessment for some emoluments (workflow: RAISED → ASSESSED)
            if ($index < 15) {
                $assessment = EmolumentAssessment::create([
                    'emolument_id' => $emolument->id,
                    'assessor_id' => $assessorUser->id,
                    'assessment_status' => 'APPROVED',
                    'comments' => 'Assessment completed',
                ]);
                $emolument->update(['status' => 'ASSESSED']);
                
                // Create validation for some assessed emoluments (workflow: ASSESSED → VALIDATED)
                if ($index < 10) {
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
        $this->command->info("Created Emoluments with workflow states");

        // 8. Create Leave Applications with proper workflow
        $leaveTypes = LeaveType::all();
        $leaveReasons = ['Annual leave', 'Sick leave', 'Maternity leave', 'Personal reasons', 'Family emergency'];
        
        if ($leaveTypes->isNotEmpty()) {
            foreach (array_slice($officers, 0, 15) as $index => $officer) {
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
                
                // Some leave applications are minuted by Staff Officer
                if ($index < 12) {
                    $leaveApp->update(['status' => 'MINUTED']);
                    
                    // Create leave approval record
                    $leaveApproval = LeaveApproval::create([
                        'leave_application_id' => $leaveApp->id,
                        'staff_officer_id' => $staffOfficerUser->id,
                        'dc_admin_id' => null,
                        'area_controller_id' => null,
                        'approval_status' => 'MINUTED',
                        'minuted_at' => now()->subDays(rand(1, 5)),
                    ]);
                    
                    // Some minuted applications are approved by DC Admin
                    if ($index < 8) {
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
        $this->command->info("Created Leave Applications with workflow states");

        // 9. Create Pass Applications with approvals
        $passReasons = ['Personal visit', 'Family event', 'Medical appointment', 'Official business'];
        foreach (array_slice($officers, 15, 10) as $index => $officer) {
            $passApp = PassApplication::create([
                'officer_id' => $officer->id,
                'start_date' => now()->addDays(rand(5, 10)),
                'end_date' => now()->addDays(rand(11, 15)),
                'number_of_days' => rand(2, 5), // Max 5 days per spec
                'reason' => $passReasons[array_rand($passReasons)],
                'status' => 'PENDING',
                'submitted_at' => now()->subDays(rand(1, 5)),
            ]);
            
            // Some pass applications are minuted and approved
            if ($index < 7) {
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
        $this->command->info("Created Pass Applications with approvals");

        // 10. Create Manning Requests
        $this->createManningRequests($commands, $staffOfficerUser, $areaControllerUser, $roleUsers['HRD'], $officers);

        // 11. Create Movement Orders
        $this->createMovementOrders($commands, $officers, $roleUsers['HRD']);

        // 12. Create Quarters and Quarter Allocations
        $this->createQuarters($commands, $officers);

        // 13. Create Duty Rosters
        $this->createDutyRosters($commands, $staffOfficerUser, $areaControllerUser, $officers);

        // 14. Create Officer Postings
        $this->createOfficerPostings($officers, $commands);

        // 15. Create Officer Courses
        $this->createOfficerCourses($officers, $roleUsers['HRD']);

        // 16. Create Staff Orders
        $officerForOrder = $officers[0];
        $toCommand = $commands->where('id', '!=', $officerForOrder->present_station)->random();

        StaffOrder::create([
            'order_number' => 'SO/2025/001',
            'officer_id' => $officerForOrder->id,
            'from_command_id' => $officerForOrder->present_station,
            'to_command_id' => $toCommand->id,
            'effective_date' => now()->addDays(30),
            'order_type' => 'STAFF_ORDER',
            'created_by' => $roleUsers['HRD']->id,
        ]);
        $this->command->info("Created Staff Orders");

        // 17. Create Deceased Officers
        foreach (array_slice($officers, 45, 2) as $officer) {
            $officer->update(['is_deceased' => true, 'deceased_date' => now()->subDays(rand(10, 100))]);
            DeceasedOfficer::create([
                'officer_id' => $officer->id,
                'reported_by' => $staffOfficerUser->id,
                'reported_at' => now()->subDays(rand(1, 90)),
                'date_of_death' => $officer->deceased_date,
            ]);
        }
        $this->command->info("Created Deceased Officers");
    }

    private function createRoleUsersWithCommands($commands, &$roleUsers)
    {
        $roles = Role::all();
        $commandLevelRoles = ['STAFF_OFFICER', 'ASSESSOR', 'VALIDATOR', 'AREA_CONTROLLER', 'DC_ADMIN', 'BUILDING_UNIT'];
        
        foreach ($roles as $role) {
            if (in_array($role->code, $commandLevelRoles)) {
                // Create users for each command for command-level roles
                foreach ($commands->take(5) as $command) {
                    $email = strtolower($role->code) . '.' . strtolower($command->code) . '@ncs.gov.ng';
                    // Get HRD user for created_by
                    $hrdUser = $roleUsers['HRD'] ?? User::where('email', 'hrd@ncs.gov.ng')->first();
                    
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'password' => Hash::make('password123'),
                            'is_active' => true,
                            'email_verified_at' => now(),
                            'created_by' => $hrdUser->id ?? null,
                        ]
                    );
                    
                    if (!$user->roles->contains($role->id)) {
                        $user->roles()->attach($role->id, [
                            'command_id' => $command->id,
                            'is_active' => true,
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }
        }
        $this->command->info("Created role users with command assignments");
    }

    private function createChatRooms($commands)
    {
        foreach ($commands as $command) {
            // Create main command chat room
            $chatRoom = ChatRoom::firstOrCreate(
                ['command_id' => $command->id, 'room_type' => 'COMMAND'],
                [
                    'name' => $command->name . ' Chat Room',
                    'description' => 'Main chat room for ' . $command->name,
                    'is_active' => true,
                ]
            );
        }
        $this->command->info("Created Chat Rooms for all commands");
    }

    private function addOfficersToChatRooms($officers)
    {
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();
        
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
        $this->command->info("Added officers to their command chat rooms");
    }

    private function getOrCreateRoleUser($roleCode, $command, $role)
    {
        $email = strtolower($roleCode) . '.' . strtolower($command->code) . '@ncs.gov.ng';
        
        // Get HRD user for created_by
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

    private function createManningRequests($commands, $staffOfficerUser, $areaControllerUser, $hrdUser, $officers = [])
    {
        $ranks = ['DC', 'AC', 'CSC', 'SC', 'DSC'];
        $sexOptions = ['M', 'F', 'ANY'];
        $qualifications = ['B.Sc', 'M.Sc', 'LLB'];
        
        // Find area controller officer (approved_by must be an officer_id, not user_id)
        // Use an officer from the newly created officers array to ensure it exists
        $areaControllerOfficer = null;
        if (!empty($officers)) {
            // First try to find an officer associated with area controller user
            if ($areaControllerUser) {
                foreach ($officers as $officer) {
                    if ($officer->user_id == $areaControllerUser->id) {
                        $areaControllerOfficer = $officer;
                        break;
                    }
                }
            }
            // If not found, use first available officer from the array
            if (!$areaControllerOfficer) {
                $areaControllerOfficer = $officers[0];
            }
        } else {
            // Fallback: try to find any officer from database
            $areaControllerOfficer = Officer::first();
        }
        
        foreach ($commands->take(5) as $command) {
            $manningRequest = ManningRequest::create([
                'command_id' => $command->id,
                'requested_by' => $staffOfficerUser->id,
                'status' => 'APPROVED',
                'notes' => 'Manning level request for ' . $command->name,
                'approved_by' => $areaControllerOfficer ? $areaControllerOfficer->id : null,
                'submitted_at' => now()->subDays(rand(5, 15)),
            ]);
            
            // Create manning request items
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
        $this->command->info("Created Manning Requests");
    }

    private function createMovementOrders($commands, $officers, $hrdUser)
    {
        $movementOrder = MovementOrder::create([
            'order_number' => 'MO/2025/001',
            'criteria_months_at_station' => 24,
            'created_by' => $hrdUser->id,
            'status' => 'PUBLISHED', // Changed from 'ACTIVE' - enum values are now: DRAFT, PUBLISHED, CANCELLED
        ]);
        
        // Create postings for some officers
        foreach (array_slice($officers, 20, 5) as $officer) {
            $toCommand = $commands->where('id', '!=', $officer->present_station)->random();
            OfficerPosting::create([
                'officer_id' => $officer->id,
                'command_id' => $toCommand->id,
                'movement_order_id' => $movementOrder->id,
                'posting_date' => now()->addDays(30),
                'is_current' => false,
            ]);
        }
        $this->command->info("Created Movement Orders");
    }

    private function createQuarters($commands, $officers)
    {
        foreach ($commands->take(10) as $command) {
            // Create 5-10 quarters per command
            for ($i = 1; $i <= rand(5, 10); $i++) {
                $quarter = Quarter::create([
                    'command_id' => $command->id,
                    'quarter_number' => 'Q' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'quarter_type' => ['Type A', 'Type B', 'Type C'][array_rand(['Type A', 'Type B', 'Type C'])],
                    'is_occupied' => false,
                    'is_active' => true,
                ]);
            }
        }
        
        // Allocate quarters to some officers
        $quarters = Quarter::where('is_occupied', false)->get();
        $buildingUnitRole = Role::where('code', 'BUILDING_UNIT')->first();
        $buildingUnitUser = $this->getOrCreateRoleUser('BUILDING_UNIT', $commands->first(), $buildingUnitRole);
        
        foreach (array_slice($officers, 0, 15) as $officer) {
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
            }
        }
        $this->command->info("Created Quarters and Quarter Allocations");
    }

    private function createDutyRosters($commands, $staffOfficerUser, $areaControllerUser, $officers = [])
    {
        // Find area controller officer (approved_by must be an officer_id, not user_id)
        $areaControllerOfficer = null;
        if (!empty($officers)) {
            // First try to find an officer associated with area controller user
            if ($areaControllerUser) {
                foreach ($officers as $officer) {
                    if ($officer->user_id == $areaControllerUser->id) {
                        $areaControllerOfficer = $officer;
                        break;
                    }
                }
            }
            // If not found, use first available officer from the array
            if (!$areaControllerOfficer) {
                $areaControllerOfficer = $officers[0];
            }
        } else {
            // Fallback: try to find any officer from database
            $areaControllerOfficer = Officer::first();
        }
        
        foreach ($commands->take(5) as $command) {
            $roster = DutyRoster::create([
                'command_id' => $command->id,
                'roster_period_start' => now()->startOfMonth(),
                'roster_period_end' => now()->endOfMonth(),
                'prepared_by' => $staffOfficerUser->id,
                'approved_by' => $areaControllerOfficer ? $areaControllerOfficer->id : null,
                'status' => 'APPROVED',
            ]);
            
            // Get officers in this command
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
        $this->command->info("Created Duty Rosters and Assignments");
    }

    private function createOfficerPostings($officers, $commands)
    {
        foreach (array_slice($officers, 0, 10) as $officer) {
            // Create posting history
            OfficerPosting::create([
                'officer_id' => $officer->id,
                'command_id' => $officer->present_station,
                'posting_date' => $officer->date_posted_to_station ?? now()->subYears(rand(1, 3)),
                'is_current' => true,
            ]);
        }
        $this->command->info("Created Officer Postings");
    }

    private function createOfficerCourses($officers, $hrdUser)
    {
        $courses = [
            ['name' => 'Advanced Leadership Course', 'type' => 'Leadership'],
            ['name' => 'Customs Procedures Training', 'type' => 'Technical'],
            ['name' => 'Management Development Program', 'type' => 'Management'],
            ['name' => 'Strategic Planning Workshop', 'type' => 'Strategic'],
        ];
        
        foreach (array_slice($officers, 0, 10) as $officer) {
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
        }
        $this->command->info("Created Officer Courses");
    }
}
