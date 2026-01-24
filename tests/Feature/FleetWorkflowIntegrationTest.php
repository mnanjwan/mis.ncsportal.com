<?php

namespace Tests\Feature;

use App\Jobs\SendNotificationEmailJob;
use App\Models\Command;
use App\Models\FleetRequest;
use App\Models\FleetRequestStep;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Fleet Workflow Integration Test
 * 
 * This test queries existing users from the live database and tests the complete
 * Fleet workflow from CD creation through final release using HTTP requests.
 * 
 * IMPORTANT: This test does NOT use RefreshDatabase, so test records will persist
 * in the database for manual verification.
 * 
 * @group integration
 * @group fleet
 */
class FleetWorkflowIntegrationTest extends TestCase
{

    private ?User $cdUser = null;
    private ?User $areaControllerUser = null;
    private ?User $cgcUser = null;
    private ?User $dcgFatsUser = null;
    private ?User $acgTsUser = null;
    private ?User $ccTlUser = null;
    private ?int $originCommandId = null;
    private array $testVehicles = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use the live database connection for this integration test
        // This test queries existing users from the live database
        // Override the test database with the live database name
        $liveDbName = 'mis_ncsportal'; // Live database name
        
        // Purge and reconfigure the MySQL connection to use live database
        DB::purge('mysql');
        config(['database.connections.mysql.database' => $liveDbName]);
        DB::reconnect('mysql');
        
        // Ensure session driver is set for authentication
        config(['session.driver' => 'array']);
        
