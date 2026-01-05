<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\DeceasedOfficer;
use App\Models\Emolument;
use App\Models\EmolumentAssessment;
use App\Models\EmolumentTimeline;
use App\Models\EmolumentValidation;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\MovementOrder;
use App\Models\Officer;
use App\Models\OfficerPosting;
use App\Models\PassApplication;
use App\Models\PromotionEligibilityList;
use App\Models\RetirementList;
use App\Models\Role;
use App\Models\StaffOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🗑️  Clearing existing data...');
        
        // Clear all data in correct order (respecting foreign keys)
        // Disable foreign key checks based on database driver
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // Delete only from tables that exist
        // NOTE: Don't delete commands/zones - they should be managed by ZoneAndCommandSeeder
        $tables = [
            'emolument_validations',
            'emolument_assessments',
            'emoluments',
            'leave_applications',
            'pass_applications',
            'staff_orders',
            'movement_orders',
            'officer_postings',
            'manning_request_items',
            'manning_requests',
            'emolument_timelines',
            'promotion_eligibility_lists',
            'retirement_lists',
            'deceased_officers',
            'officers',
            'user_roles',
            'users',
            // 'commands' - Don't delete commands, let ZoneAndCommandSeeder manage them
        ];
        
        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->delete();
                }
            } catch (\Exception $e) {
                // Table doesn't exist, skip
            }
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info('✅ Data cleared');
        
        // Get existing commands (created by ZoneAndCommandSeeder)
        // Commands should already exist since ZoneAndCommandSeeder runs first in DatabaseSeeder
        $commands = Command::all();
        if ($commands->isEmpty()) {
            $this->command->warn('⚠️  No commands found. Please run ZoneAndCommandSeeder first.');
            // Try to call ZoneAndCommandSeeder if commands don't exist
            $this->call(ZoneAndCommandSeeder::class);
            $commands = Command::all();
        }
        if ($commands->count() < 2) {
            $this->command->warn('⚠️  Less than 2 commands found. Some features may be limited.');
        }
        $this->command->info("✅ Using {$commands->count()} existing Commands");
        
        // Seed Roles (if not exists)
        $this->command->info('👥 Ensuring Roles exist...');
        $roles = [
            ['name' => 'HRD', 'code' => 'HRD', 'description' => 'Human Resources Department'],
            ['name' => 'Officer', 'code' => 'OFFICER', 'description' => 'Staff Officer'],
            ['name' => 'Staff Officer', 'code' => 'STAFF_OFFICER', 'description' => 'Staff Officer'],
            ['name' => 'Assessor', 'code' => 'ASSESSOR', 'description' => 'Emolument Assessor'],
            ['name' => 'Validator', 'code' => 'VALIDATOR', 'description' => 'Emolument Validator'],
            ['name' => 'Accounts', 'code' => 'ACCOUNTS', 'description' => 'Accounts Department'],
        ];
        
        foreach ($roles as $roleData) {
            Role::firstOrCreate(['code' => $roleData['code']], $roleData);
        }
        $roles = Role::all()->keyBy('code');
        $this->command->info("✅ Roles ready");
        
        // Create Role Users (use firstOrCreate to avoid conflicts with CompleteSystemSeeder)
        $this->command->info('👤 Creating Role Users...');
        $roleUsers = [];
        $roleEmails = [
            'HRD' => 'hrd@ncs.gov.ng',
            'OFFICER' => 'officer@ncs.gov.ng',
            'STAFF_OFFICER' => 'staff@ncs.gov.ng',
            'ASSESSOR' => 'assessor@ncs.gov.ng',
            'VALIDATOR' => 'validator@ncs.gov.ng',
            'ACCOUNTS' => 'accounts@ncs.gov.ng',
        ];
        
        foreach ($roleEmails as $code => $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            // Attach role if not already attached
            if (!$user->hasRole($roles[$code]->name)) {
                $user->roles()->attach($roles[$code]->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $roleUsers[$code] = $user;
        }
        $this->command->info("✅ Created/Updated Role Users");
        
        // Seed 100 Officers
        $this->command->info('👮 Seeding 100 Officers...');
        $surnames = [
            'NANJWAN', 'ADAMS', 'BENSON', 'CHUKWU', 'DAVID', 'EKWUEME', 'FALANA', 'GARBA', 'HASSAN', 'IBRAHIM',
            'JAMES', 'KALU', 'LAWAL', 'MUSA', 'NWANKWO', 'OGUNDIPE', 'PETER', 'QUADRI', 'RABIU', 'SALISU',
            'TUNDE', 'UMAR', 'VICTOR', 'WILLIAMS', 'YAKUBU', 'ZAINAB', 'ABUBAKAR', 'BALOGUN', 'CHIDI', 'DANIEL',
            'EMMANUEL', 'FATIMA', 'GABRIEL', 'HALIMA', 'ISAAC', 'JOSEPH', 'KABIRU', 'LAMIDI', 'MOHAMMED', 'NADIA',
            'OLUWASEUN', 'PATIENCE', 'RABIU', 'SAMUEL', 'TITUS', 'USMAN', 'VINCENT', 'WISDOM', 'YUSUF', 'ZAINAB',
            'ABDUL', 'BOLA', 'CHINEDU', 'DAMILOLA', 'ESTHER', 'FUNMI', 'GRACE', 'HABIB', 'IFEOMA', 'JOHN',
            'KEMI', 'LOLA', 'MARY', 'NGOZI', 'OLU', 'PAUL', 'RUTH', 'SEUN', 'TUNDE', 'UCHECHUKWU',
            'VICTORIA', 'WALE', 'YEMI', 'ZAINAB', 'ADEBAYO', 'BUKOLA', 'CHIOMA', 'DANIEL', 'ELIZABETH', 'FOLAKEMI',
            'GODWIN', 'HALIMA', 'IBRAHIM', 'JENNIFER', 'KELECHI', 'LUKMAN', 'MARYAM', 'NNEKA', 'OLUWATOYIN', 'PETER',
            'RACHEL', 'SALISU', 'TEMITOPE', 'UMAR', 'VIVIAN', 'WILLIAM', 'YAKUBU', 'ZAINAB', 'ABDULLAHI', 'BENEDICT'
        ];
        
        $initials = ['RD', 'AB', 'CD', 'EF', 'GH', 'IJ', 'KL', 'MN', 'OP', 'QR', 'ST', 'UV', 'WX', 'YZ'];
        $ranks = ['CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];
        $banks = ['Zenith Bank', 'GTBank', 'First Bank', 'UBA', 'Access Bank', 'Fidelity Bank'];
        $pfas = ['Stanbic IBTC Pension', 'ARM Pension', 'Premium Pension', 'Leadway Pension'];
        
        $officers = [];
        for ($i = 1; $i <= 100; $i++) {
            $command = $commands->random();
            $surname = $surnames[($i - 1) % count($surnames)];
            $init = $initials[($i - 1) % count($initials)];
            $serviceNumber = 'NCS' . str_pad($i, 5, '0', STR_PAD_LEFT);
            
            // Create User
            $user = User::create([
                'email' => "officer{$i}@ncs.gov.ng",
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);
            
            // Attach Officer Role
            $user->roles()->attach($roles['OFFICER']->id, ['is_active' => true, 'assigned_at' => now()]);
            
            // Create Officer
            $officer = Officer::create([
                'user_id' => $user->id,
                'service_number' => $serviceNumber,
                'initials' => $init,
                'surname' => $surname,
                'sex' => ($i % 2 == 0) ? 'M' : 'F',
                'date_of_birth' => Carbon::now()->subYears(rand(25, 50)),
                'date_of_first_appointment' => Carbon::now()->subYears(rand(5, 20)),
                'date_of_present_appointment' => Carbon::now()->subMonths(rand(1, 24)),
                'substantive_rank' => $ranks[array_rand($ranks)],
                'salary_grade_level' => rand(8, 16),
                'state_of_origin' => 'Lagos',
                'lga' => 'Ikeja',
                'geopolitical_zone' => 'South-West',
                'marital_status' => rand(0, 1) ? 'Married' : 'Single',
                'entry_qualification' => 'B.Sc',
                'discipline' => ['Accounting', 'Law', 'Computer Science', 'Economics'][array_rand(['Accounting', 'Law', 'Computer Science', 'Economics'])],
                'residential_address' => "{$command->location} Address",
                'permanent_home_address' => "Home Address {$i}",
                'present_station' => $command->id,
                'date_posted_to_station' => Carbon::now()->subMonths(rand(1, 36)),
                'phone_number' => '080' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'email' => $user->email,
                'bank_name' => $banks[array_rand($banks)],
                'bank_account_number' => str_pad($i, 10, '0', STR_PAD_LEFT),
                'pfa_name' => $pfas[array_rand($pfas)],
                'rsa_number' => 'PEN' . str_pad($i, 12, '0', STR_PAD_LEFT),
                'is_active' => true,
                'profile_picture_url' => 'officers/default.png', // Set placeholder to mark onboarding as complete
                'onboarding_status' => 'completed',
                'onboarding_completed_at' => Carbon::now()->subDays(rand(1, 30)), // Completed 1-30 days ago
                'verification_status' => 'verified',
                'verified_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
            
            $officers[] = $officer;
            
            if ($i % 20 == 0) {
                $this->command->info("  Created {$i}/100 officers...");
            }
        }
        $this->command->info("✅ Created 100 Officers");
        
        // Link test officer user
        $testOfficerUser = $roleUsers['OFFICER'];
        $officers[0]->update(['user_id' => $testOfficerUser->id]);
        
        // Create Emolument Timeline
        $this->command->info('📅 Creating Emolument Timeline...');
        $timeline = EmolumentTimeline::create([
            'year' => 2025,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(20),
            'is_active' => true,
            'created_by' => $roleUsers['HRD']->id,
        ]);
        $this->command->info("✅ Created Emolument Timeline");
        
        // Create Emoluments (various statuses)
        $this->command->info('💰 Creating Emoluments...');
        $emolumentStatuses = ['RAISED', 'ASSESSED', 'VALIDATED', 'PROCESSED'];
        foreach (array_slice($officers, 0, 40) as $index => $officer) {
            $status = $emolumentStatuses[$index % 4];
            $emolument = Emolument::create([
                'officer_id' => $officer->id,
                'timeline_id' => $timeline->id,
                'year' => 2025,
                'status' => $status,
                'bank_name' => $officer->bank_name,
                'bank_account_number' => $officer->bank_account_number,
                'pfa_name' => $officer->pfa_name,
                'rsa_pin' => $officer->rsa_number,
                'submitted_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);
            
            // Create assessment if assessed or beyond
            $assessment = null;
            if (in_array($status, ['ASSESSED', 'VALIDATED', 'PROCESSED'])) {
                $assessedAt = Carbon::now()->subDays(rand(1, 5));
                $assessment = EmolumentAssessment::create([
                    'emolument_id' => $emolument->id,
                    'assessor_id' => $roleUsers['ASSESSOR']->id,
                    'assessment_status' => 'APPROVED',
                    'comments' => 'Assessment completed',
                ]);
                $assessment->update(['assessed_at' => $assessedAt]);
                $emolument->update(['assessed_at' => $assessedAt]);
            }
            
            // Create validation if validated or processed (requires assessment)
            if (in_array($status, ['VALIDATED', 'PROCESSED']) && $assessment) {
                $validatedAt = Carbon::now()->subDays(rand(1, 3));
                $validation = EmolumentValidation::create([
                    'emolument_id' => $emolument->id,
                    'assessment_id' => $assessment->id,
                    'validator_id' => $roleUsers['VALIDATOR']->id,
                    'validation_status' => 'APPROVED',
                    'comments' => 'Validation completed',
                ]);
                $validation->update(['validated_at' => $validatedAt]);
                $emolument->update(['validated_at' => $validatedAt]);
            }
            
            // Mark as processed if processed
            if ($status === 'PROCESSED') {
                $emolument->update(['processed_at' => Carbon::now()->subDays(rand(1, 2))]);
            }
        }
        $this->command->info("✅ Created 40 Emoluments");
        
        // Create Staff Orders
        $this->command->info('📋 Creating Staff Orders...');
        // Need at least 2 commands to create staff orders
        if ($commands->count() < 2) {
            $this->command->warn('⚠️  Skipping Staff Orders - need at least 2 commands. Please run ZoneAndCommandSeeder first.');
        } else {
            $staffOrdersCreated = 0;
            for ($i = 0; $i < 10; $i++) {
                $officer = $officers[rand(10, 50)];
                $fromCommand = $commands->where('id', $officer->present_station)->first();
                $availableCommands = $commands->where('id', '!=', $officer->present_station);
                
                // Skip if no other command available
                if ($availableCommands->isEmpty()) {
                    continue;
                }
                
                $toCommand = $availableCommands->random();
                
                StaffOrder::create([
                    'order_number' => 'SO-' . date('Y') . '-' . date('md') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'officer_id' => $officer->id,
                    'from_command_id' => $fromCommand->id,
                    'to_command_id' => $toCommand->id,
                    'effective_date' => Carbon::now()->addDays(rand(10, 60)),
                    'order_type' => ['POSTING', 'TRANSFER', 'DEPLOYMENT'][array_rand(['POSTING', 'TRANSFER', 'DEPLOYMENT'])],
                    'created_by' => $roleUsers['HRD']->id,
                ]);
                $staffOrdersCreated++;
            }
            $this->command->info("✅ Created {$staffOrdersCreated} Staff Orders");
        }
        
        // Create Manning Requests
        $this->command->info('📝 Creating Manning Requests...');
        for ($i = 0; $i < 8; $i++) {
            $command = $commands->random();
            $status = ['DRAFT', 'SUBMITTED', 'APPROVED'][array_rand(['DRAFT', 'SUBMITTED', 'APPROVED'])];
            
            $manningRequest = ManningRequest::create([
                'command_id' => $command->id,
                'requested_by' => $roleUsers['STAFF_OFFICER']->id,
                'status' => $status,
                'submitted_at' => $status !== 'DRAFT' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'approved_at' => $status === 'APPROVED' ? Carbon::now()->subDays(rand(1, 10)) : null,
                'notes' => "Manning request for {$command->name}",
            ]);
            
            // Create manning request items
            for ($j = 0; $j < rand(2, 5); $j++) {
                ManningRequestItem::create([
                    'manning_request_id' => $manningRequest->id,
                    'rank' => $ranks[array_rand($ranks)],
                    'quantity_needed' => rand(1, 3),
                    'sex_requirement' => ['M', 'F', 'ANY'][array_rand(['M', 'F', 'ANY'])],
                ]);
            }
        }
        $this->command->info("✅ Created 8 Manning Requests");
        
        // Create Movement Orders
        $this->command->info('🚚 Creating Movement Orders...');
        $approvedManningRequests = ManningRequest::where('status', 'APPROVED')->get();
        for ($i = 0; $i < 5; $i++) {
            MovementOrder::create([
                'order_number' => 'MO-' . date('Y') . '-' . date('md') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'criteria_months_at_station' => rand(18, 36),
                'manning_request_id' => $approvedManningRequests->isNotEmpty() ? $approvedManningRequests->random()->id : null,
                'status' => 'DRAFT', // Use DRAFT only to avoid CHECK constraint issues
                'created_by' => $roleUsers['HRD']->id,
            ]);
        }
        $this->command->info("✅ Created 5 Movement Orders");
        
        // Create Leave Applications
        $this->command->info('🏖️  Creating Leave Applications...');
        $leaveTypes = LeaveType::all();
        if ($leaveTypes->isNotEmpty()) {
            foreach (array_slice($officers, 0, 30) as $officer) {
                LeaveApplication::create([
                    'officer_id' => $officer->id,
                    'leave_type_id' => $leaveTypes->random()->id,
                    'start_date' => Carbon::now()->addDays(rand(10, 60)),
                    'end_date' => Carbon::now()->addDays(rand(70, 90)),
                    'number_of_days' => rand(10, 20),
                    'reason' => 'Annual leave',
                    'status' => ['PENDING', 'APPROVED', 'REJECTED'][array_rand(['PENDING', 'APPROVED', 'REJECTED'])],
                    'submitted_at' => Carbon::now()->subDays(rand(1, 20)),
                ]);
            }
        }
        $this->command->info("✅ Created 30 Leave Applications");
        
        // Create Pass Applications
        $this->command->info('🎫 Creating Pass Applications...');
        foreach (array_slice($officers, 30, 20) as $officer) {
            PassApplication::create([
                'officer_id' => $officer->id,
                'start_date' => Carbon::now()->addDays(rand(5, 15)),
                'end_date' => Carbon::now()->addDays(rand(16, 20)),
                'number_of_days' => rand(2, 5),
                'reason' => 'Personal matters',
                'status' => ['PENDING', 'APPROVED'][array_rand(['PENDING', 'APPROVED'])],
                'submitted_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);
        }
        $this->command->info("✅ Created 20 Pass Applications");
        
        // Create Promotion Eligibility Lists (if table exists)
        if (DB::getSchemaBuilder()->hasTable('promotion_eligibility_lists')) {
            $this->command->info('📈 Creating Promotion Eligibility Lists...');
            try {
                for ($i = 0; $i < 3; $i++) {
                    PromotionEligibilityList::create([
                        'year' => 2025,
                        'generated_by' => $roleUsers['HRD']->id,
                        'status' => 'FINALIZED', // Valid values: DRAFT, FINALIZED, SUBMITTED_TO_BOARD
                    ]);
                }
                $this->command->info("✅ Created 3 Promotion Eligibility Lists");
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Could not create Promotion Eligibility Lists: " . $e->getMessage());
            }
        }
        
        // Create Retirement Lists (if table exists)
        if (DB::getSchemaBuilder()->hasTable('retirement_list')) {
            $this->command->info('👴 Creating Retirement Lists...');
            try {
                for ($i = 0; $i < 2; $i++) {
                    RetirementList::create([
                        'year' => 2025,
                        'generated_by' => $roleUsers['HRD']->id,
                        'status' => 'FINALIZED', // Valid values: DRAFT, FINALIZED, NOTIFIED
                    ]);
                }
                $this->command->info("✅ Created 2 Retirement Lists");
            } catch (\Exception $e) {
                $this->command->warn("⚠️  Could not create Retirement Lists: " . $e->getMessage());
            }
        }
        
        // Create Deceased Officers
        $this->command->info('🕊️  Creating Deceased Officers...');
        foreach (array_slice($officers, 95, 3) as $officer) {
            $officer->update([
                'is_deceased' => true,
                'deceased_date' => Carbon::now()->subDays(rand(10, 100))
            ]);
            
            DeceasedOfficer::create([
                'officer_id' => $officer->id,
                'reported_by' => $roleUsers['STAFF_OFFICER']->id,
                'reported_at' => Carbon::now()->subDays(rand(1, 90)),
                'date_of_death' => $officer->deceased_date,
            ]);
        }
        $this->command->info("✅ Created 3 Deceased Officers");
        
        $this->command->info('');
        $this->command->info('🎉 Test data seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info("  • {$commands->count()} Commands");
        $this->command->info("  • 100 Officers (Service Numbers: NCS00001 - NCS00100)");
        $this->command->info("  • 40 Emoluments (various statuses)");
        $this->command->info("  • 10 Staff Orders");
        $this->command->info("  • 8 Manning Requests");
        $this->command->info("  • 5 Movement Orders");
        $this->command->info("  • 30 Leave Applications");
        $this->command->info("  • 20 Pass Applications");
        $this->command->info("  • 3 Promotion Eligibility Lists");
        $this->command->info("  • 2 Retirement Lists");
        $this->command->info("  • 3 Deceased Officers");
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('  HRD: hrd@ncs.gov.ng / password123');
        $this->command->info('  Officer: officer@ncs.gov.ng / password123');
        $this->command->info('  All officers: officer1@ncs.gov.ng to officer100@ncs.gov.ng / password123');
    }
}

