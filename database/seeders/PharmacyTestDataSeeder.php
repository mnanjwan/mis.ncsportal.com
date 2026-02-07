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
use Illuminate\Support\Facades\Hash;

class PharmacyTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💊 Starting Pharmacy Test Data Seeding...');
        
        DB::beginTransaction();
        
        try {
            // Step 1: Create test users with known credentials
            $this->command->info('👤 Step 1: Creating test users...');
            $users = $this->createTestUsers();
            
            // Step 2: Get or create commands
            $this->command->info('🏢 Step 2: Setting up commands...');
            $commands = $this->setupCommands();
            
            // Step 3: Create drugs
            $this->command->info('💉 Step 3: Creating drugs...');
            $drugs = $this->createDrugs();
            
            // Step 4: Create stock with ALL expiry scenarios
            $this->command->info('📊 Step 4: Creating stock with all expiry conditions...');
            $this->createStockWithExpiryScenarios($drugs, $commands, $users);
            
            // Step 5: Create procurements in all states
            $this->command->info('📦 Step 5: Creating procurements...');
            $this->createProcurements($users, $drugs);
            
            // Step 6: Create requisitions in all states
            $this->command->info('📋 Step 6: Creating requisitions...');
            $this->createRequisitions($users, $drugs, $commands);
            
            DB::commit();
            
            $this->command->info('✅ Pharmacy Test Data seeding completed!');
            $this->command->info('');
            $this->command->info('🔑 LOGIN CREDENTIALS:');
            $this->command->info('═══════════════════════════════════════════════════════');
            $this->command->info('Controller Procurement:');
            $this->command->info('  Email: pharmacy.procurement@ncs.gov.ng');
            $this->command->info('  Password: password');
            $this->command->info('');
            $this->command->info('OC Pharmacy:');
            $this->command->info('  Email: pharmacy.oc@ncs.gov.ng');
            $this->command->info('  Password: password');
            $this->command->info('');
            $this->command->info('Central Medical Store:');
            $this->command->info('  Email: pharmacy.store@ncs.gov.ng');
            $this->command->info('  Password: password');
            $this->command->info('');
            $this->command->info('Command Pharmacist:');
            $this->command->info('  Email: pharmacy.command1@ncs.gov.ng');
            $this->command->info('  Password: password');
            $this->command->info('═══════════════════════════════════════════════════════');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function createTestUsers(): array
    {
        $users = [];
        
        // Controller Procurement
        $role = Role::where('name', 'Controller Procurement')->first();
        if ($role) {
            $user = User::firstOrCreate(
                ['email' => 'pharmacy.procurement@ncs.gov.ng'],
                [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $users['Controller Procurement'] = $user;
        }
        
        // OC Pharmacy
        $role = Role::where('name', 'OC Pharmacy')->first();
        if ($role) {
            $user = User::firstOrCreate(
                ['email' => 'pharmacy.oc@ncs.gov.ng'],
                [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $users['OC Pharmacy'] = $user;
        }
        
        // Central Medical Store
        $role = Role::where('name', 'Central Medical Store')->first();
        if ($role) {
            $user = User::firstOrCreate(
                ['email' => 'pharmacy.store@ncs.gov.ng'],
                [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id, ['is_active' => true, 'assigned_at' => now()]);
            }
            $users['Central Medical Store'] = $user;
        }
        
        // Command Pharmacist (for first command)
        $role = Role::where('name', 'Command Pharmacist')->first();
        $command = Command::where('is_active', true)->first();
        if ($role && $command) {
            $user = User::firstOrCreate(
                ['email' => 'pharmacy.command1@ncs.gov.ng'],
                [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            if (!$user->roles()->where('roles.id', $role->id)->wherePivot('command_id', $command->id)->exists()) {
                $user->roles()->attach($role->id, [
                    'command_id' => $command->id,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]);
            }
            $users['Command Pharmacist'] = $user;
            $users['Command Pharmacist Command'] = $command;
        }
        
        return $users;
    }
    
    private function setupCommands(): \Illuminate\Support\Collection
    {
        $commands = Command::where('is_active', true)->limit(3)->get();
        if ($commands->isEmpty()) {
            $commands = collect([
                Command::create(['name' => 'Test Command 1', 'code' => 'TEST1', 'is_active' => true]),
                Command::create(['name' => 'Test Command 2', 'code' => 'TEST2', 'is_active' => true]),
                Command::create(['name' => 'Test Command 3', 'code' => 'TEST3', 'is_active' => true]),
            ]);
        }
        return $commands;
    }
    
    private function createDrugs(): \Illuminate\Support\Collection
    {
        $drugsData = [
            ['name' => 'Paracetamol 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics'],
            ['name' => 'Amoxicillin 500mg', 'unit_of_measure' => 'capsules', 'category' => 'Antibiotics'],
            ['name' => 'Ibuprofen 400mg', 'unit_of_measure' => 'tablets', 'category' => 'Analgesics'],
            ['name' => 'Artemether-Lumefantrine', 'unit_of_measure' => 'tablets', 'category' => 'Antimalarials'],
            ['name' => 'Ceftriaxone 1g', 'unit_of_measure' => 'vials', 'category' => 'Antibiotics'],
            ['name' => 'Ciprofloxacin 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics'],
            ['name' => 'Azithromycin 500mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics'],
            ['name' => 'Normal Saline 0.9%', 'unit_of_measure' => 'bottles', 'category' => 'IV Fluids'],
            ['name' => 'Metronidazole 400mg', 'unit_of_measure' => 'tablets', 'category' => 'Antibiotics'],
            ['name' => 'Tramadol 50mg', 'unit_of_measure' => 'capsules', 'category' => 'Analgesics'],
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
    
    private function createStockWithExpiryScenarios($drugs, $commands, $users)
    {
        $centralStoreUser = $users['Central Medical Store'] ?? $users['OC Pharmacy'] ?? null;
        $command = $commands->first();
        
        foreach ($drugs->take(8) as $index => $drug) {
            // 1. EXPIRED stock (past expiry date) - RED
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(50, 500),
                'expiry_date' => now()->subDays(rand(10, 90)),
                'batch_number' => 'EXPIRED-' . $drug->id . '-001',
            ]);
            
            // 2. CRITICAL stock (≤30 days) - RED
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(100, 2000),
                'expiry_date' => now()->addDays(rand(5, 30)),
                'batch_number' => 'CRITICAL-' . $drug->id . '-001',
            ]);
            
            // 3. WARNING stock (31-60 days) - YELLOW
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(500, 3000),
                'expiry_date' => now()->addDays(rand(31, 60)),
                'batch_number' => 'WARNING-' . $drug->id . '-001',
            ]);
            
            // 4. CAUTION stock (61-90 days) - BLUE
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(1000, 5000),
                'expiry_date' => now()->addDays(rand(61, 90)),
                'batch_number' => 'CAUTION-' . $drug->id . '-001',
            ]);
            
            // 5. OK stock (>90 days) - GREEN
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(5000, 20000),
                'expiry_date' => now()->addMonths(rand(12, 24)),
                'batch_number' => 'OK-' . $drug->id . '-001',
            ]);
            
            // 6. LOW STOCK (quantity < 10) - RED badge
            PharmacyStock::create([
                'pharmacy_drug_id' => $drug->id,
                'location_type' => 'CENTRAL_STORE',
                'command_id' => null,
                'quantity' => rand(1, 9),
                'expiry_date' => now()->addMonths(rand(6, 12)),
                'batch_number' => 'LOW-' . $drug->id . '-001',
            ]);
            
            // Command Pharmacy stock (for testing requisition flow)
            if ($command && $index < 5) {
                PharmacyStock::create([
                    'pharmacy_drug_id' => $drug->id,
                    'location_type' => 'COMMAND_PHARMACY',
                    'command_id' => $command->id,
                    'quantity' => rand(100, 1000),
                    'expiry_date' => now()->addMonths(rand(6, 18)),
                    'batch_number' => 'CMD-' . $command->id . '-' . $drug->id . '-001',
                ]);
            }
        }
    }
    
    private function createProcurements($users, $drugs)
    {
        $controllerProcurement = $users['Controller Procurement'] ?? null;
        $ocPharmacy = $users['OC Pharmacy'] ?? null;
        $centralStore = $users['Central Medical Store'] ?? null;
        
        if (!$controllerProcurement) return;
        
        $year = now()->format('Y');
        $maxRef = PharmacyProcurement::whereYear('created_at', $year)
            ->where('reference_number', 'like', 'PROC-' . $year . '-%')
            ->max('reference_number');
        $nextNum = $maxRef ? ((int) substr($maxRef, -5)) + 1 : 1;
        
        // 1. DRAFT procurement
        $proc1 = PharmacyProcurement::create([
            'status' => 'DRAFT',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT),
            'notes' => 'Test draft procurement',
            'created_by' => $controllerProcurement->id,
        ]);
        $proc1->items()->createMany([
            ['drug_name' => 'Paracetamol 500mg', 'unit_of_measure' => 'tablets', 'quantity_requested' => 10000],
            ['drug_name' => 'Amoxicillin 500mg', 'unit_of_measure' => 'capsules', 'quantity_requested' => 5000],
        ]);
        
        // 2. SUBMITTED procurement (pending approval)
        $proc2 = PharmacyProcurement::create([
            'status' => 'SUBMITTED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($nextNum + 1, 5, '0', STR_PAD_LEFT),
            'notes' => 'Test submitted procurement',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(1),
            'current_step_order' => 1,
        ]);
        $proc2->items()->createMany([
            ['drug_name' => 'Ibuprofen 400mg', 'unit_of_measure' => 'tablets', 'quantity_requested' => 8000],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $proc2->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
        ]);
        
        // 3. APPROVED procurement (pending receipt)
        $proc3 = PharmacyProcurement::create([
            'status' => 'APPROVED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($nextNum + 2, 5, '0', STR_PAD_LEFT),
            'notes' => 'Test approved procurement',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(1),
            'current_step_order' => 2,
        ]);
        $proc3->items()->createMany([
            ['drug_name' => 'Artemether-Lumefantrine', 'unit_of_measure' => 'tablets', 'quantity_requested' => 5000],
            ['drug_name' => 'Ceftriaxone 1g', 'unit_of_measure' => 'vials', 'quantity_requested' => 200],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $proc3->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(1),
            'decision' => 'APPROVED',
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $proc3->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
        ]);
        
        // 4. RECEIVED procurement
        $drug1 = $drugs->where('name', 'Paracetamol 500mg')->first();
        $drug2 = $drugs->where('name', 'Amoxicillin 500mg')->first();
        $proc4 = PharmacyProcurement::create([
            'status' => 'RECEIVED',
            'reference_number' => 'PROC-' . $year . '-' . str_pad($nextNum + 3, 5, '0', STR_PAD_LEFT),
            'notes' => 'Test received procurement',
            'created_by' => $controllerProcurement->id,
            'submitted_at' => now()->subDays(10),
            'approved_at' => now()->subDays(8),
            'received_at' => now()->subDays(5),
            'current_step_order' => 2,
        ]);
        $proc4->items()->createMany([
            [
                'pharmacy_drug_id' => $drug1?->id,
                'drug_name' => 'Paracetamol 500mg',
                'unit_of_measure' => 'tablets',
                'quantity_requested' => 20000,
                'quantity_received' => 20000,
                'expiry_date' => now()->addMonths(24),
                'batch_number' => 'BATCH-REC-' . now()->format('Ymd') . '-001',
            ],
            [
                'pharmacy_drug_id' => $drug2?->id,
                'drug_name' => 'Amoxicillin 500mg',
                'unit_of_measure' => 'capsules',
                'quantity_requested' => 10000,
                'quantity_received' => 10000,
                'expiry_date' => now()->addMonths(18),
                'batch_number' => 'BATCH-REC-' . now()->format('Ymd') . '-002',
            ],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $proc4->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(8),
            'decision' => 'APPROVED',
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_procurement_id' => $proc4->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(5),
            'decision' => 'REVIEWED',
        ]);
        
        // Create stock from received procurement
        foreach ($proc4->items as $item) {
            if ($item->pharmacy_drug_id && $item->quantity_received > 0) {
                PharmacyStock::firstOrCreate(
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
            }
        }
    }
    
    private function createRequisitions($users, $drugs, $commands)
    {
        $commandPharmacist = $users['Command Pharmacist'] ?? null;
        $command = $users['Command Pharmacist Command'] ?? $commands->first();
        $ocPharmacy = $users['OC Pharmacy'] ?? null;
        $centralStore = $users['Central Medical Store'] ?? null;
        
        if (!$commandPharmacist || !$command) return;
        
        $year = now()->format('Y');
        $maxRef = PharmacyRequisition::whereYear('created_at', $year)
            ->where('reference_number', 'like', 'REQ-' . $year . '-%')
            ->max('reference_number');
        $nextNum = $maxRef ? ((int) substr($maxRef, -5)) + 1 : 1;
        
        // 1. DRAFT requisition
        $req1 = PharmacyRequisition::create([
            'status' => 'DRAFT',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT),
            'command_id' => $command->id,
            'notes' => 'Test draft requisition',
            'created_by' => $commandPharmacist->id,
        ]);
        $req1->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Paracetamol 500mg')->first()?->id, 'quantity_requested' => 500],
        ]);
        
        // 2. SUBMITTED requisition (pending approval)
        $req2 = PharmacyRequisition::create([
            'status' => 'SUBMITTED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($nextNum + 1, 5, '0', STR_PAD_LEFT),
            'command_id' => $command->id,
            'notes' => 'Test submitted requisition',
            'created_by' => $commandPharmacist->id,
            'submitted_at' => now()->subDays(1),
            'current_step_order' => 1,
        ]);
        $req2->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Ibuprofen 400mg')->first()?->id, 'quantity_requested' => 1000],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $req2->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
        ]);
        
        // 3. APPROVED requisition (pending issue)
        $req3 = PharmacyRequisition::create([
            'status' => 'APPROVED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($nextNum + 2, 5, '0', STR_PAD_LEFT),
            'command_id' => $command->id,
            'notes' => 'Test approved requisition',
            'created_by' => $commandPharmacist->id,
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(1),
            'current_step_order' => 2,
        ]);
        $req3->items()->createMany([
            ['pharmacy_drug_id' => $drugs->where('name', 'Artemether-Lumefantrine')->first()?->id, 'quantity_requested' => 200],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $req3->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(1),
            'decision' => 'APPROVED',
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $req3->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
        ]);
        
        // 4. ISSUED requisition (pending dispense)
        $req4 = PharmacyRequisition::create([
            'status' => 'ISSUED',
            'reference_number' => 'REQ-' . $year . '-' . str_pad($nextNum + 3, 5, '0', STR_PAD_LEFT),
            'command_id' => $command->id,
            'notes' => 'Test issued requisition',
            'created_by' => $commandPharmacist->id,
            'submitted_at' => now()->subDays(5),
            'approved_at' => now()->subDays(3),
            'issued_at' => now()->subDays(1),
        ]);
        $req4->items()->createMany([
            [
                'pharmacy_drug_id' => $drugs->where('name', 'Paracetamol 500mg')->first()?->id,
                'quantity_requested' => 800,
                'quantity_issued' => 800,
            ],
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $req4->id,
            'step_order' => 1,
            'role_name' => 'OC Pharmacy',
            'action' => 'APPROVE',
            'acted_by_user_id' => $ocPharmacy?->id,
            'acted_at' => now()->subDays(3),
            'decision' => 'APPROVED',
        ]);
        PharmacyWorkflowStep::create([
            'pharmacy_requisition_id' => $req4->id,
            'step_order' => 2,
            'role_name' => 'Central Medical Store',
            'action' => 'REVIEW',
            'acted_by_user_id' => $centralStore?->id,
            'acted_at' => now()->subDays(1),
            'decision' => 'REVIEWED',
        ]);
    }
}
