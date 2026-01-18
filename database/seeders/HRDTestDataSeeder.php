<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Command;
use App\Models\Officer;
use App\Models\User;
use App\Models\Role;
use App\Models\EmolumentTimeline;
use App\Models\StaffOrder;
use App\Models\MovementOrder;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\PromotionEligibilityCriterion;
use App\Models\PromotionEligibilityList;
use App\Models\PromotionEligibilityListItem;
use App\Models\RetirementList;
use App\Models\RetirementListItem;
use App\Models\LeaveType;
use App\Models\OfficerCourse;
use App\Models\SystemSetting;
use App\Models\Bank;
use App\Models\Pfa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HRDTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder creates comprehensive test data for HRD role testing
     * Data is NOT deleted - it persists for viewing results
     */
    public function run()
    {
        $this->command->info('Creating HRD Test Data...');
        
        // Create commands if they don't exist
        $this->createCommands();
        
        // Create HRD user if doesn't exist
        $hrdUserId = $this->createHRDUser();
        
        // Create officers with various statuses
        $this->createOfficers();
        
        // Create emolument timeline
        $this->createEmolumentTimeline($hrdUserId);
        
        // Create staff orders (only if officers and commands exist)
        $this->createStaffOrders($hrdUserId);
        
        // Create manning requests
        $this->createManningRequests($hrdUserId);
        
        // Create movement orders
        $this->createMovementOrders($hrdUserId);
        
        // Create promotion criteria
        $this->createPromotionCriteria($hrdUserId);
        
        // Create promotion eligibility lists
        $this->createPromotionEligibilityLists($hrdUserId);
        
        // Create retirement lists
        $this->createRetirementLists($hrdUserId);
        
        // Create leave types
        $this->createLeaveTypes($hrdUserId);
        
        // Create course nominations
        $this->createCourseNominations($hrdUserId);
        
        // Create system settings
        $this->createSystemSettings($hrdUserId);
        
        $this->command->info('HRD Test Data created successfully!');
    }

    private function createCommands()
    {
        // Don't create commands here - use existing commands from ZoneAndCommandSeeder
        // This prevents duplicate commands with different codes
        // HRDTestDataSeeder should work with commands that already exist
        $existingCommands = Command::all();
        if ($existingCommands->isEmpty()) {
            $this->command->warn('⚠️  No commands found. ZoneAndCommandSeeder should run first.');
        } else {
            $this->command->info('✅ Using existing commands from ZoneAndCommandSeeder');
        }
    }

    private function createHRDUser()
    {
        $user = User::firstOrCreate(
            ['email' => 'hrd@ncs.gov.ng'],
            [
                'email' => 'hrd@ncs.gov.ng',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $hrdRole = Role::firstOrCreate(['name' => 'HRD']);
        if (!$user->roles()->where('name', 'HRD')->exists()) {
            $user->roles()->attach($hrdRole->id, [
                'assigned_at' => now(),
                'assigned_by' => $user->id,
                'is_active' => true,
            ]);
        }
        
        return $user->id;
    }

    private function createOfficers()
    {
        $commands = Command::all();
        $ranks = ['CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];

        $bankRecords = Bank::query()->where('is_active', true)->get(['name', 'account_number_digits']);
        if ($bankRecords->isEmpty()) {
            $bankRecords = collect(['Test Bank'])->map(fn ($name) => (object) ['name' => $name, 'account_number_digits' => 10]);
        }

        $pfaRecords = Pfa::query()->where('is_active', true)->get(['name', 'rsa_prefix', 'rsa_digits']);
        if ($pfaRecords->isEmpty()) {
            $pfaRecords = collect(['Test PFA'])->map(fn ($name) => (object) ['name' => $name, 'rsa_prefix' => 'PEN', 'rsa_digits' => 12]);
        }

        $randomDigits = function (int $length): string {
            $length = max(1, $length);
            $out = '';
            for ($i = 0; $i < $length; $i++) {
                $out .= (string) random_int(0, 9);
            }
            return $out;
        };
        
        // Create officers with various statuses for testing
        for ($i = 1; $i <= 50; $i++) {
            $command = $commands->random();
            $rank = $ranks[array_rand($ranks)];
            $birthYear = 1965 + rand(0, 30);
            $appointmentYear = 2000 + rand(0, 20);

            $bank = $bankRecords->random();
            $pfa = $pfaRecords->random();
            
            $officer = Officer::firstOrCreate(
                ['service_number' => 'NCS' . str_pad($i, 5, '0', STR_PAD_LEFT)],
                [
                    'service_number' => 'NCS' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'initials' => 'HRD',
                    'surname' => 'TEST' . $i,
                    'sex' => rand(0, 1) ? 'M' : 'F',
                    'date_of_birth' => Carbon::create($birthYear, rand(1, 12), rand(1, 28)),
                    'date_of_first_appointment' => Carbon::create($appointmentYear, rand(1, 12), rand(1, 28)),
                    'date_of_present_appointment' => Carbon::now()->subMonths(rand(6, 60)),
                    'substantive_rank' => $rank,
                    'salary_grade_level' => 'GL' . rand(7, 12),
                    'state_of_origin' => 'Lagos',
                    'lga' => 'Ikeja',
                    'geopolitical_zone' => 'South West',
                    'marital_status' => 'Married',
                    'entry_qualification' => 'BSc',
                    'present_station' => $command->id,
                    'date_posted_to_station' => Carbon::now()->subMonths(rand(1, 24)),
                    'residential_address' => 'Test Address ' . $i,
                    'permanent_home_address' => 'Test Home Address ' . $i,
                    'phone_number' => '080' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'email' => 'officer' . $i . '@test.ncs.gov.ng',
                    'bank_name' => $bank->name,
                    'bank_account_number' => $randomDigits((int) ($bank->account_number_digits ?? 10)),
                    'pfa_name' => $pfa->name,
                    'rsa_number' => strtoupper((string) ($pfa->rsa_prefix ?? 'PEN')) . $randomDigits((int) ($pfa->rsa_digits ?? 12)),
                    'is_active' => true,
                    'interdicted' => $i % 10 == 0, // Every 10th officer is interdicted
                    'suspended' => $i % 15 == 0, // Every 15th officer is suspended
                    'dismissed' => false,
                    'is_deceased' => false,
                ]
            );
        }
    }

    private function createEmolumentTimeline($userId)
    {
        EmolumentTimeline::firstOrCreate(
            ['year' => date('Y')],
            [
                'year' => date('Y'),
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => true,
                'is_extended' => false,
                'created_by' => $userId,
            ]
        );
    }

    private function createStaffOrders($userId)
    {
        // Only use officers created by this seeder (with HRD TEST surname)
        $officers = Officer::where('surname', 'LIKE', 'TEST%')
            ->whereNotNull('present_station')
            ->take(10)
            ->get();
        // Use actual command codes from ZoneAndCommandSeeder
        $commands = Command::whereIn('code', ['APAPA', 'FCT', 'KADUNA', 'PH_I_BAYELSA', 'OYO_OSUN'])->get();
        
        if ($officers->isEmpty() || $commands->count() < 2) {
            $this->command->warn('Skipping staff orders - need at least 2 commands and officers with present_station');
            return;
        }
        
        foreach ($officers as $index => $officer) {
            $fromCommand = $officer->presentStation;
            if (!$fromCommand) {
                continue;
            }
            
            $toCommand = $commands->where('id', '!=', $fromCommand->id)->first();
            if (!$toCommand) {
                continue;
            }
            
            $orderNumber = 'SO-HRD-' . date('Y') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            if (!StaffOrder::where('order_number', $orderNumber)->exists()) {
                try {
                    StaffOrder::create([
                        'order_number' => $orderNumber,
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommand->id,
                        'to_command_id' => $toCommand->id,
                        'effective_date' => Carbon::now()->addDays(rand(1, 30)),
                        'order_type' => ['POSTING', 'TRANSFER', 'DEPLOYMENT'][rand(0, 2)],
                        'created_by' => $userId,
                    ]);
                } catch (\Exception $e) {
                    $this->command->warn("Failed to create staff order: " . $e->getMessage());
                }
            }
        }
    }

    private function createManningRequests($userId)
    {
        // Use actual command codes from ZoneAndCommandSeeder
        $commands = Command::whereIn('code', ['APAPA', 'FCT', 'KADUNA', 'PH_I_BAYELSA', 'OYO_OSUN'])->get();
        
        if ($commands->isEmpty() || !$userId) {
            $this->command->warn('Skipping manning requests - no commands or user available');
            return;
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $command = $commands->random();
            $requestNumber = 'MR-HRD-' . date('Y') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            try {
            // Check if manning request already exists for this command
            $request = ManningRequest::where('command_id', $command->id)
                ->where('status', 'APPROVED')
                ->first();
            
            if (!$request) {
                $request = ManningRequest::create([
                    'command_id' => $command->id,
                    'requested_by' => $userId,
                    'status' => 'APPROVED',
                    'notes' => 'Test manning request ' . $i,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }
                
                // Create items for each request
                $availableRanks = ['CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC', 'CSC', 'SC', 'DSC', 'ASC I', 'ASC II', 'IC', 'AIC', 'CA I', 'CA II', 'CA III'];
                for ($j = 1; $j <= 3; $j++) {
                    $rank = $availableRanks[array_rand($availableRanks)];
                    
                    if (!ManningRequestItem::where('manning_request_id', $request->id)
                        ->where('rank', $rank)
                        ->exists()) {
                        ManningRequestItem::create([
                            'manning_request_id' => $request->id,
                            'rank' => $rank,
                            'quantity_needed' => rand(2, 5),
                            'sex_requirement' => ['M', 'F', 'ANY'][rand(0, 2)],
                            'qualification_requirement' => 'BSc',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->command->warn("Failed to create manning request {$i}: " . $e->getMessage());
            }
        }
    }

    private function createMovementOrders($userId)
    {
        $manningRequests = ManningRequest::where('status', 'APPROVED')
            ->take(3)
            ->get();
        
        if ($manningRequests->isEmpty()) {
            $this->command->warn('Skipping movement orders - no approved manning requests available');
            return;
        }
        
        foreach ($manningRequests as $index => $request) {
            $orderNumber = 'MO-HRD-' . date('Y') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            if (!MovementOrder::where('order_number', $orderNumber)->exists()) {
                MovementOrder::create([
                    'order_number' => $orderNumber,
                    'criteria_months_at_station' => rand(12, 36),
                    'manning_request_id' => $request->id,
                    'status' => ['DRAFT', 'PUBLISHED'][rand(0, 1)], // Valid values: DRAFT, PUBLISHED, CANCELLED
                    'created_by' => $userId,
                ]);
            }
        }
    }

    private function createPromotionCriteria($userId)
    {
        $ranks = [
            'CA III' => 2,
            'CA II' => 2,
            'CA I' => 2,
            'AIC' => 2,
            'IC' => 2,
            'ASC II' => 2,
            'ASC I' => 2,
            'DSC' => 2,
            'SC' => 2,
            'CSC' => 2,
            'AC' => 2,
            'DC' => 2,
            'CC' => 2,
            'ACG' => 2,
            'DCG' => 2,
            'CGC' => 0, // Top rank, no promotion needed
        ];
        
        foreach ($ranks as $rank => $years) {
            PromotionEligibilityCriterion::firstOrCreate(
                ['rank' => $rank, 'is_active' => true],
                [
                    'rank' => $rank,
                    'years_in_rank_required' => $years,
                    'is_active' => true,
                    'created_by' => $userId,
                ]
            );
        }
    }

    private function createPromotionEligibilityLists($userId)
    {
        for ($year = date('Y') - 1; $year <= date('Y') + 1; $year++) {
            $list = PromotionEligibilityList::firstOrCreate(
                ['year' => $year],
                [
                    'year' => $year,
                    'generated_by' => $userId,
                    'status' => 'DRAFT',
                ]
            );
            
            // Only add officers if list is empty
            if ($list->items()->count() == 0) {
                // Add some officers to the list (only HRD test officers)
                $officers = Officer::where('surname', 'LIKE', 'TEST%')
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->where('interdicted', false)
                    ->where('suspended', false)
                    ->where('dismissed', false)
                    ->take(10)
                    ->get();
                
                $serialNumber = 1;
                foreach ($officers as $officer) {
                    PromotionEligibilityListItem::create([
                        'eligibility_list_id' => $list->id,
                        'officer_id' => $officer->id,
                        'serial_number' => $serialNumber++,
                        'current_rank' => $officer->substantive_rank ?? 'N/A',
                        'years_in_rank' => $officer->date_of_present_appointment ? 
                            Carbon::parse($officer->date_of_present_appointment)->diffInYears(now()) : 0,
                        'date_of_first_appointment' => $officer->date_of_first_appointment ?? now(),
                        'date_of_present_appointment' => $officer->date_of_present_appointment ?? now(),
                        'state' => $officer->state_of_origin ?? 'N/A',
                        'date_of_birth' => $officer->date_of_birth ?? now(),
                    ]);
                }
            }
        }
    }

    private function createRetirementLists($userId)
    {
        for ($year = date('Y'); $year <= date('Y') + 2; $year++) {
            $list = RetirementList::firstOrCreate(
                ['year' => $year],
                [
                    'year' => $year,
                    'generated_by' => $userId,
                    'status' => 'DRAFT',
                ]
            );
            
            // Only add officers if list is empty
            if ($list->items()->count() == 0) {
                // Add some officers to the list (only HRD test officers)
                $officers = Officer::where('surname', 'LIKE', 'TEST%')
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->take(5)
                    ->get();
                
                $serialNumber = 1;
                foreach ($officers as $officer) {
                    $retirementDate = Carbon::create($year, 12, 31);
                    $preRetirementLeave = $retirementDate->copy()->subMonths(3);
                    
                    RetirementListItem::create([
                        'retirement_list_id' => $list->id,
                        'officer_id' => $officer->id,
                        'serial_number' => $serialNumber++,
                        'rank' => $officer->substantive_rank ?? 'N/A',
                        'initials' => $officer->initials ?? '',
                        'name' => $officer->surname ?? '',
                        'retirement_condition' => 'AGE',
                        'date_of_birth' => $officer->date_of_birth ?? now(),
                        'date_of_first_appointment' => $officer->date_of_first_appointment ?? now(),
                        'date_of_pre_retirement_leave' => $preRetirementLeave,
                        'retirement_date' => $retirementDate,
                        'notified' => false,
                    ]);
                }
            }
        }
    }

    private function createLeaveTypes($userId)
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'code' => 'AL', 'max_duration_days' => 30, 'max_occurrences_per_year' => 2],
            ['name' => 'Sick Leave', 'code' => 'SL', 'max_duration_days' => 14, 'max_occurrences_per_year' => 4],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'max_duration_days' => 90, 'max_occurrences_per_year' => 1],
            ['name' => 'Study Leave', 'code' => 'STL', 'max_duration_days' => 180, 'max_occurrences_per_year' => 1],
        ];
        
        foreach ($leaveTypes as $type) {
            // Check by code first, then by name if code doesn't exist
            $existing = LeaveType::where('code', $type['code'])->first();
            if (!$existing) {
                $existing = LeaveType::where('name', $type['name'])->first();
            }
            
            if (!$existing) {
                LeaveType::create(array_merge($type, [
                    'requires_medical_certificate' => $type['code'] === 'SL',
                    'requires_approval_level' => 'DC_ADMIN',
                    'is_active' => true,
                    'created_by' => $userId,
                ]));
            }
        }
    }

    private function createCourseNominations($userId)
    {
        // Only use HRD test officers
        $officers = Officer::where('surname', 'LIKE', 'TEST%')
            ->where('is_active', true)
            ->take(10)
            ->get();
        $courses = [
            'Leadership Development Program',
            'Advanced Management Course',
            'Strategic Planning Workshop',
            'Digital Transformation Training',
        ];
        
        foreach ($officers as $index => $officer) {
            $courseName = $courses[$index % count($courses)];
            
            if (!OfficerCourse::where('officer_id', $officer->id)
                ->where('course_name', $courseName)
                ->exists()) {
                OfficerCourse::create([
                    'officer_id' => $officer->id,
                    'course_name' => $courseName,
                    'course_type' => ['MANDATORY', 'OPTIONAL'][rand(0, 1)],
                    'start_date' => Carbon::now()->addDays(rand(1, 30)),
                    'end_date' => Carbon::now()->addDays(rand(31, 60)),
                    'is_completed' => rand(0, 1) == 1,
                    'completion_date' => rand(0, 1) == 1 ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'nominated_by' => $userId,
                    'notes' => 'Test course nomination ' . ($index + 1),
                ]);
            }
        }
    }

    private function createSystemSettings($userId)
    {
        $settings = [
            'retirement_age' => 60,
            'years_of_service_for_retirement' => 35,
            'pre_retirement_leave_months' => 3,
            'annual_leave_days_gl07_below' => 28,
            'annual_leave_days_gl08_above' => 30,
            'annual_leave_max_applications' => 2,
            'pass_max_days' => 5,
            'rsa_pin_prefix' => 'PEN',
            'rsa_pin_length' => 12,
        ];
        
        foreach ($settings as $key => $value) {
            SystemSetting::firstOrCreate(
                ['setting_key' => $key],
                [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'description' => 'Test setting for ' . $key,
                    'updated_by' => $userId,
                ]
            );
        }
    }
}

