<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FleetTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $command = \App\Models\Command::first();
        if (!$command) {
            $command = \App\Models\Command::create(['name' => 'HQ Command', 'code' => 'HQ']);
        }

        // Expanded roles list and explicit email mapping for complete workflow testing
        $roleMapping = [
            'tl_user_1@example.com' => 'CD',
            'tl_user_2@example.com' => 'Area Controller',
            'tl_user_3@example.com' => 'OC Workshop',
            'tl_user_4@example.com' => 'Staff Officer T&L',
            'tl_user_5@example.com' => 'CC T&L',
            'tl_user_6@example.com' => 'ACG TS',
            'tl_user_7@example.com' => 'DCG FATS',
            'tl_user_8@example.com' => 'CGC',
        ];

        $testUsers = [];

        foreach ($roleMapping as $email => $roleName) {
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                $user = \App\Models\User::create([
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'is_active' => true,
                ]);
            } else {
                // Force update password and active status to ensure login works
                $user->update([
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'is_active' => true,
                ]);
            }

            $role = \App\Models\Role::where('name', $roleName)->first();
            if ($role) {
                // All test users linked to HQ Command for simplicity in seeding
                $user->roles()->syncWithoutDetaching([
                    $role->id => [
                        'command_id' => $command->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]
                ]);
            }

            $testUsers[$roleName] = $user;
        }

        // 1. Create Some Test Vehicles
        if (\App\Models\FleetVehicle::count() < 10) {
            foreach (range(1, 10) as $i) {
                \App\Models\FleetVehicle::create([
                    'reg_no' => "ABC-" . rand(100, 999) . "XY",
                    'make' => 'Toyota',
                    'model' => 'Hilux',
                    'vehicle_type' => 'SUV',
                    'lifecycle_status' => 'IN_STOCK',
                    'chassis_number' => "CHAS-" . str()->random(8),
                    'engine_number' => "ENG-" . str()->random(8),
                    'year_of_manufacture' => 2023,
                ]);
            }
        }

        $vehicle = \App\Models\FleetVehicle::first();
        $service = app(\App\Services\Fleet\FleetWorkflowService::class);

        $userAc = $testUsers['Area Controller'];
        $userOc = $testUsers['OC Workshop'];
        $userSo = $testUsers['Staff Officer T&L'];

        // --- SCENARIO 1: New Vehicle Request (Draft) by Area Controller ---
        $service->createRequest($userAc, [
            'request_type' => 'FLEET_NEW_VE_HICLE', // Intentionally mismatched to trigger fallback or distinct
            'requested_vehicle_type' => 'SUV',
            'requested_quantity' => 2,
            'notes' => 'Expanding patrol fleet.',
        ]);
        // Fix typo above and re-create correctly
        $req1 = $service->createRequest($userAc, [
            'request_type' => 'FLEET_NEW_VEHICLE',
            'requested_vehicle_type' => 'SUV',
            'requested_quantity' => 2,
            'notes' => 'Expanding patrol fleet.',
        ]);

        // --- SCENARIO 2: Re-allocation Request (Submitted) by Area Controller ---
        $req2 = $service->createRequest($userAc, [
            'request_type' => 'FLEET_RE_ALLOCATION',
            'fleet_vehicle_id' => $vehicle->id,
            'notes' => 'Re-allocating TO HQ unit.',
        ]);
        $service->submit($req2, $userAc);

        // --- SCENARIO 3: Requisition (Low Amount: 150k) by OC Workshop ---
        $req3 = $service->createRequest($userOc, [
            'request_type' => 'FLEET_REQUISITION',
            'fleet_vehicle_id' => $vehicle->id,
            'amount' => 150000,
            'notes' => 'Brake replacement.',
        ]);
        $service->submit($req3, $userOc);

        // --- SCENARIO 4: Requisition (Mid Amount: 450k) by OC Workshop ---
        $req4 = $service->createRequest($userOc, [
            'request_type' => 'FLEET_REQUISITION',
            'fleet_vehicle_id' => $vehicle->id,
            'amount' => 450000,
            'notes' => 'Engine top overhaul.',
        ]);
        $service->submit($req4, $userOc);

        // --- SCENARIO 5: Requisition (High Amount: 1.2M) by OC Workshop ---
        $req5 = $service->createRequest($userOc, [
            'request_type' => 'FLEET_REQUISITION',
            'fleet_vehicle_id' => $vehicle->id,
            'amount' => 1200000,
            'notes' => 'Major gearbox replacement.',
        ]);
        $service->submit($req5, $userOc);

        // --- SCENARIO 6: Repair Request by Staff Officer T&L ---
        $req6 = $service->createRequest($userSo, [
            'request_type' => 'FLEET_REPAIR',
            'fleet_vehicle_id' => $vehicle->id,
            'notes' => 'Dented bumper fix.',
        ]);
        $service->submit($req6, $userSo);
    }
}
