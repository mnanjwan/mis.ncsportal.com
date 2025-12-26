<?php

namespace Database\Seeders;

use App\Models\TrainingResult;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainingResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Updating existing training results with proper ranks...');
        
        // Fix existing training results that have numeric ranks
        $resultsToFix = TrainingResult::whereNotNull('officer_id')
            ->whereHas('officer', function($query) {
                $query->whereNotNull('substantive_rank');
            })
            ->get();
        
        $fixed = 0;
        foreach ($resultsToFix as $result) {
            if ($result->officer && $result->officer->substantive_rank) {
                // Update rank to use officer's substantive rank
                $result->update([
                    'rank' => $result->officer->substantive_rank
                ]);
                $fixed++;
            }
        }
        
        $this->command->info("✅ Fixed {$fixed} training results with proper ranks");
        
        // Also fix results without officer_id but with appointment_number
        $resultsByAppointment = TrainingResult::whereNull('officer_id')
            ->whereNotNull('appointment_number')
            ->get();
        
        $fixedByAppointment = 0;
        foreach ($resultsByAppointment as $result) {
            $officer = Officer::where('appointment_number', $result->appointment_number)->first();
            if ($officer && $officer->substantive_rank) {
                $result->update([
                    'rank' => $officer->substantive_rank,
                    'officer_id' => $officer->id
                ]);
                $fixedByAppointment++;
            }
        }
        
        $this->command->info("✅ Fixed {$fixedByAppointment} training results by appointment number");
        
        // Delete training results that still have numeric ranks (orphaned data)
        $numericRanks = TrainingResult::whereRaw('CAST(rank AS UNSIGNED) > 0')
            ->whereNotIn('rank', ['CA III', 'CA II', 'CA I', 'AIC', 'IC', 'ASC II', 'ASC I', 'DSC', 'SC', 'CSC', 'AC', 'DC', 'CC', 'ACG', 'DCG', 'CGC'])
            ->delete();
        
        if ($numericRanks > 0) {
            $this->command->info("🗑️  Deleted {$numericRanks} training results with invalid numeric ranks");
        }
        
        // Get new recruits (officers with appointment numbers but no service numbers)
        $newRecruits = Officer::whereNotNull('appointment_number')
            ->whereNull('service_number')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->get();
        
        if ($newRecruits->isEmpty()) {
            $this->command->warn('⚠️  No new recruits found. Please create recruits with appointment numbers first.');
            return;
        }
        
        $this->command->info("📊 Found {$newRecruits->count()} new recruits");
        
        // Get TRADOC user for uploaded_by
        $tradocUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'TRADOC')->where('is_active', true);
        })->where('is_active', true)->first();
        
        if (!$tradocUser) {
            $this->command->warn('⚠️  No TRADOC user found. Creating training results without uploaded_by.');
        }
        
        // Commission ranks in order
        $ranks = [
            'CA III', 'CA II', 'CA I', 'AIC', 'IC', 'ASC II', 'ASC I', 'DSC', 'SC', 'CSC', 
            'AC', 'DC', 'CC', 'ACG', 'DCG', 'CGC'
        ];
        
        $trainingResults = [];
        $rankGroups = [];
        
        // Group recruits by their substantive rank
        foreach ($newRecruits as $recruit) {
            $rank = $recruit->substantive_rank ?? 'Unknown';
            if (!isset($rankGroups[$rank])) {
                $rankGroups[$rank] = [];
            }
            $rankGroups[$rank][] = $recruit;
        }
        
        $this->command->info('📝 Creating training results...');
        
        // Create training results grouped by rank
        foreach ($rankGroups as $rank => $recruits) {
            foreach ($recruits as $index => $recruit) {
                // Generate training score (0-100)
                $trainingScore = rand(40, 100);
                
                // Auto-determine status: >= 50 = PASS, < 50 = FAIL
                $status = $trainingScore >= 50 ? 'PASS' : 'FAIL';
                
                // Create officer name
                $officerName = trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
                
                $trainingResults[] = [
                    'appointment_number' => $recruit->appointment_number,
                    'officer_id' => $recruit->id,
                    'officer_name' => $officerName,
                    'training_score' => $trainingScore,
                    'status' => $status,
                    'rank' => $rank, // Store the officer's substantive rank
                    'service_number' => null, // Will be assigned after training
                    'uploaded_by' => $tradocUser->id ?? null,
                    'uploaded_at' => now()->subDays(rand(1, 30)),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }
            
            $this->command->info("  Created training results for {$rank}: " . count($recruits) . " recruits");
        }
        
        // Insert all training results
        if (!empty($trainingResults)) {
            // Insert in chunks to avoid memory issues
            $chunks = array_chunk($trainingResults, 100);
            foreach ($chunks as $chunk) {
                TrainingResult::insert($chunk);
            }
            
            $this->command->info("✅ Created " . count($trainingResults) . " training results");
            
            // Summary by rank
            $this->command->info('');
            $this->command->info('📊 Summary by Rank:');
            foreach ($rankGroups as $rank => $recruits) {
                $count = count($recruits);
                $this->command->info("  • {$rank}: {$count} result(s)");
            }
        } else {
            $this->command->warn('⚠️  No training results created.');
        }
    }
}

