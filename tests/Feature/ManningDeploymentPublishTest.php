<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Officer;
use App\Models\Command;
use App\Models\ManningRequest;
use App\Models\ManningRequestItem;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;
use App\Models\MovementOrder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ManningDeploymentPublishTest extends TestCase
{
    use RefreshDatabase;

    protected $hrdUser;
    protected $commands;
    protected $officers;
    protected $manningRequest;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create HRD user
        $this->hrdUser = User::create([
            'email' => 'hrd@ncs.gov.ng',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $hrdRole = Role::firstOrCreate(
            ['code' => 'HRD'],
            [
                'name' => 'HRD', 
                'code' => 'HRD', 
                'description' => 'Human Resources Department',
                'access_level' => 'system_wide'
            ]
        );
        $this->hrdUser->roles()->attach($hrdRole->id, [
            'assigned_at' => now(),
            'assigned_by' => $this->hrdUser->id,
            'is_active' => true,
        ]);

        // Create commands
        $this->commands = collect();
        for ($i = 1; $i <= 3; $i++) {
            $this->commands->push(Command::create([
                'name' => 'Test Command ' . $i,
                'code' => 'CMD' . $i,
                'is_active' => true,
            ]));
        }

        // Create officers
        $this->officers = collect();
        for ($i = 1; $i <= 5; $i++) {
            $this->officers->push(Officer::create([
                'service_number' => 'NCS' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'initials' => 'TEST',
                'surname' => 'OFFICER' . $i,
                'sex' => rand(0, 1) ? 'M' : 'F',
                'date_of_birth' => Carbon::now()->subYears(30 + rand(0, 20)),
                'date_of_first_appointment' => Carbon::now()->subYears(rand(5, 15)),
                'date_of_present_appointment' => Carbon::now()->subMonths(rand(6, 60)),
                'substantive_rank' => ['Assistant Superintendent', 'Deputy Superintendent', 'Superintendent'][rand(0, 2)],
                'salary_grade_level' => 'GL' . rand(7, 12),
                'state_of_origin' => 'Lagos',
                'lga' => 'Ikeja',
                'geopolitical_zone' => 'South West',
                'entry_qualification' => 'B.Sc',
                'permanent_home_address' => 'Test Address',
                'phone_number' => '080' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'email' => 'officer' . $i . '@test.ncs.gov.ng',
                'present_station' => $this->commands->first()->id,
                'is_active' => true,
                'is_deceased' => false,
                'interdicted' => false,
                'suspended' => false,
                'dismissed' => false,
            ]));
        }

        // Create manning request
        $this->manningRequest = ManningRequest::create([
            'request_number' => 'MR-TEST-001',
            'command_id' => $this->commands->last()->id,
            'requested_by' => $this->hrdUser->id,
            'status' => 'APPROVED',
            'justification' => 'Test request',
        ]);
    }

    /** @test */
    public function published_deployment_keeps_assignments_when_fully_published()
    {
        // Create a draft deployment
        $deployment = ManningDeployment::create([
            'deployment_number' => 'DEP-TEST-001',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        // Create 3 assignments
        $assignments = collect();
        for ($i = 0; $i < 3; $i++) {
            $assignments->push(ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $this->manningRequest->id,
                'officer_id' => $this->officers[$i]->id,
                'from_command_id' => $this->commands->first()->id,
                'to_command_id' => $this->commands->last()->id,
                'rank' => $this->officers[$i]->substantive_rank,
            ]));
        }

        // Verify initial state
        $this->assertEquals('DRAFT', $deployment->status);
        $this->assertEquals(3, $deployment->assignments()->count());

        // Publish the deployment (all assignments)
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.manning-deployments.publish', $deployment->id), [
                'manning_request_id' => $this->manningRequest->id,
            ]);

        $response->assertRedirect(route('hrd.manning-deployments.published'));
        $response->assertSessionHas('success');

        // Refresh deployment
        $deployment->refresh();

        // Verify deployment is published
        $this->assertEquals('PUBLISHED', $deployment->status);
        $this->assertNotNull($deployment->published_at);
        $this->assertEquals($this->hrdUser->id, $deployment->published_by);

        // CRITICAL: Verify assignments are still present (not deleted)
        $this->assertEquals(3, $deployment->assignments()->count(), 
            'Assignments should be kept when deployment is fully published');

        // Verify movement order was created
        $this->assertDatabaseHas('movement_orders', [
            'manning_request_id' => $this->manningRequest->id,
        ]);
    }

    /** @test */
    public function published_deployment_shows_correct_officer_count()
    {
        // Create a draft deployment with 3 assignments
        $deployment = ManningDeployment::create([
            'deployment_number' => 'DEP-TEST-002',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        for ($i = 0; $i < 3; $i++) {
            ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $this->manningRequest->id,
                'officer_id' => $this->officers[$i]->id,
                'from_command_id' => $this->commands->first()->id,
                'to_command_id' => $this->commands->last()->id,
                'rank' => $this->officers[$i]->substantive_rank,
            ]);
        }

        // Publish the deployment
        $this->actingAs($this->hrdUser)
            ->post(route('hrd.manning-deployments.publish', $deployment->id), [
                'manning_request_id' => $this->manningRequest->id,
            ]);

        // View published deployments page
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.manning-deployments.published'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.manning-deployments-published');

        // Verify deployment is in the list
        $deployment->refresh();
        $this->assertEquals(3, $deployment->assignments()->count());
        
        // The view should show "3 officer(s)" not "0 officer(s)"
        $response->assertSee('3 officer(s)');
    }

    /** @test */
    public function print_view_shows_correct_officers()
    {
        // Create a draft deployment with 3 assignments
        $deployment = ManningDeployment::create([
            'deployment_number' => 'DEP-TEST-003',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        for ($i = 0; $i < 3; $i++) {
            ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $this->manningRequest->id,
                'officer_id' => $this->officers[$i]->id,
                'from_command_id' => $this->commands->first()->id,
                'to_command_id' => $this->commands->last()->id,
                'rank' => $this->officers[$i]->substantive_rank,
            ]);
        }

        // Publish the deployment
        $this->actingAs($this->hrdUser)
            ->post(route('hrd.manning-deployments.publish', $deployment->id), [
                'manning_request_id' => $this->manningRequest->id,
            ]);

        // Access print view
        $response = $this->actingAs($this->hrdUser)
            ->get(route('hrd.manning-deployments.print', $deployment->id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboards.hrd.manning-deployment-print');

        // Verify all 3 officers are in the print view
        $deployment->refresh();
        $this->assertEquals(3, $deployment->assignments()->count());
        
        // Check that officers are displayed
        $response->assertSee($this->officers[0]->service_number);
        $response->assertSee($this->officers[1]->service_number);
        $response->assertSee($this->officers[2]->service_number);
        $response->assertSee('Total Officers: 3');
    }

    /** @test */
    public function partial_publish_removes_only_published_assignments()
    {
        // Create a draft deployment with 5 assignments
        $deployment = ManningDeployment::create([
            'deployment_number' => 'DEP-TEST-004',
            'status' => 'DRAFT',
            'created_by' => $this->hrdUser->id,
        ]);

        // Create 5 assignments - 3 from one request, 2 from another
        $manningRequest2 = ManningRequest::create([
            'request_number' => 'MR-TEST-002',
            'command_id' => $this->commands->last()->id,
            'requested_by' => $this->hrdUser->id,
            'status' => 'APPROVED',
            'justification' => 'Test request 2',
        ]);

        // 3 assignments from first request
        for ($i = 0; $i < 3; $i++) {
            ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $this->manningRequest->id,
                'officer_id' => $this->officers[$i]->id,
                'from_command_id' => $this->commands->first()->id,
                'to_command_id' => $this->commands->last()->id,
                'rank' => $this->officers[$i]->substantive_rank,
            ]);
        }

        // 2 assignments from second request
        for ($i = 3; $i < 5; $i++) {
            ManningDeploymentAssignment::create([
                'manning_deployment_id' => $deployment->id,
                'manning_request_id' => $manningRequest2->id,
                'officer_id' => $this->officers[$i]->id,
                'from_command_id' => $this->commands->first()->id,
                'to_command_id' => $this->commands->last()->id,
                'rank' => $this->officers[$i]->substantive_rank,
            ]);
        }

        // Verify initial state
        $this->assertEquals(5, $deployment->assignments()->count());

        // Publish only assignments from first request (3 assignments)
        $this->actingAs($this->hrdUser)
            ->post(route('hrd.manning-deployments.publish', $deployment->id), [
                'manning_request_id' => $this->manningRequest->id,
            ]);

        // Refresh deployment
        $deployment->refresh();

        // Deployment should still be DRAFT (not all assignments published)
        $this->assertEquals('DRAFT', $deployment->status);

        // Only 2 assignments should remain (from second request)
        $this->assertEquals(2, $deployment->assignments()->count(), 
            'Only unpublished assignments should remain in draft');

        // Verify the remaining assignments are from the second request
        $remainingAssignments = $deployment->assignments;
        foreach ($remainingAssignments as $assignment) {
            $this->assertEquals($manningRequest2->id, $assignment->manning_request_id);
        }
    }

    /** @test */
    public function cannot_publish_non_draft_deployment()
    {
        // Create a published deployment
        $deployment = ManningDeployment::create([
            'deployment_number' => 'DEP-TEST-005',
            'status' => 'PUBLISHED',
            'created_by' => $this->hrdUser->id,
            'published_by' => $this->hrdUser->id,
            'published_at' => now(),
        ]);

        // Try to publish again
        $response = $this->actingAs($this->hrdUser)
            ->post(route('hrd.manning-deployments.publish', $deployment->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', 'Can only publish draft deployments.');
    }
}

