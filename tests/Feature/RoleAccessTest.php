<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createUserWithRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        $user = User::factory()->create();
        $user->roles()->attach($role);
        return $user;
    }

    /**
     * Test HRD Access
     */
    public function test_hrd_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('HRD');

        $response = $this->actingAs($user)->get(route('hrd.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('hrd.officers'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('hrd.emolument-timeline'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('hrd.staff-orders'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('hrd.reports'));
        $response->assertStatus(200);
    }

    /**
     * Test Staff Officer Access
     */
    public function test_staff_officer_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Staff Officer');

        $response = $this->actingAs($user)->get(route('staff-officer.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('staff-officer.leave-pass'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('staff-officer.manning-level'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('staff-officer.roster'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('staff-officer.officers'));
        $response->assertStatus(200);
    }

    /**
     * Test Building Unit Access
     */
    public function test_building_unit_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Building Unit');

        $response = $this->actingAs($user)->get(route('building.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('building.quarters'));
        $response->assertStatus(200);
    }

    /**
     * Test Establishment Access
     */
    public function test_establishment_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Establishment');

        $response = $this->actingAs($user)->get(route('establishment.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('establishment.service-numbers'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('establishment.new-recruits'));
        $response->assertStatus(200);
    }

    /**
     * Test Accounts Access
     */
    public function test_accounts_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Accounts');

        $response = $this->actingAs($user)->get(route('accounts.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('accounts.validated-officers'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('accounts.deceased-officers'));
        $response->assertStatus(200);
    }

    /**
     * Test Board Access
     */
    public function test_board_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Board');

        $response = $this->actingAs($user)->get(route('board.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('board.promotions'));
        $response->assertStatus(200);
    }

    /**
     * Test Assessor Access
     */
    public function test_assessor_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Assessor');

        $response = $this->actingAs($user)->get(route('assessor.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('assessor.emoluments'));
        $response->assertStatus(200);
    }

    /**
     * Test Validator Access
     */
    public function test_validator_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Validator');

        $response = $this->actingAs($user)->get(route('validator.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('validator.emoluments'));
        $response->assertStatus(200);
    }

    /**
     * Test Officer Access
     */
    public function test_officer_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Officer');

        $response = $this->actingAs($user)->get(route('officer.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('officer.profile'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('officer.emoluments'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('officer.leave-applications'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('officer.pass-applications'));
        $response->assertStatus(200);
    }

    /**
     * Test Area Controller Access
     */
    public function test_area_controller_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Area Controller');

        $response = $this->actingAs($user)->get(route('area-controller.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('area-controller.emoluments'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('area-controller.leave-pass'));
        $response->assertStatus(200);
    }

    /**
     * Test DC Admin Access
     */
    public function test_dc_admin_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('DC Admin');

        $response = $this->actingAs($user)->get(route('dc-admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('dc-admin.leave-pass'));
        $response->assertStatus(200);
    }

    /**
     * Test Welfare Access
     */
    public function test_welfare_can_access_dashboard_and_features()
    {
        $user = $this->createUserWithRole('Welfare');

        $response = $this->actingAs($user)->get(route('welfare.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('welfare.deceased-officers'));
        $response->assertStatus(200);
    }

    /**
     * Test Unauthorized Access
     */
    public function test_officer_cannot_access_hrd_dashboard()
    {
        $user = $this->createUserWithRole('Officer');

        $response = $this->actingAs($user)->get(route('hrd.dashboard'));
        $response->assertStatus(302);
    }
}
