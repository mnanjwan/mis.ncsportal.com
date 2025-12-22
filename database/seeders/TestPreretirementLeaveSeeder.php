<?php

namespace Database\Seeders;

use App\Models\Officer;
use App\Models\RetirementList;
use App\Models\RetirementListItem;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestPreretirementLeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates test data for preretirement leave functionality:
     * 1. Officers already past preretirement date (should be auto-placed)
     * 2. Officers approaching preretirement date (within 3 months)
     * 3. Officers far from retirement (more than 3 months)
     * 4. Officers already on preretirement leave
     * 5. Officers with CGC approval
     */
    public function run(): void
    {
        $this->command->info('🧪 Creating test data for Preretirement Leave functionality...');

        // Get HRD user for generating retirement lists
        $hrdUser = User::whereHas('roles', function ($query) {
            $query->where('code', 'HRD');
        })->first();

        if (!$hrdUser) {
            $this->command->error('HRD user not found. Please run RoleSeeder and CreateCGCUserSeeder first.');
            return;
        }

        // Get CGC user
        $cgcUser = User::whereHas('roles', function ($query) {
            $query->where('code', 'CGC');
        })->first();

        if (!$cgcUser) {
            $this->command->error('CGC user not found. Please run CreateCGCUserSeeder first.');
            return;
        }

        // Create test officers with different retirement scenarios
        $testOfficers = [];

        // Scenario 1: Officer already past preretirement date (should be auto-placed)
        $officer1 = $this->createTestOfficer('TEST001', 'John', 'Doe', Carbon::now()->subYears(60)->subMonths(4));
        $testOfficers[] = [
            'officer' => $officer1,
            'retirement_date' => Carbon::now()->subMonths(1), // Retired 1 month ago
            'preretirement_date' => Carbon::now()->subMonths(4), // Preretirement was 4 months ago
            'scenario' => 'Past preretirement date - should be auto-placed',
        ];

        // Scenario 2: Officer approaching preretirement date (within 3 months)
        $officer2 = $this->createTestOfficer('TEST002', 'Jane', 'Smith', Carbon::now()->subYears(60)->subMonths(2));
        $testOfficers[] = [
            'officer' => $officer2,
            'retirement_date' => Carbon::now()->addMonths(1), // Retires in 1 month
            'preretirement_date' => Carbon::now()->subMonths(2), // Preretirement was 2 months ago
            'scenario' => 'Approaching preretirement - should be auto-placed',
        ];

        // Scenario 3: Officer approaching preretirement date (exactly 3 months from now)
        $officer3 = $this->createTestOfficer('TEST003', 'Michael', 'Johnson', Carbon::now()->subYears(60)->subMonths(3));
        $testOfficers[] = [
            'officer' => $officer3,
            'retirement_date' => Carbon::now()->addMonths(3), // Retires in 3 months
            'preretirement_date' => Carbon::now(), // Preretirement is today
            'scenario' => 'Preretirement date is today - should be auto-placed',
        ];

        // Scenario 4: Officer approaching preretirement date (1 month from now)
        $officer4 = $this->createTestOfficer('TEST004', 'Sarah', 'Williams', Carbon::now()->subYears(60)->subMonths(2));
        $testOfficers[] = [
            'officer' => $officer4,
            'retirement_date' => Carbon::now()->addMonths(1), // Retires in 1 month
            'preretirement_date' => Carbon::now()->subMonths(2), // Preretirement was 2 months ago
            'scenario' => 'Already past preretirement date - should be auto-placed',
        ];

        // Scenario 5: Officer approaching preretirement date (2 months from now)
        $officer5 = $this->createTestOfficer('TEST005', 'David', 'Brown', Carbon::now()->subYears(60)->subMonths(1));
        $testOfficers[] = [
            'officer' => $officer5,
            'retirement_date' => Carbon::now()->addMonths(2), // Retires in 2 months
            'preretirement_date' => Carbon::now()->subMonths(1), // Preretirement was 1 month ago
            'scenario' => 'Already past preretirement date - should be auto-placed',
        ];

        // Scenario 6: Officer with CGC approval (already approved to work in office)
        $officer6 = $this->createTestOfficer('TEST006', 'Emily', 'Davis', Carbon::now()->subYears(60)->subMonths(4));
        $testOfficers[] = [
            'officer' => $officer6,
            'retirement_date' => Carbon::now()->subMonths(1), // Retired 1 month ago
            'preretirement_date' => Carbon::now()->subMonths(4), // Preretirement was 4 months ago
            'scenario' => 'CGC approved - should show as approved in office',
            'cgc_approved' => true,
        ];

        // Scenario 7: Officer approaching preretirement (1 week from now) - FOR "APPROACHING" PAGE
        $officer7 = $this->createTestOfficer('TEST007', 'Robert', 'Wilson', Carbon::now()->subYears(60)->subMonths(3)->subDays(7));
        $testOfficers[] = [
            'officer' => $officer7,
            'retirement_date' => Carbon::now()->addMonths(3)->subDays(7), // Retires in ~3 months
            'preretirement_date' => Carbon::now()->addWeek(), // Preretirement in 1 week
            'scenario' => 'Approaching preretirement - 1 week away (for approaching page)',
            'not_yet_placed' => true, // Flag to keep status NULL
        ];

        // Scenario 8: Officer approaching preretirement (1 month from now) - FOR "APPROACHING" PAGE
        $officer8 = $this->createTestOfficer('TEST008', 'Lisa', 'Anderson', Carbon::now()->subYears(60)->subMonths(2));
        $testOfficers[] = [
            'officer' => $officer8,
            'retirement_date' => Carbon::now()->addMonths(4), // Retires in 4 months
            'preretirement_date' => Carbon::now()->addMonth(), // Preretirement in 1 month
            'scenario' => 'Approaching preretirement - 1 month away (for approaching page)',
            'not_yet_placed' => true, // Flag to keep status NULL
        ];

        // Scenario 9: Officer approaching preretirement (2 months from now) - FOR "APPROACHING" PAGE
        $officer9 = $this->createTestOfficer('TEST009', 'James', 'Martinez', Carbon::now()->subYears(60)->subMonths(1));
        $testOfficers[] = [
            'officer' => $officer9,
            'retirement_date' => Carbon::now()->addMonths(5), // Retires in 5 months
            'preretirement_date' => Carbon::now()->addMonths(2), // Preretirement in 2 months
            'scenario' => 'Approaching preretirement - 2 months away (for approaching page)',
            'not_yet_placed' => true, // Flag to keep status NULL
        ];

        // Scenario 10: Officer approaching preretirement (exactly 3 months from now) - FOR "APPROACHING" PAGE
        $officer10 = $this->createTestOfficer('TEST010', 'Patricia', 'Taylor', Carbon::now()->subYears(60));
        $testOfficers[] = [
            'officer' => $officer10,
            'retirement_date' => Carbon::now()->addMonths(6), // Retires in 6 months
            'preretirement_date' => Carbon::now()->addMonths(3), // Preretirement in exactly 3 months
            'scenario' => 'Approaching preretirement - exactly 3 months away (for approaching page)',
            'not_yet_placed' => true, // Flag to keep status NULL
        ];

        // Create retirement list for current year
        $retirementList = RetirementList::firstOrCreate(
            ['year' => Carbon::now()->year],
            [
                'generated_by' => $hrdUser->id,
                'status' => 'FINALIZED',
                'generated_at' => now(),
            ]
        );

        $this->command->info("✅ Created retirement list for year {$retirementList->year}");

        // Create retirement list items
        $serialNumber = 1;
        foreach ($testOfficers as $testData) {
            $officer = $testData['officer'];
            $retirementDate = $testData['retirement_date'];
            $preretirementDate = $testData['preretirement_date'];
            $scenario = $testData['scenario'];
            $cgcApproved = $testData['cgc_approved'] ?? false;
            $notYetPlaced = $testData['not_yet_placed'] ?? false;

            // Determine retirement condition
            $ageAtRetirement = $retirementDate->diffInYears($officer->date_of_birth);
            $serviceAtRetirement = $retirementDate->diffInYears($officer->date_of_first_appointment);
            $retirementCondition = ($ageAtRetirement >= 60) ? 'AGE' : 'SVC';

            // Set preretirement leave status
            // If not_yet_placed flag is true, keep status as NULL (for "approaching" page)
            // If cgc_approved is true, set to CGC_APPROVED_IN_OFFICE
            // Otherwise, leave as NULL (will be auto-placed later)
            $preretirementStatus = null;
            if ($cgcApproved) {
                $preretirementStatus = 'CGC_APPROVED_IN_OFFICE';
            } elseif ($notYetPlaced) {
                $preretirementStatus = null; // Explicitly NULL for approaching page
            }

            // Create retirement list item
            $item = RetirementListItem::updateOrCreate(
                [
                    'retirement_list_id' => $retirementList->id,
                    'officer_id' => $officer->id,
                ],
                [
                    'serial_number' => $serialNumber++,
                    'rank' => $officer->substantive_rank ?? 'SC',
                    'initials' => $officer->initials,
                    'name' => $officer->surname,
                    'retirement_condition' => $retirementCondition,
                    'date_of_birth' => $officer->date_of_birth,
                    'date_of_first_appointment' => $officer->date_of_first_appointment,
                    'date_of_pre_retirement_leave' => $preretirementDate,
                    'retirement_date' => $retirementDate,
                    'notified' => false,
                    'preretirement_leave_status' => $preretirementStatus,
                    'cgc_approved_by' => $cgcApproved ? $cgcUser->id : null,
                    'cgc_approved_at' => $cgcApproved ? now() : null,
                    'cgc_approval_reason' => $cgcApproved ? 'Test approval for preretirement leave in office' : null,
                ]
            );

            // If CGC approved, update officer status
            if ($cgcApproved) {
                $officer->update([
                    'preretirement_leave_status' => 'PRERETIREMENT_LEAVE_IN_OFFICE',
                    'preretirement_leave_started_at' => now(),
                ]);
            }

            $this->command->info("  ✅ Created retirement item for {$officer->service_number} - {$scenario}");
        }

        $this->command->info('');
        $this->command->info('📊 Test Data Summary:');
        $this->command->info('  - Total test officers: ' . count($testOfficers));
        $this->command->info('  - Retirement list items created: ' . ($serialNumber - 1));
        $this->command->info('');
        $this->command->info('🧪 Test Scenarios Created:');
        $this->command->info('  1. TEST001 - Past preretirement date (should auto-place)');
        $this->command->info('  2. TEST002 - Approaching preretirement (should auto-place)');
        $this->command->info('  3. TEST003 - Preretirement date is today (should auto-place)');
        $this->command->info('  4. TEST004 - Already past preretirement (should auto-place)');
        $this->command->info('  5. TEST005 - Already past preretirement (should auto-place)');
        $this->command->info('  6. TEST006 - CGC approved (already approved in office)');
        $this->command->info('  7. TEST007 - Approaching preretirement (1 week away) - FOR "APPROACHING" PAGE');
        $this->command->info('  8. TEST008 - Approaching preretirement (1 month away) - FOR "APPROACHING" PAGE');
        $this->command->info('  9. TEST009 - Approaching preretirement (2 months away) - FOR "APPROACHING" PAGE');
        $this->command->info('  10. TEST010 - Approaching preretirement (3 months away) - FOR "APPROACHING" PAGE');
        $this->command->info('');
        $this->command->info('📋 Test Pages:');
        $this->command->info('  - "All Preretirement Leave" page: Shows officers already placed (TEST001-TEST006)');
        $this->command->info('  - "Officers Approaching" page: Shows officers not yet placed (TEST007-TEST010)');
        $this->command->info('');
        $this->command->info('🚀 Next Steps:');
        $this->command->info('  1. Run: php artisan tinker');
        $this->command->info('  2. Execute: (new App\Services\RetirementService)->checkAndActivatePreRetirementStatus()');
        $this->command->info('  3. Login as CGC (cgc@ncs.gov.ng / password123)');
        $this->command->info('  4. Check "All Preretirement Leave" dashboard');
        $this->command->info('  5. Check "Officers Approaching Preretirement Leave" dashboard');
        $this->command->info('');
    }

    /**
     * Create a test officer
     */
    private function createTestOfficer(string $serviceNumber, string $initials, string $surname, Carbon $dateOfBirth): Officer
    {
        // Normalize service number (add NCS prefix if not present)
        $normalizedServiceNumber = str_starts_with($serviceNumber, 'NCS') ? $serviceNumber : 'NCS' . $serviceNumber;
        
        // Check if officer already exists
        $officer = Officer::where('service_number', $normalizedServiceNumber)
            ->orWhere('service_number', $serviceNumber)
            ->first();
        
        if ($officer) {
            // Update dates if needed
            $officer->update([
                'service_number' => $normalizedServiceNumber,
                'date_of_birth' => $dateOfBirth,
                'date_of_first_appointment' => $dateOfBirth->copy()->subYears(25), // Assume 25 years old at appointment
                'date_of_present_appointment' => $dateOfBirth->copy()->subYears(20), // Assume promoted 20 years ago
            ]);
            return $officer;
        }

        // Create user for officer (use unique email)
        $email = 'test' . strtolower($serviceNumber) . '@test.ncs.gov.ng';
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Check if user already has an officer
        if ($user->officer) {
            // Update existing officer
            $officer = $user->officer;
            $officer->update([
                'service_number' => $serviceNumber,
                'initials' => $initials,
                'surname' => $surname,
                'date_of_birth' => $dateOfBirth,
                'date_of_first_appointment' => $dateOfBirth->copy()->subYears(25),
                'date_of_present_appointment' => $dateOfBirth->copy()->subYears(20),
                'email' => $email,
            ]);
            return $officer;
        }

        // Create or update officer
        $officer = Officer::updateOrCreate(
            ['service_number' => $normalizedServiceNumber],
            [
                'user_id' => $user->id,
                'initials' => $initials,
                'surname' => $surname,
                'date_of_birth' => $dateOfBirth,
                'date_of_first_appointment' => $dateOfBirth->copy()->subYears(25), // Assume 25 years old at appointment
                'date_of_present_appointment' => $dateOfBirth->copy()->subYears(20), // Assume promoted 20 years ago
                'substantive_rank' => 'SC',
                'salary_grade_level' => '08',
                'sex' => 'M',
                'email' => $email,
                'is_active' => true,
                'present_station' => 1, // Assuming command ID 1 exists
            ]
        );

        return $officer;
    }
}
