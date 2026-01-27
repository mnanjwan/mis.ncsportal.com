<?php

namespace Database\Seeders;

use App\Models\Command;
use App\Models\PharmacyDrug;
use App\Models\PharmacyProcurement;
use App\Models\PharmacyProcurementItem;
use App\Models\PharmacyRequisition;
use App\Models\PharmacyRequisitionItem;
use App\Models\PharmacyStock;
use App\Models\PharmacyStockMovement;
use App\Models\PharmacyWorkflowStep;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💊 Starting Comprehensive Pharmacy Module Seeding...');
        
        DB::beginTransaction();
        
        try {
            // Step 1: Get or create pharmacy users
            $this->command->info('👤 Step 1: Setting up pharmacy users...');
            $users = $this->setupPharmacyUsers();
            
            // Step 2: Get commands
            $this->command->info('🏢 Step 2: Getting commands...');
            $commands = Command::where('is_active', true)->limit(5)->get();
            if ($commands->isEmpty()) {
                $this->command->warn('⚠️  No active commands found. Creating test commands...');
                $commands = collect([
                    Command::create(['name' => 'Test Command 1', 'code' => 'TEST1', 'is_active' => true]),
                    Command::create(['name' => 'Test Command 2', 'code' => 'TEST2', 'is_active' => true]),
                    Command::create(['name' => 'Test Command 3', 'code' => 'TEST3', 'is_active' => true]),
                ]);
            }
            
            // Step 3: Create comprehensive drugs
            $this->command->info('💉 Step 3: Creating drugs...');
            $drugs = $this->createDrugs();
            
            // Step 4: Create procurements in ALL states
            $this->command->info('📦 Step 4: Creating procurements in all states...');
            $procurements = $this->createProcurements($users, $drugs);
            
            // Step 5: Create comprehensive stock records (with expired, expiring soon, good)
            $this->command->info('📊 Step 5: Creating stock records (all conditions)...');
            $this->createStock($drugs, $commands, $procurements, $users);
            
            // Step 6: Create requisitions in ALL states
            $this->command->info('📋 Step 6: Creating requisitions in all states...');
            $this->createRequisitions($users, $drugs, $commands);
            
            DB::commit();
            
            $this->command->info('✅ Comprehensive Pharmacy seeding completed successfully!');
            $this->command->info('📊 Summary:');
            $this->command->info("  ✓ Created {$drugs->count()} drugs");
            $this->command->info("  ✓ Created {$procurements->count()} procurements (all states)");
            $this->command->info("  ✓ Created stock records (expired, expiring soon, good)");
            $this->command->info("  ✓ Created requisitions (all states)");
            $this->command->info("  ✓ Created stock movements (all types)");
            $this->command->info("  ✓ Created workflow steps (all states)");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error seeding pharmacy data: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
    
    private function setupPharmacyUsers(): array
    {
        $roles = [
            'Controller Procurement',
            'OC Pharmacy',
            'Central Medical Store',
            'Command Pharmacist',
        ];
        
        $users = [];
        
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                $this->command->warn("⚠️  Role '{$roleName}' not found. Skipping user creation for this role.");
                continue;
            }
            
            // Try to find existing user with this role
            $user = User::whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', $roleName);
            })->first();
            
            if (!$user) {
                $email = Str::slug($roleName, '.') . '@ncs.gov.ng';
                $user = User::create([
                    'email' => $email,
                    'password' => bcrypt('password123'),
                    'is_active' => true,
                ]);
                $user->roles()->attach($role->id);
                $this->command->info("  ✓ Created user: {$email} (password: password123)");
            } else {
                $this->command->info("  ✓ Using existing user: {$user->email}");
            }
            
            $users[$roleName] = $user;
        }
        
        // Create additional Command Pharmacist users for different commands
        $commandPharmacistRole = Role::where('name', 'Command Pharmacist')->first();
        if ($commandPharmacistRole) {
            $commands = Command::where('is_active', true)->limit(5)->get();
            foreach ($commands as $index => $command) {
                $email = 'command.pharmacist.' . ($index + 1) . '@ncs.gov.ng';
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::create([
                        'email' => $email,
                        'password' => bcrypt('password123'),
                        'is_active' => true,
                    ]);
                    // Attach role with command_id in pivot
                    $user->roles()->attach($commandPharmacistRole->id, [
                        'command_id' => $command->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                    $this->command->info("  ✓ Created Command Pharmacist: {$email} for {$command->name}");
                }
                $users['Command Pharmacist ' . ($index + 1)] = $user;
            }
        }
        
        return $users;
    }
    
    private function createDrugs()
    {
        $drugsData = [
            // Analgesics
            ['name' => 'Paracetamol 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics', 'description' => 'Pain reliever and fever reducer'],
            ['name' => 'Ibuprofen 400mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics', 'description' => 'Non-steroidal anti-inflammatory drug'],
            ['name' => 'Aspirin 100mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics', 'description' => 'Pain reliever and blood thinner'],
            ['name' => 'Tramadol 50mg', 'unit_of_measure' => 'capsules', 'category' => 'Analgesics', 'description' => 'Opioid pain medication'],
            ['name' => 'Diclofenac 50mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics', 'description' => 'NSAID for pain and inflammation'],
            
            // Antibiotics
            ['name' => 'Amoxicillin 500mg', 'unit_of_measure' => 'capsules', 'category' => 'Antibiotics', 'description' => 'Broad-spectrum antibiotic'],
            ['name' => 'Ciprofloxacin 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics', 'description' => 'Fluoroquinolone antibiotic'],
            ['name' => 'Azithromycin 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics', 'description' => 'Macrolide antibiotic'],
            ['name' => 'Ceftriaxone 1g', 'unit_of_measure' => 'vials', 'category' => 'Antibiotics', 'description' => 'Injectable cephalosporin antibiotic'],
            ['name' => 'Metronidazole 400mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics', 'description' => 'Antiprotozoal and antibacterial'],
            ['name' => 'Doxycycline 100mg', 'unit_of_measure' => 'capsules', 'category' => 'Antibiotics', 'description' => 'Tetracycline antibiotic'],
            ['name' => 'Amoxicillin-Clavulanate 625mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics', 'description' => 'Combination antibiotic'],
            
            // Antimalarials
            ['name' => 'Artemether-Lumefantrine', 'unit_of_measure' => 'tablets', 'category' => 'Antimalarials', 'description' => 'Combination antimalarial therapy'],
            ['name' => 'Quinine Sulphate 300mg', 'unit_of_measure' => 'tablets', 'category' => 'Antimalarials', 'description' => 'Antimalarial medication'],
            ['name' => 'Chloroquine 250mg', 'unit_of_measure' => 'tablets', 'category' => 'Antimalarials', 'description' => 'Antimalarial and anti-inflammatory'],
            ['name' => 'Artemether Injection 80mg', 'unit_of_measure' => 'ampoules', 'category' => 'Antimalarials', 'description' => 'Injectable antimalarial'],
            
            // Antihypertensives
            ['name' => 'Amlodipine 5mg', 'unit_of_measure' => 'tablets', 'category' => 'Antihypertensives', 'description' => 'Calcium channel blocker'],
            ['name' => 'Losartan 50mg', 'unit_of_measure' => 'tablets', 'category' => 'Antihypertensives', 'description' => 'Angiotensin II receptor blocker'],
            ['name' => 'Hydrochlorothiazide 25mg', 'unit_of_measure' => 'tablets', 'category' => 'Antihypertensives', 'description' => 'Diuretic'],
            ['name' => 'Enalapril 5mg', 'unit_of_measure' => 'tablets', 'category' => 'Antihypertensives', 'description' => 'ACE inhibitor'],
            
            // Antidiabetics
            ['name' => 'Metformin 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Antidiabetics', 'description' => 'Biguanide antidiabetic'],
            ['name' => 'Glibenclamide 5mg', 'unit_of_measure' => 'tablets', 'category' => 'Antidiabetics', 'description' => 'Sulfonylurea antidiabetic'],
            ['name' => 'Insulin Glargine', 'unit_of_measure' => 'vials', 'category' => 'Antidiabetics', 'description' => 'Long-acting insulin'],
            
            // Gastrointestinal
            ['name' => 'Omeprazole 20mg', 'unit_of_measure' => 'capsules', 'category' => 'Gastrointestinal', 'description' => 'Proton pump inhibitor'],
            ['name' => 'Ranitidine 150mg', 'unit_of_measure' => 'tablets', 'category' => 'Gastrointestinal', 'description' => 'H2 receptor antagonist'],
            ['name' => 'Metoclopramide 10mg', 'unit_of_measure' => 'tablets', 'category' => 'Gastrointestinal', 'description' => 'Antiemetic and prokinetic'],
            
            // Respiratory
            ['name' => 'Salbutamol Inhaler', 'unit_of_measure' => 'units', 'category' => 'Respiratory', 'description' => 'Bronchodilator for asthma'],
            ['name' => 'Beclomethasone Inhaler', 'unit_of_measure' => 'units', 'category' => 'Respiratory', 'description' => 'Corticosteroid inhaler'],
            
            // IV Fluids
            ['name' => 'Normal Saline 0.9%', 'unit_of_measure' => 'bottles', 'category' => 'IV Fluids', 'description' => 'Intravenous fluid'],
            ['name' => 'Dextrose 5%', 'unit_of_measure' => 'bottles', 'category' => 'IV Fluids', 'description' => 'Intravenous fluid with dextrose'],
            ['name' => 'Ringer\'s Lactate', 'unit_of_measure' => 'bottles', 'category' => 'IV Fluids', 'description' => 'Electrolyte solution'],
            
            // Emergency
            ['name' => 'Adrenaline 1mg/ml', 'unit_of_measure' => 'ampoules', 'category' => 'Emergency', 'description' => 'Emergency medication'],
            ['name' => 'Diazepam 10mg', 'unit_of_measure' => 'ampoules', 'category' => 'Emergency', 'description' => 'Sedative and anticonvulsant'],
        ];
        
        $drugs = collect();
        foreach ($drugsData as $drugData) {
            $drug = PharmacyDrug::firstOrCreate(
                ['name' => $drugData['name']],
                array_merge($drugData, ['is_active' => true])
            );
            $drugs->push($drug);
        }
        
        return $drugs;
    }
    
    private function createProcurements($users, $drugs)
    {
        $procurements = collect();
        $controllerProcurement = $users['Controller Procurement'] ?? null;
        $ocPharmacy = $users['OC Pharmacy'] ?? null;
        $centralStore = $users['Central Medical Store'] ?? null;
        
        if (!$controllerProcurement) {
            $this->command->warn('⚠️  Controller Procurement user not found. Skipping procurement creation.');
            return $procurements;
        }
        
        // Generate unique reference numbers
        $year = now()->format('Y');
        $procCount = PharmacyProcurement::whereYear('created_at', $year)->count();
        
        // 1. DRAFT procurement (not submitted)
        $procurement1 = PharmacyProcurement::create([
            'status' => 'DRAFT',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 1, 5, '0', STR_PAD_LEFT),
            'notes' => 'Initial draft for Q1 supplies',
            'created_by' => $controllerProcurement->id,
            'current_step_order' => null,
        ]);
        
        $procurement1->items()->createMany([
            ['drug_name' => 'Paracetamol 500mg', 'unit_of_measure' => 'tablets', 'quantity_requested' => 10000],
            ['drug_name' => 'Amoxicillin 500mg', 'unit_of_measure' => 'capsules', 'quantity_requested' => 5000],
        ]);
        $procurements->push($procurement1);
        
        // 2. SUBMITTED procurement (pending OC Pharmacy approval)
        $procurement2 = PharmacyProcurement::create([
            'status' => 'SUBMITTED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 2, 5, '0', STR_PAD_LEFT),
            'notes' => 'Urgent supplies needed',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(2),
            'current_step_order' => 1,
        ]);
        
        $procurement2->items()->createMany([
            ['drug_name' => 'Artemether-Lumefantrine', 'unit_of_measure' => 'tablets', 'quantity_requested' => 8000],
            ['drug_name' => 'Ceftriaxone 1g', 'unit_of_measure' => 'vials', 'quantity_requested' => 200],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement2->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_at' => null,
            'decision' => null,
        ]);
        $procurements->push($procurement2);
        
        // 3. REJECTED procurement (rejected by OC Pharmacy)
        $procurement3 = PharmacyProcurement::create([
            'status' => 'REJECTED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 3, 5, '0', STR_PAD_LEFT),
            'notes' => 'Rejected due to budget constraints',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(10),
            'current_step_order' => 1,
        ]);
        
        $procurement3->items()->createMany([
            ['drug_name' => 'Tramadol 50mg', 'unit_of_measure' => 'capsules', 'quantity_requested' => 5000],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement3->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(9),
            'decision' => 'REJECTED',
            'comment' => 'Budget constraints - reduce quantity',
        ]);
        $procurements->push($procurement3);
        
        // 4. APPROVED procurement (pending Central Medical Store receipt)
        $procurement4 = PharmacyProcurement::create([
            'status' => 'APPROVED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 4, 5, '0', STR_PAD_LEFT),
            'notes' => 'Approved for immediate procurement',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(5),
            'approved_at' => now()->subDays(3),
            'current_step_order' => 2,
        ]);
        
        $procurement4->items()->createMany([
            ['drug_name' => 'Ibuprofen 400mg', 'unit_of_measure' => 'tablets', 'quantity_requested' => 15000],
            ['drug_name' => 'Azithromycin 500mg', 'unit_of_measure' => 'tablets', 'quantity_requested' => 3000],
            ['drug_name' => 'Normal Saline 0.9%', 'unit_of_measure' => 'bottles', 'quantity_requested' => 500],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement4->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(3),
            'decision' => 'APPROVED',
            'comment' => 'Approved for procurement',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement4->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_at' => null,
            'decision' => null,
        ]);
        $procurements->push($procurement4);
        
        // 5. RECEIVED procurement (fully received)
        $drug1 = $drugs->where('name', 'Paracetamol 500mg')->first();
        $drug2 = $drugs->where('name', 'Amoxicillin 500mg')->first();
        $drug3 = $drugs->where('name', 'Ciprofloxacin 500mg')->first();
        $drug4 = $drugs->where('name', 'Artemether-Lumefantrine')->first();
        
        $procurement5 = PharmacyProcurement::create([
            'status' => 'RECEIVED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 5, 5, '0', STR_PAD_LEFT),
            'notes' => 'Fully received and stocked',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(10),
            'approved_at' => now()->subDays(8),
            'received_at' => now()->subDays(5),
            'current_step_order' => 2,
        ]);
        
        $procurement5->items()->createMany([
            [
                'pharmacy_drug_id' => $drug1?->id,
                'drug_name' => 'Paracetamol 500mg',
                'unit_of_measure' => 'tablets',
                'quantity_requested' => 20000,
                'quantity_received' => 20000,
                'expiry_date' => now()->addMonths(24),
                'batch_number' => 'BATCH-' . now()->format('Ymd') . '-001',
            ],
            [
                'pharmacy_drug_id' => $drug2?->id,
                'drug_name' => 'Amoxicillin 500mg',
                'unit_of_measure' => 'capsules',
                'quantity_requested' => 10000,
                'quantity_received' => 10000,
                'expiry_date' => now()->addMonths(18),
                'batch_number' => 'BATCH-' . now()->format('Ymd') . '-002',
            ],
            [
                'pharmacy_drug_id' => $drug3?->id,
                'drug_name' => 'Ciprofloxacin 500mg',
                'unit_of_measure' => 'tablets',
                'quantity_requested' => 5000,
                'quantity_received' => 5000,
                'expiry_date' => now()->addMonths(20),
                'batch_number' => 'BATCH-' . now()->format('Ymd') . '-003',
            ],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement5->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(8),
            'decision' => 'APPROVED',
            'comment' => 'Approved',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement5->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(5),
            'decision' => 'REVIEWED',
            'comment' => 'Received and stocked',
        ]);
        $procurements->push($procurement5);
        
        // 6. RECEIVED procurement (partially received)
        $procurement6 = PharmacyProcurement::create([
            'status' => 'RECEIVED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($procCount + 6, 5, '0', STR_PAD_LEFT),
            'notes' => 'Partially received - some items pending',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(8),
            'approved_at' => now()->subDays(6),
            'received_at' => now()->subDays(3),
            'current_step_order' => 2,
        ]);
        
        $procurement6->items()->createMany([
            [
                'pharmacy_drug_id' => $drug4?->id,
                'drug_name' => 'Artemether-Lumefantrine',
                'unit_of_measure' => 'tablets',
                'quantity_requested' => 10000,
                'quantity_received' => 7500, // Partial receipt
                'expiry_date' => now()->addMonths(22),
                'batch_number' => 'BATCH-' . now()->format('Ymd') . '-004',
            ],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement6->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(6),
            'decision' => 'APPROVED',
            'comment' => 'Approved',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $procurement6->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(3),
            'decision' => 'REVIEWED',
            'comment' => 'Partially received - balance pending',
        ]);
        $procurements->push($procurement6);
        
        return $procurements;
    }
    
    private function createStock($drugs, $commands, $procurements, $users)
    {
        $centralStoreUser = $users['Central Medical Store'] ?? $users['OC Pharmacy'] ?? null;
        
        // Create central store stock from received procurements
        $receivedProcurements = $procurements->where('status', 'RECEIVED');
        foreach ($receivedProcurements as $procurement) {
            foreach ($procurement->items as $item) {
                if ($item->pharmacy_drug_id && $item->quantity_received > 0) {
                    $stock = PharmacyStock::firstOrCreate(
                        [
                            'pharmacy_drug_id' => $item->pharmacy_drug_id,
                            'location_type' => 'CENTRAL_STORE',
                            'command_id' => null,
                            'batch_number' => $item->batch_number,
                        ],
                        [
                            'quantity' => $item->quantity_received,
                            'expiry_date' => $item->expiry_date,
                        ]
                    );
                    
                    // Create stock movement
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'movement_type' => 'PROCUREMENT_RECEIPT',
                        'reference_id' => $procurement->id,
                        'reference_type' => PharmacyProcurement::class,
                        'location_type' => 'CENTRAL_STORE',
                        'command_id' => null,
                        'quantity' => $item->quantity_received,
                        'expiry_date' => $item->expiry_date,
                        'batch_number' => $item->batch_number,
                        'notes' => 'Received from procurement ' . $procurement->reference_number,
                        'created_by' => $centralStoreUser?->id ?? 1,
                    ]);
                }
            }
        }
        
        // Create additional central store stock with various expiry conditions
        $additionalDrugs = $drugs->take(15);
        foreach ($additionalDrugs as $drug) {
            // Good stock (expires in 12+ months)
            $stock1 = PharmacyStock::firstOrCreate(
                [
                    'pharmacy_drug_id' => $drug->id,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'batch_number' => 'BATCH-GOOD-' . $drug->id,
                ],
                [
                    'quantity' => rand(5000, 50000),
                    'expiry_date' => now()->addMonths(rand(12, 24)),
                ]
            );
            
            PharmacyStockMovement::create([
                'pharmacy_drug_id' => $drug->id,
                'movement_type' => 'ADJUSTMENT',
                'reference_id' => null,
                'reference_type' => null,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => $stock1->quantity,
                'expiry_date' => $stock1->expiry_date,
                'batch_number' => $stock1->batch_number,
                'notes' => 'Initial stock entry',
                'created_by' => $centralStoreUser?->id ?? 1,
            ]);
            
            // Expiring soon stock (expires in 1-3 months)
            if (rand(0, 1)) {
                $stock2 = PharmacyStock::create([
                    'pharmacy_drug_id' => $drug->id,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'quantity' => rand(100, 2000),
                    'expiry_date' => now()->addMonths(rand(1, 3)),
                    'batch_number' => 'BATCH-EXPIRING-' . $drug->id,
                ]);
                
                PharmacyStockMovement::create([
                    'pharmacy_drug_id' => $drug->id,
                    'movement_type' => 'ADJUSTMENT',
                    'reference_id' => null,
                    'reference_type' => null,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'quantity' => $stock2->quantity,
                    'expiry_date' => $stock2->expiry_date,
                    'batch_number' => $stock2->batch_number,
                    'notes' => 'Expiring soon stock',
                    'created_by' => $centralStoreUser?->id ?? 1,
                ]);
            }
            
            // Expired stock (for testing expiry reports)
            if (rand(0, 1)) {
                $stock3 = PharmacyStock::create([
                    'pharmacy_drug_id' => $drug->id,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'quantity' => rand(50, 500),
                    'expiry_date' => now()->subMonths(rand(1, 6)),
                    'batch_number' => 'BATCH-EXPIRED-' . $drug->id,
                ]);
                
                PharmacyStockMovement::create([
                    'pharmacy_drug_id' => $drug->id,
                    'movement_type' => 'ADJUSTMENT',
                    'reference_id' => null,
                    'reference_type' => null,
                    'location_type' => 'CENTRAL_STORE',
                    'command_id' => null,
                    'quantity' => $stock3->quantity,
                    'expiry_date' => $stock3->expiry_date,
                    'batch_number' => $stock3->batch_number,
                    'notes' => 'Expired stock (for testing)',
                    'created_by' => $centralStoreUser?->id ?? 1,
                ]);
            }
        }
        
        // Create command pharmacy stock (distributed from central store)
        foreach ($commands->take(3) as $command) {
            $commandDrugs = $drugs->random(min(8, $drugs->count()));
            foreach ($commandDrugs as $drug) {
                $centralStock = PharmacyStock::where('pharmacy_drug_id', $drug->id)
                    ->where('location_type', 'CENTRAL_STORE')
                    ->where('quantity', '>', 0)
                    ->first();
                
                if ($centralStock && $centralStock->quantity > 0) {
                    $quantity = min(rand(100, 2000), $centralStock->quantity);
                    $stock = PharmacyStock::firstOrCreate(
                        [
                            'pharmacy_drug_id' => $drug->id,
                            'location_type' => 'COMMAND_PHARMACY',
                            'command_id' => $command->id,
                            'batch_number' => $centralStock->batch_number,
                        ],
                        [
                            'quantity' => $quantity,
                            'expiry_date' => $centralStock->expiry_date,
                        ]
                    );
                    
                    // Create stock movement for issue
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $drug->id,
                        'movement_type' => 'REQUISITION_ISSUE',
                        'reference_id' => null,
                        'reference_type' => null,
                        'location_type' => 'COMMAND_PHARMACY',
                        'command_id' => $command->id,
                        'quantity' => $quantity,
                        'expiry_date' => $centralStock->expiry_date,
                        'batch_number' => $centralStock->batch_number,
                        'notes' => 'Initial stock distribution to ' . $command->name,
                        'created_by' => $centralStoreUser?->id ?? 1,
                    ]);
                }
            }
        }
    }
    
    private function createRequisitions($users, $drugs, $commands)
    {
        $commandPharmacists = array_filter($users, function ($key) {
            return str_contains($key, 'Command Pharmacist');
        }, ARRAY_FILTER_USE_KEY);
        
        if (empty($commandPharmacists)) {
            $this->command->warn('⚠️  Command Pharmacist users not found. Skipping requisition creation.');
            return;
        }
        
        $ocPharmacy = $users['OC Pharmacy'] ?? null;
        $centralStore = $users['Central Medical Store'] ?? null;
        $pharmacist1 = reset($commandPharmacists);
        $command1 = $commands->first();
        
        if (!$pharmacist1 || !$command1) {
            return;
        }
        
        // Generate unique requisition reference numbers
        $year = now()->format('Y');
        $reqCount = PharmacyRequisition::whereYear('created_at', $year)->count();
        
        // 1. DRAFT requisition
        $requisition1 = PharmacyRequisition::create([
            'status' => 'DRAFT',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 1, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Draft requisition for monthly supplies',
            'created_by' => $pharmacist1->id,
            'current_step_order' => null,
        ]);
        
        $requisition1->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Paracetamol 500mg')->first()?->id, 'quantity_requested' => 500],
            ['pharmacy_drug_id' => $drugs->where('name', 'Amoxicillin 500mg')->first()?->id, 'quantity_requested' => 300],
        ]);
        
        // 2. SUBMITTED requisition (pending OC Pharmacy approval)
        $requisition2 = PharmacyRequisition::create([
            'status' => 'SUBMITTED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 2, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Urgent requisition for emergency supplies',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(1),
            'current_step_order' => 1,
        ]);
        
        $requisition2->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Artemether-Lumefantrine')->first()?->id, 'quantity_requested' => 200],
            ['pharmacy_drug_id' => $drugs->where('name', 'Ceftriaxone 1g')->first()?->id, 'quantity_requested' => 50],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition2->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_at' => null,
            'decision' => null,
        ]);
        
        // 3. REJECTED requisition (rejected by OC Pharmacy)
        $requisition3 = PharmacyRequisition::create([
            'status' => 'REJECTED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 3, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Rejected due to insufficient stock',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(8),
            'current_step_order' => 1,
        ]);
        
        $requisition3->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Tramadol 50mg')->first()?->id, 'quantity_requested' => 1000],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition3->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(7),
            'decision' => 'REJECTED',
            'comment' => 'Insufficient stock at central store',
        ]);
        
        // 4. APPROVED requisition (pending Central Medical Store issue)
        $requisition4 = PharmacyRequisition::create([
            'status' => 'APPROVED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 4, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Approved requisition',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(4),
            'approved_at' => now()->subDays(2),
            'current_step_order' => 2,
        ]);
        
        $requisition4->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Ibuprofen 400mg')->first()?->id, 'quantity_requested' => 1000],
            ['pharmacy_drug_id' => $drugs->where('name', 'Azithromycin 500mg')->first()?->id, 'quantity_requested' => 500],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition4->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(2),
            'decision' => 'APPROVED',
            'comment' => 'Approved for issue',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition4->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_at' => null,
            'decision' => null,
        ]);
        
        // 5. ISSUED requisition (pending Command Pharmacist dispense)
        $requisition5 = PharmacyRequisition::create([
            'status' => 'ISSUED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 5, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Issued from central store',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(7),
            'approved_at' => now()->subDays(5),
            'issued_at' => now()->subDays(3),
            'current_step_order' => 3,
        ]);
        
        $item1 = $requisition5->items()->create([
            'pharmacy_drug_id' => $drugs->where('name', 'Paracetamol 500mg')->first()?->id,
            'quantity_requested' => 800,
            'quantity_issued' => 800,
        ]);
        
        $item2 = $requisition5->items()->create([
            'pharmacy_drug_id' => $drugs->where('name', 'Amoxicillin 500mg')->first()?->id,
            'quantity_requested' => 400,
            'quantity_issued' => 400,
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition5->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(5),
            'decision' => 'APPROVED',
            'comment' => 'Approved',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition5->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(3),
            'decision' => 'REVIEWED',
            'comment' => 'Issued to command',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition5->id,
            'step_order' => 3,
            'role_name' => 'Command Pharmacist',
            'action' => 'REVIEW',
            'acted_at' => null,
            'decision' => null,
        ]);
        
        // 6. ISSUED requisition (partially issued)
        $requisition6 = PharmacyRequisition::create([
            'status' => 'ISSUED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 6, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Partially issued - some items out of stock',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(6),
            'approved_at' => now()->subDays(4),
            'issued_at' => now()->subDays(2),
            'current_step_order' => 3,
        ]);
        
        $requisition6->items()->createMany([
            [
                'pharmacy_drug_id' => $drugs->where('name', 'Ciprofloxacin 500mg')->first()?->id,
                'quantity_requested' => 600,
                'quantity_issued' => 400, // Partial issue
            ],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition6->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(4),
            'decision' => 'APPROVED',
            'comment' => 'Approved',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition6->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(2),
            'decision' => 'REVIEWED',
            'comment' => 'Partially issued - balance out of stock',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition6->id,
            'step_order' => 3,
            'role_name' => 'Command Pharmacist',
            'action' => 'REVIEW',
            'acted_at' => null,
            'decision' => null,
        ]);
        
        // 7. DISPENSED requisition (completed)
        $requisition7 = PharmacyRequisition::create([
            'status' => 'DISPENSED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($reqCount + 7, 5, '0', STR_PAD_LEFT),
            'command_id' => $command1->id,
            'notes' => 'Dispensed to patients',
            'created_by' => $pharmacist1->id,
            'submitted_at' => now()->subDays(10),
            'approved_at' => now()->subDays(8),
            'issued_at' => now()->subDays(6),
            'dispensed_at' => now()->subDays(4),
            'current_step_order' => 3,
        ]);
        
        $requisition7->items()->createMany([
            [
                'pharmacy_drug_id' => $drugs->where('name', 'Ciprofloxacin 500mg')->first()?->id,
                'quantity_requested' => 600,
                'quantity_issued' => 600,
            ],
            [
                'pharmacy_drug_id' => $drugs->where('name', 'Metronidazole 400mg')->first()?->id,
                'quantity_requested' => 300,
                'quantity_issued' => 300,
            ],
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition7->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(8),
            'decision' => 'APPROVED',
            'comment' => 'Approved',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition7->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(6),
            'decision' => 'REVIEWED',
            'comment' => 'Issued',
        ]);
        
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $requisition7->id,
            'step_order' => 3,
            'role_name' => 'Command Pharmacist',
            'action' => 'REVIEW',
            'acted_by_user_id' => $pharmacist1->id,
            'acted_at' => now()->subDays(4),
            'decision' => 'REVIEWED',
            'comment' => 'Dispensed',
        ]);
        
        // Create stock movements for issued requisitions
        foreach ([$requisition5, $requisition6, $requisition7] as $req) {
            foreach ($req->items as $item) {
                if ($item->quantity_issued > 0) {
                    PharmacyStockMovement::create([
                        'pharmacy_drug_id' => $item->pharmacy_drug_id,
                        'movement_type' => 'REQUISITION_ISSUE',
                        'reference_id' => $req->id,
                        'reference_type' => PharmacyRequisition::class,
                        'location_type' => 'COMMAND_PHARMACY',
                        'command_id' => $req->command_id,
                        'quantity' => $item->quantity_issued,
                        'expiry_date' => null,
                        'batch_number' => null,
                        'notes' => 'Issued from requisition ' . $req->reference_number,
                        'created_by' => $centralStore?->id ?? 1,
                    ]);
                }
            }
        }
        
        // Create dispensed movements for dispensed requisition
        foreach ($requisition7->items as $item) {
            PharmacyStockMovement::create([
                'pharmacy_drug_id' => $item->pharmacy_drug_id,
                'movement_type' => 'DISPENSED',
                'reference_id' => $requisition7->id,
                'reference_type' => PharmacyRequisition::class,
                'location_type' => 'COMMAND_PHARMACY',
                'command_id' => $requisition7->command_id,
                'quantity' => -$item->quantity_issued, // Negative for dispensed
                'expiry_date' => null,
                'batch_number' => null,
                'notes' => 'Dispensed to patients from requisition ' . $requisition7->reference_number,
                'created_by' => $pharmacist1->id,
            ]);
        }
    }
}