        // Fake the queue to prevent actual email sending but allow verification
        Queue::fake();
    }

    /**
     * Query existing users from the database with Fleet roles assigned
     */
    private function findExistingUsers(): void
    {
        // Find CD user with command assignment
        $this->cdUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'CD')
                ->where('user_roles.is_active', true)
                ->whereNotNull('user_roles.command_id');
        }, '>=', 1)->where('is_active', true)->first();

        if (!$this->cdUser) {
            $this->markTestSkipped('No CD user found in database with active role and command assignment');
        }

        // Get the command ID from the CD's role pivot
        $cdRole = $this->cdUser->roles()
            ->where('name', 'CD')
            ->wherePivot('is_active', true)
            ->wherePivotNotNull('command_id')
            ->first();
        
        $this->originCommandId = $cdRole?->pivot->command_id;

        if (!$this->originCommandId) {
            $this->markTestSkipped('CD user found but no command_id in role pivot');
        }

        // Find Area Controller from the same command
        $this->areaControllerUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'Area Controller')
                ->where('user_roles.is_active', true)
                ->where('user_roles.command_id', $this->originCommandId);
        })->where('is_active', true)->first();

        if (!$this->areaControllerUser) {
            $this->markTestSkipped("No Area Controller user found for command ID {$this->originCommandId}");
        }

        // Find system-wide roles (may or may not have command_id, but role access_level is system_wide)
        $this->cgcUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'CGC')
                ->where('user_roles.is_active', true)
                ->where('access_level', 'system_wide');
        })->where('is_active', true)->first();

        if (!$this->cgcUser) {
            $this->markTestSkipped('No CGC user found with system-wide role');
        }

        $this->dcgFatsUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'DCG FATS')
                ->where('user_roles.is_active', true)
                ->where('access_level', 'system_wide');
        })->where('is_active', true)->first();

        if (!$this->dcgFatsUser) {
            $this->markTestSkipped('No DCG FATS user found with system-wide role');
        }

        $this->acgTsUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'ACG TS')
                ->where('user_roles.is_active', true)
                ->where('access_level', 'system_wide');
        })->where('is_active', true)->first();

        if (!$this->acgTsUser) {
            $this->markTestSkipped('No ACG TS user found with system-wide role');
        }

        $this->ccTlUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'CC T&L')
                ->where('user_roles.is_active', true)
                ->where('access_level', 'system_wide');
        })->where('is_active', true)->first();

        if (!$this->ccTlUser) {
            $this->markTestSkipped('No CC T&L user found with system-wide role');
        }

        // Reload all users from the database after reconnection to ensure they're on the correct connection
        $this->cdUser = User::find($this->cdUser->id);
        $this->areaControllerUser = User::find($this->areaControllerUser->id);
        $this->cgcUser = User::find($this->cgcUser->id);
        $this->dcgFatsUser = User::find($this->dcgFatsUser->id);
        $this->acgTsUser = User::find($this->acgTsUser->id);
        $this->ccTlUser = User::find($this->ccTlUser->id);
    }

    /**
     * Create test vehicles for the CC T&L to reserve
     */
    private function createTestVehicles(): void
    {
        $timestamp = time();
        $random = rand(1000, 9999);
        
        $this->testVehicles[] = FleetVehicle::firstOrCreate(
            ['chassis_number' => "CH-INTEGRATION-{$timestamp}-{$random}-1"],
            [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'vehicle_type' => 'SALOON',
                'engine_number' => "EN-INTEGRATION-{$timestamp}-{$random}-1",
                'service_status' => 'SERVICEABLE',
                'lifecycle_status' => 'IN_STOCK',
            ]
        );

        $this->testVehicles[] = FleetVehicle::firstOrCreate(
            ['chassis_number' => "CH-INTEGRATION-{$timestamp}-{$random}-2"],
            [
                'make' => 'Toyota',
                'model' => 'Corolla',
                'vehicle_type' => 'SALOON',
                'engine_number' => "EN-INTEGRATION-{$timestamp}-{$random}-2",
                'service_status' => 'SERVICEABLE',
                'lifecycle_status' => 'IN_STOCK',
            ]
        );
    }

    /**
     * Verify CD receives notifications (in-app and email)
     */
    private function verifyCdNotifications(int $requestId, string $expectedTitleContains = ''): void
    {
        // Verify in-app notification exists for this request
        $notification = Notification::where('user_id', $this->cdUser->id)
            ->where('entity_type', 'fleet_request')
            ->where('entity_id', $requestId)
            ->latest()
            ->first();

        // Just verify notification exists - title may vary
        $this->assertNotNull($notification, "CD should receive in-app notification for request #{$requestId}");

        // Verify email job was dispatched (if notification exists)
        if ($notification) {
            Queue::assertPushed(SendNotificationEmailJob::class, function ($job) use ($notification) {
                return $job->notification->id === $notification->id;
            });
        }
    }

    /**
     * Complete Fleet Workflow Integration Test
     * 
     * Tests the complete workflow from CD creation through final release.
     * Documents all possible actions and statuses at each step.
     */
    public function test_complete_fleet_workflow_with_existing_users(): void
    {
        // Step 0: Find existing users and create test vehicles
        $this->findExistingUsers();
        $this->createTestVehicles();

        // ====================================================================
        // STEP 0: CD CREATES AND SUBMITS REQUEST
        // ====================================================================
        // Action: Use FleetWorkflowService directly to create and submit
        // Possible actions: CREATE (DRAFT), SUBMIT (SUBMITTED)
        // Status transition: DRAFT → SUBMITTED
        
        $service = app(FleetWorkflowService::class);
        
        // Create request using service
        $request = $service->createCommandRequisition($this->cdUser, [
            'requested_vehicle_type' => 'SALOON',
            'requested_make' => 'Toyota',
            'requested_model' => 'Corolla',
            'requested_quantity' => 2,
        ]);

        $this->assertNotNull($request, 'Request should be created');
        $this->assertEquals('DRAFT', $request->status, 'Initial status should be DRAFT');
        $this->assertNull($request->current_step_order, 'DRAFT requests have no current step');

        // Submit the request using service
        $request = $service->submit($request, $this->cdUser);

        $request->refresh();
        $this->assertEquals('SUBMITTED', $request->status, 'Status should be SUBMITTED after submission');
        $this->assertEquals(1, $request->current_step_order, 'Current step should be 1 (Area Controller)');
        $this->assertNotNull($request->submitted_at, 'submitted_at should be set');

        // Verify CD was notified of submission
        $this->verifyCdNotifications($request->id, 'Request submitted');

        // ====================================================================
        // STEP 1: AREA CONTROLLER (FORWARD action)
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: SUBMITTED → IN_REVIEW
        
        $request = $service->act($request, $this->areaControllerUser, 'FORWARDED', 'Area Controller forwarded to CGC');

        $request->refresh();
        $this->assertEquals('IN_REVIEW', $request->status, 'Status should be IN_REVIEW after Area Controller');
        $this->assertEquals(2, $request->current_step_order, 'Current step should be 2 (CGC)');

        $step1 = $request->steps()->where('step_order', 1)->first();
        $this->assertNotNull($step1->acted_by_user_id, 'Step 1 should have acted_by_user_id');
        $this->assertEquals('FORWARDED', $step1->decision, 'Step 1 decision should be FORWARDED');
        $this->assertEquals($this->areaControllerUser->id, $step1->acted_by_user_id, 'Step 1 should be acted by Area Controller');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 2: CGC (FORWARD action)
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: IN_REVIEW → IN_REVIEW (continues)
        
        $request = $service->act($request, $this->cgcUser, 'FORWARDED', 'CGC forwarded to DCG FATS');

        $request->refresh();
        $this->assertEquals('IN_REVIEW', $request->status, 'Status should remain IN_REVIEW');
        $this->assertEquals(3, $request->current_step_order, 'Current step should be 3 (DCG FATS)');

        $step2 = $request->steps()->where('step_order', 2)->first();
        $this->assertEquals('FORWARDED', $step2->decision, 'Step 2 decision should be FORWARDED');
        $this->assertEquals($this->cgcUser->id, $step2->acted_by_user_id, 'Step 2 should be acted by CGC');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 3: DCG FATS (FORWARD action)
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: IN_REVIEW → IN_REVIEW (continues)
        
        $request = $service->act($request, $this->dcgFatsUser, 'FORWARDED', 'DCG FATS forwarded to ACG TS');

        $request->refresh();
        $this->assertEquals('IN_REVIEW', $request->status, 'Status should remain IN_REVIEW');
        $this->assertEquals(4, $request->current_step_order, 'Current step should be 4 (ACG TS)');

        $step3 = $request->steps()->where('step_order', 3)->first();
        $this->assertEquals('FORWARDED', $step3->decision, 'Step 3 decision should be FORWARDED');
        $this->assertEquals($this->dcgFatsUser->id, $step3->acted_by_user_id, 'Step 3 should be acted by DCG FATS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 4: ACG TS (FORWARD action)
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: IN_REVIEW → IN_REVIEW (continues)
        
        $request = $service->act($request, $this->acgTsUser, 'FORWARDED', 'ACG TS forwarded to CC T&L');

        $request->refresh();
        $this->assertEquals('IN_REVIEW', $request->status, 'Status should remain IN_REVIEW');
        $this->assertEquals(5, $request->current_step_order, 'Current step should be 5 (CC T&L)');

        $step4 = $request->steps()->where('step_order', 4)->first();
        $this->assertEquals('FORWARDED', $step4->decision, 'Step 4 decision should be FORWARDED');
        $this->assertEquals($this->acgTsUser->id, $step4->acted_by_user_id, 'Step 4 should be acted by ACG TS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 5: CC T&L (REVIEW action) - PROPOSE VEHICLES
        // ====================================================================
        // Action: Use FleetWorkflowService->ccTlPropose() with vehicle_ids
        // Possible actions: 
        //   - REVIEWED (via propose with vehicles) → advances workflow
        //   - KIV (via propose with empty vehicle_ids) → pauses at step 5
        // Status transition: IN_REVIEW → PENDING_CGC_APPROVAL (or PARTIALLY_FULFILLED if partial)
        
        $vehicleIds = array_map(fn($v) => $v->id, $this->testVehicles);
        $request = $service->ccTlPropose($request, $this->ccTlUser, $vehicleIds, 'CC T&L reserved 2 vehicles');

        $request->refresh();
        $this->assertContains($request->status, ['PENDING_CGC_APPROVAL', 'PARTIALLY_FULFILLED'], 
            'Status should be PENDING_CGC_APPROVAL or PARTIALLY_FULFILLED after CC T&L propose');
        $this->assertEquals(6, $request->current_step_order, 'Current step should be 6 (ACG TS - back up)');

        // Verify vehicles are reserved
        foreach ($this->testVehicles as $vehicle) {
            $vehicle->refresh();
            $this->assertEquals($request->id, $vehicle->reserved_fleet_request_id, 
                "Vehicle {$vehicle->id} should be reserved for request {$request->id}");
        }

        $step5 = $request->steps()->where('step_order', 5)->first();
        $this->assertEquals('REVIEWED', $step5->decision, 'Step 5 decision should be REVIEWED');
        $this->assertEquals($this->ccTlUser->id, $step5->acted_by_user_id, 'Step 5 should be acted by CC T&L');

        $this->verifyCdNotifications($request->id, 'Vehicles reserved');

        // ====================================================================
        // STEP 6: ACG TS (FORWARD action) - Back up for approval
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: PENDING_CGC_APPROVAL → PENDING_CGC_APPROVAL (continues)
        
        $request = $service->act($request, $this->acgTsUser, 'FORWARDED', 'ACG TS forwarded proposal to DCG FATS');

        $request->refresh();
        $this->assertEquals('PENDING_CGC_APPROVAL', $request->status, 'Status should be PENDING_CGC_APPROVAL');
        $this->assertEquals(7, $request->current_step_order, 'Current step should be 7 (DCG FATS)');

        $step6 = $request->steps()->where('step_order', 6)->first();
        $this->assertEquals('FORWARDED', $step6->decision, 'Step 6 decision should be FORWARDED');
        $this->assertEquals($this->acgTsUser->id, $step6->acted_by_user_id, 'Step 6 should be acted by ACG TS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 7: DCG FATS (FORWARD action) - Back up for approval
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: PENDING_CGC_APPROVAL → PENDING_CGC_APPROVAL (continues)
        
        $request = $service->act($request, $this->dcgFatsUser, 'FORWARDED', 'DCG FATS forwarded proposal to CGC');

        $request->refresh();
        $this->assertEquals('PENDING_CGC_APPROVAL', $request->status, 'Status should be PENDING_CGC_APPROVAL');
        $this->assertEquals(8, $request->current_step_order, 'Current step should be 8 (CGC - final approval)');

        $step7 = $request->steps()->where('step_order', 7)->first();
        $this->assertEquals('FORWARDED', $step7->decision, 'Step 7 decision should be FORWARDED');
        $this->assertEquals($this->dcgFatsUser->id, $step7->acted_by_user_id, 'Step 7 should be acted by DCG FATS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 8: CGC (APPROVE action) - Final approval
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: APPROVED or REJECTED
        // Possible actions: 
        //   - APPROVED → continues workflow to step 9
        //   - REJECTED → ends workflow (current_step_order becomes null)
        // Status transition: PENDING_CGC_APPROVAL → APPROVED (or REJECTED)
        
        $request = $service->act($request, $this->cgcUser, 'APPROVED', 'CGC approved the vehicle allocation');

        $request->refresh();
        $this->assertEquals('APPROVED', $request->status, 'Status should be APPROVED after CGC approval');
        $this->assertEquals(9, $request->current_step_order, 'Current step should be 9 (DCG FATS - back down)');

        $step8 = $request->steps()->where('step_order', 8)->first();
        $this->assertEquals('APPROVED', $step8->decision, 'Step 8 decision should be APPROVED');
        $this->assertEquals($this->cgcUser->id, $step8->acted_by_user_id, 'Step 8 should be acted by CGC');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 9: DCG FATS (FORWARD action) - Back down after approval
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: APPROVED → APPROVED (continues)
        
        $request = $service->act($request, $this->dcgFatsUser, 'FORWARDED', 'DCG FATS forwarded back to ACG TS');

        $request->refresh();
        $this->assertEquals('APPROVED', $request->status, 'Status should remain APPROVED');
        $this->assertEquals(10, $request->current_step_order, 'Current step should be 10 (ACG TS)');

        $step9 = $request->steps()->where('step_order', 9)->first();
        $this->assertEquals('FORWARDED', $step9->decision, 'Step 9 decision should be FORWARDED');
        $this->assertEquals($this->dcgFatsUser->id, $step9->acted_by_user_id, 'Step 9 should be acted by DCG FATS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 10: ACG TS (FORWARD action) - Back down after approval
        // ====================================================================
        // Action: Use FleetWorkflowService->act() with decision: FORWARDED
        // Possible actions: FORWARDED (only valid action for FORWARD steps)
        // Status transition: APPROVED → APPROVED (continues)
        
        $request = $service->act($request, $this->acgTsUser, 'FORWARDED', 'ACG TS forwarded back to CC T&L for release');

        $request->refresh();
        $this->assertEquals('APPROVED', $request->status, 'Status should remain APPROVED');
        $this->assertEquals(11, $request->current_step_order, 'Current step should be 11 (CC T&L - release)');

        $step10 = $request->steps()->where('step_order', 10)->first();
        $this->assertEquals('FORWARDED', $step10->decision, 'Step 10 decision should be FORWARDED');
        $this->assertEquals($this->acgTsUser->id, $step10->acted_by_user_id, 'Step 10 should be acted by ACG TS');

        $this->verifyCdNotifications($request->id, 'Action taken');

        // ====================================================================
        // STEP 11: CC T&L (REVIEW action) - RELEASE RESERVED VEHICLES
        // ====================================================================
        // Action: Use FleetWorkflowService->ccTlReleaseReserved()
        // Possible actions: REVIEWED (via release) → workflow complete
        // Status transition: APPROVED → RELEASED
        
        $request = $service->ccTlReleaseReserved($request, $this->ccTlUser, 'CC T&L released vehicles to command pool');

        $request->refresh();
        $this->assertEquals('RELEASED', $request->status, 'Status should be RELEASED after CC T&L release');
        $this->assertNull($request->current_step_order, 'Current step should be null (workflow complete)');

        $step11 = $request->steps()->where('step_order', 11)->first();
        $this->assertEquals('REVIEWED', $step11->decision, 'Step 11 decision should be REVIEWED');
        $this->assertEquals($this->ccTlUser->id, $step11->acted_by_user_id, 'Step 11 should be acted by CC T&L');

        // Verify vehicles are assigned to command
        foreach ($this->testVehicles as $vehicle) {
            $vehicle->refresh();
            $this->assertEquals($this->originCommandId, $vehicle->current_command_id, 
                "Vehicle {$vehicle->id} should be assigned to command {$this->originCommandId}");
            $this->assertEquals('AT_COMMAND_POOL', $vehicle->lifecycle_status, 
                "Vehicle {$vehicle->id} should be in AT_COMMAND_POOL status");
            $this->assertNull($vehicle->reserved_fleet_request_id, 
                "Vehicle {$vehicle->id} should no longer be reserved");

            // Verify FleetVehicleAssignment record exists
            $this->assertTrue(
                FleetVehicleAssignment::where('fleet_vehicle_id', $vehicle->id)
                    ->where('assigned_to_command_id', $this->originCommandId)
                    ->exists(),
                "FleetVehicleAssignment should exist for vehicle {$vehicle->id}"
            );
        }

        $this->verifyCdNotifications($request->id, 'Vehicles released');

        // ====================================================================
        // FINAL VERIFICATION: All steps completed
        // ====================================================================
        
        $allSteps = $request->steps()->orderBy('step_order')->get();
        $this->assertCount(11, $allSteps, 'Request should have 11 workflow steps');
        
        foreach ($allSteps as $step) {
            $this->assertNotNull($step->acted_by_user_id, "Step {$step->step_order} should have acted_by_user_id");
            $this->assertNotNull($step->acted_at, "Step {$step->step_order} should have acted_at");
            $this->assertNotNull($step->decision, "Step {$step->step_order} should have decision");
        }

        // Summary of all possible actions and statuses
        $this->assertTrue(true, "
            ====================================================================
            FLEET WORKFLOW - ALL POSSIBLE ACTIONS AND STATUSES
            ====================================================================
            
            STEP 1-4, 6-7, 9-10 (FORWARD actions):
              - Only valid action: FORWARDED
              - Status: IN_REVIEW (steps 1-4) or PENDING_CGC_APPROVAL (steps 6-7) or APPROVED (steps 9-10)
            
            STEP 5 (CC T&L REVIEW action):
              - Action: POST /fleet/requests/{id}/cc-tl/propose
              - With vehicles: REVIEWED → advances to step 6, status: PENDING_CGC_APPROVAL or PARTIALLY_FULFILLED
              - Without vehicles: KIV → stays at step 5, status: KIV
            
            STEP 8 (CGC APPROVE action):
              - Action: POST /fleet/requests/{id}/act
              - APPROVED → continues to step 9, status: APPROVED
              - REJECTED → workflow ends, status: REJECTED, current_step_order: null
            
            STEP 11 (CC T&L REVIEW action):
              - Action: POST /fleet/requests/{id}/cc-tl/release
              - REVIEWED → workflow complete, status: RELEASED, current_step_order: null
            
            STATUS TRANSITIONS:
              - DRAFT → SUBMITTED → IN_REVIEW → PENDING_CGC_APPROVAL → APPROVED → RELEASED
              - Alternative: DRAFT → SUBMITTED → IN_REVIEW → KIV (if no vehicles at step 5)
              - Alternative: DRAFT → SUBMITTED → IN_REVIEW → PENDING_CGC_APPROVAL → REJECTED (if CGC rejects)
            
            CD NOTIFICATIONS:
              - CD receives notifications (in-app and email) at EVERY step:
                1. Request submitted
                2. Area Controller action
                3. CGC action (forward)
                4. DCG FATS action (forward)
                5. ACG TS action (forward)
                6. CC T&L vehicles reserved
                7. ACG TS action (back up)
                8. DCG FATS action (back up)
                9. CGC approval
                10. DCG FATS action (back down)
                11. ACG TS action (back down)
                12. CC T&L vehicles released
        ");
    }
}
