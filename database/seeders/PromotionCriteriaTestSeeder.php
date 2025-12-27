<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PromotionCriteriaTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 5 officers who meet promotion criteria requirements:
     * - AIC: 2.00 years required
     * - CA I: 1.00 years required
     * - CSC: 2.00 years required
     */
    public function run(): void
    {
        // Get a command for officers
        $command = Command::first();
        if (!$command) {
            $this->command->error('No commands found. Please run ZoneAndCommandSeeder first.');
            return;
        }

        // Get HRD user for created_by field
        $hrdUser = User::where('email', 'hrd@ncs.gov.ng')->first();
        if (!$hrdUser) {
            $this->command->error('HRD user not found. Please run RoleSeeder and UserSeeder first.');
            return;
        }

        // Get Officer role
        $officerRole = Role::where('code', 'OFFICER')->first();
        if (!$officerRole) {
            $this->command->error('Officer role not found. Please run RoleSeeder first.');
            return;
        }

        // Define test officers with ranks that match promotion criteria
        // Based on criteria: AIC (2 years), CA I (1 year), CSC (2 years)
        $testOfficers = [
            [
                'rank' => 'AIC',
                'years_in_rank' => 2.5, // Meets 2.00 requirement
                'initials' => 'A.B.',
                'surname' => 'Adekunle',
                'sex' => 'M',
            ],
            [
                'rank' => 'AIC',
                'years_in_rank' => 2.2, // Meets 2.00 requirement
                'initials' => 'C.D.',
                'surname' => 'Chukwu',
                'sex' => 'F',
            ],
            [
                'rank' => 'CA I',
                'years_in_rank' => 1.5, // Meets 1.00 requirement
                'initials' => 'E.F.',
                'surname' => 'Eze',
                'sex' => 'M',
            ],
            [
                'rank' => 'CA I',
                'years_in_rank' => 1.2, // Meets 1.00 requirement
                'initials' => 'G.H.',
                'surname' => 'Garba',
                'sex' => 'F',
            ],
            [
                'rank' => 'CSC',
                'years_in_rank' => 2.8, // Meets 2.00 requirement
                'initials' => 'I.J.',
                'surname' => 'Ibrahim',
                'sex' => 'M',
            ],
        ];

        $createdCount = 0;
        $usedServiceNumbers = [];

        foreach ($testOfficers as $index => $testOfficer) {
            // Generate unique service number (mutator will add NCS prefix)
            do {
                $serviceNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                $fullServiceNumber = 'NCS' . $serviceNumber;
            } while (in_array($serviceNumber, $usedServiceNumbers) || Officer::where('service_number', $fullServiceNumber)->exists());
            $usedServiceNumbers[] = $serviceNumber;

            // Calculate dates based on years_in_rank requirement
            $dateOfPresentAppointment = Carbon::now()->subYears((int)$testOfficer['years_in_rank'])
                ->subMonths((int)(($testOfficer['years_in_rank'] - (int)$testOfficer['years_in_rank']) * 12))
                ->subDays(rand(0, 30)); // Add some random days for variation

            $dateOfFirstAppointment = $dateOfPresentAppointment->copy()->subYears(rand(3, 8));
            $dateOfBirth = Carbon::now()->subYears(rand(28, 45));

            // Create User for Officer
            $user = User::create([
                'email' => "promo.test{$serviceNumber}@ncs.gov.ng",
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
                'created_by' => $hrdUser->id,
            ]);

            // Attach Officer Role
            $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

            // Create Officer Profile
            $officer = Officer::create([
                'user_id' => $user->id,
                'service_number' => $serviceNumber, // Mutator will add NCS prefix
                'initials' => $testOfficer['initials'],
                'surname' => $testOfficer['surname'],
                'sex' => $testOfficer['sex'],
                'date_of_birth' => $dateOfBirth,
                'date_of_first_appointment' => $dateOfFirstAppointment,
                'date_of_present_appointment' => $dateOfPresentAppointment,
                'substantive_rank' => $testOfficer['rank'],
                'salary_grade_level' => $this->getSalaryGradeForRank($testOfficer['rank']),
                'state_of_origin' => 'Lagos',
                'lga' => 'Ikeja',
                'geopolitical_zone' => 'South-West',
                'marital_status' => 'Single',
                'entry_qualification' => 'B.Sc',
                'discipline' => 'Accounting',
                'additional_qualification' => null,
                'residential_address' => 'Lagos State, Ikeja LGA',
                'permanent_home_address' => 'Lagos State, Ikeja LGA',
                'present_station' => $command->id,
                'date_posted_to_station' => $dateOfPresentAppointment->copy()->subMonths(rand(0, 6)),
                'phone_number' => '080' . rand(10000000, 99999999),
                'email' => $user->email,
                'bank_name' => 'Zenith Bank',
                'bank_account_number' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                'sort_code' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'pfa_name' => 'Stanbic IBTC Pension',
                'rsa_number' => 'PEN' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
                'unit' => 'Unit 1',
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
                'quartered' => false,
                'is_deceased' => false,
                'is_active' => true,
                'created_by' => $hrdUser->id,
            ]);

            $createdCount++;
            $yearsInRank = Carbon::parse($dateOfPresentAppointment)->diffInYears(now());
            
            $this->command->info("✓ Created Officer: {$officer->service_number} - {$testOfficer['surname']}, {$testOfficer['rank']} ({$yearsInRank} years in rank)");
        }

        $this->command->info("\n✅ Successfully created {$createdCount} officers eligible for promotion!");
        $this->command->info("These officers meet the promotion criteria and should appear in eligibility lists.");
    }

    /**
     * Get salary grade level based on rank
     */
    private function getSalaryGradeForRank($rank): int
    {
        $rankGrades = [
            'CGC' => 18,
            'DCG' => 17,
            'ACG' => 16,
            'CC' => 15,
            'DC' => 14,
            'AC' => 13,
            'CSC' => 12,
            'SC' => 11,
            'DSC' => 10,
            'ASC I' => 9,
            'ASC II' => 8,
            'IC' => 7,
            'AIC' => 6,
            'CA I' => 5,
            'CA II' => 4,
            'CA III' => 3,
        ];

        return $rankGrades[$rank] ?? 5;
    }
}

