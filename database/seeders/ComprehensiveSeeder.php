<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\DeceasedOfficer;
use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Officer;
use App\Models\PassApplication;
use App\Models\Role;
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
        $faker = \Faker\Factory::create();

        // 1. Ensure Roles and Commands exist (assuming RoleSeeder and CommandSeeder ran)
        $commands = Command::all();
        if ($commands->isEmpty()) {
            $this->call(CommandSeeder::class);
            $commands = Command::all();
        }

        // 2. Create Users for each Role
        $roles = Role::all();
        $roleUsers = [];

        foreach ($roles as $role) {
            $email = strtolower(str_replace(' ', '.', $role->name)) . '@ncs.gov.ng';

            // Special case for Staff Officer to match login instructions if needed, 
            // but let's stick to a pattern: hrd@ncs.gov.ng, staff.officer@ncs.gov.ng, etc.
            if ($role->code === 'STAFF_OFFICER')
                $email = 'staff@ncs.gov.ng';
            if ($role->code === 'HRD')
                $email = 'hrd@ncs.gov.ng';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (!$user->roles->contains($role->id)) {
                $user->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
            }

            $roleUsers[$role->code] = $user;
            $this->command->info("Created/Found User: {$email} with Role: {$role->name}");
        }

        // 3. Create ~50 Officers
        $ranks = ['ASC II', 'ASC I', 'DSC', 'SC', 'CSC', 'AC', 'DC', 'CC', 'ACG', 'DCG'];
        $officers = [];

        for ($i = 0; $i < 50; $i++) {
            $command = $commands->random();
            $rank = $faker->randomElement($ranks);
            $serviceNumber = $faker->unique()->numberBetween(10000, 99999);

            // Create User for Officer
            $user = User::create([
                'email' => "officer{$serviceNumber}@ncs.gov.ng",
                'password' => Hash::make('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Attach Officer Role
            $officerRole = Role::where('code', 'OFFICER')->first();
            $user->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);

            // Create Officer Profile
            $officer = Officer::create([
                'user_id' => $user->id,
                'service_number' => $serviceNumber,
                'initials' => strtoupper($faker->randomLetter . '.' . $faker->randomLetter),
                'surname' => $faker->lastName,
                'sex' => $faker->randomElement(['M', 'F']),
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-25 years'),
                'date_of_first_appointment' => $faker->dateTimeBetween('-20 years', '-5 years'),
                'date_of_present_appointment' => $faker->dateTimeBetween('-5 years', 'now'),
                'substantive_rank' => $rank,
                'salary_grade_level' => $faker->numberBetween(8, 16),
                'state_of_origin' => $faker->state,
                'lga' => $faker->city,
                'geopolitical_zone' => $faker->randomElement(['North-Central', 'North-East', 'North-West', 'South-East', 'South-South', 'South-West']),
                'marital_status' => $faker->randomElement(['Single', 'Married']),
                'entry_qualification' => 'B.Sc',
                'discipline' => $faker->randomElement(['Accounting', 'Law', 'Computer Science', 'Economics']),
                'residential_address' => $faker->address,
                'permanent_home_address' => $faker->address,
                'present_station' => $command->id,
                'date_posted_to_station' => $faker->dateTimeBetween('-2 years', 'now'),
                'phone_number' => $faker->phoneNumber,
                'email' => $user->email,
                'bank_name' => $faker->randomElement(['Zenith Bank', 'GTBank', 'First Bank', 'UBA']),
                'bank_account_number' => $faker->numerify('##########'),
                'pfa_name' => $faker->randomElement(['Stanbic IBTC Pension', 'ARM Pension', 'Premium Pension']),
                'rsa_number' => 'PEN' . $faker->numerify('############'),
                'unit' => $faker->word,
                'is_active' => true,
            ]);

            $officers[] = $officer;
        }

        $this->command->info("Created 50 Officers");

        // Link the test officer user to the first officer profile
        $testOfficerUser = $roleUsers['OFFICER'];
        $firstOfficer = $officers[0];
        $firstOfficer->update(['user_id' => $testOfficerUser->id]);
        $this->command->info("Linked officer@ncs.gov.ng to Officer: {$firstOfficer->service_number}");

        // 4. Create Emolument Timeline
        $timeline = EmolumentTimeline::create([
            'year' => 2025,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'is_active' => true,
            'created_by' => $roleUsers['HRD']->id,
        ]);

        // 5. Create Emoluments
        foreach (array_slice($officers, 0, 20) as $officer) {
            Emolument::create([
                'officer_id' => $officer->id,
                'timeline_id' => $timeline->id,
                'year' => 2025,
                'status' => $faker->randomElement(['RAISED', 'ASSESSED', 'VALIDATED']),
                'bank_name' => $officer->bank_name,
                'bank_account_number' => $officer->bank_account_number,
                'pfa_name' => $officer->pfa_name,
                'rsa_pin' => $officer->rsa_number,
                'submitted_at' => now()->subDays(rand(1, 5)),
            ]);
        }
        $this->command->info("Created Emoluments");

        // 6. Create Leave Applications
        $leaveTypes = LeaveType::all();
        if ($leaveTypes->isNotEmpty()) {
            foreach (array_slice($officers, 0, 15) as $officer) {
                LeaveApplication::create([
                    'officer_id' => $officer->id,
                    'leave_type_id' => $leaveTypes->random()->id,
                    'start_date' => now()->addDays(rand(10, 30)),
                    'end_date' => now()->addDays(rand(35, 50)),
                    'number_of_days' => rand(10, 20),
                    'reason' => $faker->sentence,
                    'status' => $faker->randomElement(['PENDING', 'APPROVED', 'REJECTED']),
                    'submitted_at' => now()->subDays(rand(1, 10)),
                ]);
            }
        }
        $this->command->info("Created Leave Applications");

        // 7. Create Pass Applications
        foreach (array_slice($officers, 15, 10) as $officer) {
            PassApplication::create([
                'officer_id' => $officer->id,
                'start_date' => now()->addDays(rand(5, 10)),
                'end_date' => now()->addDays(rand(11, 15)),
                'number_of_days' => rand(2, 5),
                'reason' => $faker->sentence,
                'status' => $faker->randomElement(['PENDING', 'APPROVED']),
                'submitted_at' => now()->subDays(rand(1, 5)),
            ]);
        }
        $this->command->info("Created Pass Applications");

        // 8. Create Staff Orders
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

        // 9. Create Deceased Officers
        foreach (array_slice($officers, 45, 2) as $officer) {
            $officer->update(['is_deceased' => true, 'deceased_date' => now()->subDays(rand(10, 100))]);
            DeceasedOfficer::create([
                'officer_id' => $officer->id,
                'reported_by' => $roleUsers['STAFF_OFFICER']->id,
                'reported_at' => now()->subDays(rand(1, 90)),
                'date_of_death' => $officer->deceased_date,
            ]);
        }
        $this->command->info("Created Deceased Officers");
    }
}
