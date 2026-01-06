<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\Officer;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManningRequestTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 5 officers for each rank in 5 selected commands
     * This ensures proper testing of manning request matching
     */
    public function run(): void
    {
        $this->command->info("🚀 Starting Manning Request Test Data Seeding...");
        $this->command->info("=" . str_repeat("=", 60));

        // Ensure zones and commands exist
        $this->command->info("\n📋 Step 1: Ensuring zones and commands exist...");
        $this->call(ZoneAndCommandSeeder::class);
        
        // Limit to 5 commands for faster seeding
        $commands = Command::where('is_active', true)
            ->orderBy('id')
            ->take(5)
            ->get();
        $this->command->info("   ✓ Selected 5 commands for seeding:");
        foreach ($commands as $cmd) {
            $this->command->info("      - {$cmd->name} (ID: {$cmd->id})");
        }

        // Get HRD user for created_by
        $hrdUser = User::whereHas('roles', function($q) {
            $q->where('name', 'HRD');
        })->first();

        if (!$hrdUser) {
            $this->command->warn("   ⚠️  HRD user not found. Creating one...");
            $hrdUser = User::firstOrCreate(
                ['email' => 'hrd@ncs.gov.ng'],
                [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now()
                ]
            );
        }

        // Define all ranks
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

        // Sample data for officers
        $firstNames = ['John', 'Mary', 'James', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        $initials = ['A.B.', 'C.D.', 'E.F.', 'G.H.', 'I.J.', 'K.L.', 'M.N.', 'O.P.', 'Q.R.', 'S.T.'];
        $sexes = ['M', 'F'];
        $states = ['Lagos', 'Abuja', 'Kano', 'Rivers', 'Ogun', 'Kaduna', 'Delta', 'Oyo'];
        $lgas = ['Ikeja', 'Victoria Island', 'Surulere', 'Yaba', 'Lekki', 'Ajah'];
        $zones = ['North Central', 'North East', 'North West', 'South East', 'South South', 'South West'];
        $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];
        $qualifications = ['B.Sc', 'B.A', 'B.Eng', 'M.Sc', 'M.A', 'HND', 'ND', 'NCE'];
        $banks = ['First Bank', 'GTBank', 'Access Bank', 'Zenith Bank', 'UBA', 'Union Bank'];
        $pfas = ['PENCOM', 'NLPC', 'ARM Pension', 'Stanbic IBTC Pension'];

        $totalOfficers = 0;
        $officerCounter = 1;

        $totalToCreate = $commands->count() * count($ranks) * 5;
        $this->command->info("\n📋 Step 2: Creating 5 officers per rank per command...");
        $this->command->info("   Total to create: {$totalToCreate} officers");
        $this->command->info("   Commands: {$commands->count()}");
        $this->command->info("   Ranks: " . count($ranks));
        $this->command->info("   Officers per rank per command: 5\n");

        $progressCounter = 0;
        
        foreach ($commands as $commandIndex => $command) {
            $this->command->info("   Processing command " . ($commandIndex + 1) . "/{$commands->count()}: {$command->name}");
            
            foreach ($ranks as $rank) {
                for ($i = 1; $i <= 5; $i++) {
                    // Generate unique service number (just the number part, mutator adds NCS prefix)
                    $serviceNumberBase = str_pad($officerCounter, 5, '0', STR_PAD_LEFT);
                    $fullServiceNumber = 'NCS' . $serviceNumberBase;
                    
                    // Check if service number already exists
                    while (Officer::where('service_number', $fullServiceNumber)->exists()) {
                        $officerCounter++;
                        $serviceNumberBase = str_pad($officerCounter, 5, '0', STR_PAD_LEFT);
                        $fullServiceNumber = 'NCS' . $serviceNumberBase;
                    }

                    // Generate names
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $initial = $initials[array_rand($initials)];
                    $sex = $sexes[array_rand($sexes)];

                    // Generate email
                    $email = strtolower($firstName . '.' . $lastName . '.' . $officerCounter . '@ncs.gov.ng');
                    
                    // Ensure unique email
                    while (User::where('email', $email)->exists()) {
                        $officerCounter++;
                        $email = strtolower($firstName . '.' . $lastName . '.' . $officerCounter . '@ncs.gov.ng');
                    }

                    // Create user first
                    $user = User::create([
                        'email' => $email,
                        'password' => Hash::make('password123'),
                        'is_active' => true,
                        'created_by' => $hrdUser->id,
                    ]);

                    // Calculate dates
                    $dateOfBirth = Carbon::now()->subYears(rand(25, 55))->subDays(rand(0, 365));
                    $dateOfFirstAppointment = $dateOfBirth->copy()->addYears(rand(22, 30));
                    $dateOfPresentAppointment = $dateOfFirstAppointment->copy()->addYears(rand(1, 15));
                    $datePostedToStation = Carbon::now()->subMonths(rand(1, 60));

                    // Create officer
                    $officer = Officer::create([
                        'user_id' => $user->id,
                        'service_number' => $serviceNumberBase, // Mutator will add NCS prefix
                        'appointment_number' => 'APT' . str_pad($officerCounter, 6, '0', STR_PAD_LEFT),
                        'initials' => $initial,
                        'surname' => $lastName,
                        'sex' => $sex,
                        'date_of_birth' => $dateOfBirth,
                        'date_of_first_appointment' => $dateOfFirstAppointment,
                        'date_of_present_appointment' => $dateOfPresentAppointment,
                        'substantive_rank' => $rank,
                        'salary_grade_level' => $this->getGradeLevelForRank($rank),
                        'state_of_origin' => $states[array_rand($states)],
                        'lga' => $lgas[array_rand($lgas)],
                        'geopolitical_zone' => $zones[array_rand($zones)],
                        'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                        'entry_qualification' => $qualifications[array_rand($qualifications)],
                        'discipline' => 'General',
                        'additional_qualification' => rand(0, 1) ? $qualifications[array_rand($qualifications)] : null,
                        'present_station' => $command->id,
                        'date_posted_to_station' => $datePostedToStation,
                        'residential_address' => rand(1, 100) . ' Street, ' . $command->location ?? 'Lagos',
                        'permanent_home_address' => rand(1, 100) . ' Home Street, ' . $states[array_rand($states)],
                        'phone_number' => '080' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                        'email' => $email,
                        'personal_email' => strtolower($firstName . '.' . $lastName . $officerCounter . '@gmail.com'),
                        'customs_email' => $email,
                        'email_status' => 'VERIFIED',
                        'bank_name' => $banks[array_rand($banks)],
                        'bank_account_number' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                        'sort_code' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                        'pfa_name' => $pfas[array_rand($pfas)],
                        'rsa_number' => 'PEN' . str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT),
                        'interdicted' => false,
                        'suspended' => false,
                        'ongoing_investigation' => false,
                        'dismissed' => false,
                        'quartered' => false,
                        'is_deceased' => false,
                        'is_active' => true,
                        'onboarding_status' => 'completed',
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                        'onboarding_completed_at' => now(),
                        'created_by' => $hrdUser->id,
                    ]);

                    // Create posting record (using model for consistency)
                    \App\Models\OfficerPosting::create([
                        'officer_id' => $officer->id,
                        'command_id' => $command->id,
                        'posting_date' => $datePostedToStation,
                        'is_current' => true,
                        'documented_by' => $hrdUser->id,
                        'documented_at' => $datePostedToStation,
                    ]);

                    $totalOfficers++;
                    $officerCounter++;
                    $progressCounter++;
                }
            }
            
            $this->command->info("      ✓ Completed {$command->name}: {$totalOfficers} officers created so far");
        }

        $this->command->info("\n" . str_repeat("=", 60));
        $this->command->info("✅ Manning Request Test Data Seeding Complete!");
        $this->command->info("   Created: {$totalOfficers} officers");
        $this->command->info("   Distribution: 5 officers per rank per command");
        $this->command->info("   Commands: {$commands->count()}");
        $this->command->info("   Ranks: " . count($ranks));
        $this->command->info(str_repeat("=", 60));
        $this->command->info("\n📝 All officers have:");
        $this->command->info("   ✓ User accounts (email: firstname.lastname.number@ncs.gov.ng, password: password123)");
        $this->command->info("   ✓ Complete officer profiles");
        $this->command->info("   ✓ Posting records");
        $this->command->info("   ✓ Active status");
        $this->command->info("\n🎯 You can now test 'Find Matches' functionality!");
        $this->command->info("   Create a manning request from any command for any rank");
        $this->command->info("   and it should find officers from other commands.\n");
    }

    /**
     * Get grade level for rank
     */
    private function getGradeLevelForRank(string $rank): string
    {
        $gradeLevels = [
            'CGC' => 'GL18',
            'DCG' => 'GL17',
            'ACG' => 'GL16',
            'CC' => 'GL15',
            'DC' => 'GL14',
            'AC' => 'GL13',
            'CSC' => 'GL12',
            'SC' => 'GL11',
            'DSC' => 'GL10',
            'ASC I' => 'GL09',
            'ASC II' => 'GL08',
            'IC' => 'GL07',
            'AIC' => 'GL06',
            'CA I' => 'GL05',
            'CA II' => 'GL04',
            'CA III' => 'GL03',
        ];

        return $gradeLevels[$rank] ?? 'GL05';
    }
}

