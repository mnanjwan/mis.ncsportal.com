<?php

namespace Tests\Feature;

use App\Models\Command;
use App\Models\FleetRequest;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAudit;
use App\Models\FleetVehicleAssignment;
use App\Models\Role;
use App\Models\User;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        foreach ([
            ['name' => 'CD', 'code' => 'CD', 'access_level' => 'command_level'],
            ['name' => 'O/C T&L', 'code' => 'OC_TL', 'access_level' => 'command_level'],
            ['name' => 'Transport Store/Receiver', 'code' => 'TRANSPORT_STORE_RECEIVER', 'access_level' => 'command_level'],
            ['name' => 'Area Controller', 'code' => 'AREA_CONTROLLER', 'access_level' => 'command_level'],
            ['name' => 'CC T&L', 'code' => 'CC_TL', 'access_level' => 'system_wide'],
            ['name' => 'ACG TS', 'code' => 'ACG_TS', 'access_level' => 'system_wide'],
            ['name' => 'DCG FATS', 'code' => 'DCG_FATS', 'access_level' => 'system_wide'],
            ['name' => 'CGC', 'code' => 'CGC', 'access_level' => 'system_wide'],
            ['name' => 'Officer', 'code' => 'OFFICER', 'access_level' => 'personal'],
        ] as $r) {
            Role::create([
                'name' => $r['name'],
                'code' => $r['code'],
                'description' => $r['name'],
                'access_level' => $r['access_level'],
            ]);
        }
    }

    private function attachRole(User $user, string $roleName, ?int $commandId = null): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role->id, [
            'command_id' => $commandId,
            'is_active' => true,
            'assigned_at' => now(),
        ]);
    }

    public function test_cd_can_create_submit_and_flow_reserve_and_release(): void
    {
        $this->seedRoles();

        $command = Command::create([
            'code' => 'APAPA',
            'name' => 'APAPA',
            'location' => 'Lagos',
            'zone_id' => null,
            'is_active' => true,
        ]);

        $cd = User::factory()->create();
        $areaController = User::factory()->create();
        $ccTl = User::factory()->create();
        $acgTs = User::factory()->create();
        $dcgFats = User::factory()->create();
        $cgc = User::factory()->create();

        $this->attachRole($cd, 'CD', $command->id);
        $this->attachRole($areaController, 'Area Controller', $command->id);
        $this->attachRole($ccTl, 'CC T&L');
        $this->attachRole($acgTs, 'ACG TS');
        $this->attachRole($dcgFats, 'DCG FATS');
        $this->attachRole($cgc, 'CGC');

        // Inventory vehicles
        $v1 = FleetVehicle::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'vehicle_type' => 'SALOON',
            'chassis_number' => 'CH-1',
            'engine_number' => 'EN-1',
            'service_status' => 'SERVICEABLE',
            'lifecycle_status' => 'IN_STOCK',
        ]);
        $v2 = FleetVehicle::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'vehicle_type' => 'SALOON',
            'chassis_number' => 'CH-2',
            'engine_number' => 'EN-2',
            'service_status' => 'SERVICEABLE',
            'lifecycle_status' => 'IN_STOCK',
        ]);

        $service = app(FleetWorkflowService::class);

        $req = $service->createCommandRequisition($cd, [
            'requested_vehicle_type' => 'SALOON',
            'requested_make' => 'Toyota',
            'requested_model' => 'Corolla',
            'requested_quantity' => 2,
        ]);
        $this->assertSame('DRAFT', $req->status);

        $service->submit($req, $cd);
        $req->refresh();
        $this->assertSame('SUBMITTED', $req->status);
        $this->assertSame(1, $req->current_step_order);

        // Forward chain to step 5 (CC T&L)
        $service->act($req, $areaController, 'FORWARDED');
        $service->act($req, $cgc, 'FORWARDED');
        $service->act($req, $dcgFats, 'FORWARDED');
        $service->act($req, $acgTs, 'FORWARDED');
        $req->refresh();
        $this->assertSame(5, $req->current_step_order);

        // CC T&L proposes by reserving vehicles
        $service->ccTlPropose($req, $ccTl, [$v1->id, $v2->id], 'Available');
        $req->refresh();
        $v1->refresh();
        $v2->refresh();
        $this->assertSame($req->id, $v1->reserved_fleet_request_id);
        $this->assertSame($req->id, $v2->reserved_fleet_request_id);

        // Back up for approval: ACG TS -> DCG FATS -> CGC approve
        $service->act($req, $acgTs, 'FORWARDED');
        $service->act($req, $dcgFats, 'FORWARDED');
        $service->act($req, $cgc, 'APPROVED');

        // Back down: DCG -> ACG -> CC release step
        $service->act($req, $dcgFats, 'FORWARDED');
        $service->act($req, $acgTs, 'FORWARDED');
        $req->refresh();
        $this->assertSame(11, $req->current_step_order);

        $service->ccTlReleaseReserved($req, $ccTl, 'Released');
        $req->refresh();
        $v1->refresh();
        $v2->refresh();

        $this->assertSame($command->id, $v1->current_command_id);
        $this->assertSame('AT_COMMAND_POOL', $v1->lifecycle_status);
        $this->assertNull($v1->reserved_fleet_request_id);

        $this->assertSame($command->id, $v2->current_command_id);
        $this->assertSame('AT_COMMAND_POOL', $v2->lifecycle_status);
        $this->assertNull($v2->reserved_fleet_request_id);

        $this->assertTrue(
            FleetVehicleAssignment::where('fleet_vehicle_id', $v1->id)
                ->where('assigned_to_command_id', $command->id)
                ->exists()
        );
    }

    public function test_reg_and_engine_changes_are_audited(): void
    {
        $this->seedRoles();

        $user = User::factory()->create();
        $this->attachRole($user, 'CC T&L');

        $vehicle = FleetVehicle::create([
            'make' => 'Honda',
            'model' => 'Civic',
            'vehicle_type' => 'SALOON',
            'chassis_number' => 'CH-AUDIT',
            'engine_number' => null,
            'reg_no' => null,
            'service_status' => 'SERVICEABLE',
            'lifecycle_status' => 'IN_STOCK',
        ]);

        $this->actingAs($user)
            ->put(route('fleet.vehicles.identifiers.update', $vehicle), [
                'reg_no' => 'REG-001',
                'engine_number' => 'ENG-001',
            ])
            ->assertRedirect();

        $vehicle->refresh();
        $this->assertSame('REG-001', $vehicle->reg_no);
        $this->assertSame('ENG-001', $vehicle->engine_number);

        $this->assertTrue(
            FleetVehicleAudit::where('fleet_vehicle_id', $vehicle->id)
                ->where('field_name', 'REG_NO')
                ->where('new_value', 'REG-001')
                ->exists()
        );
        $this->assertTrue(
            FleetVehicleAudit::where('fleet_vehicle_id', $vehicle->id)
                ->where('field_name', 'ENGINE_NO')
                ->where('new_value', 'ENG-001')
                ->exists()
        );
    }
}

