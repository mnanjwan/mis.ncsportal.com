<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffOfficerFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function staff_officer_can_access_dashboard()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Staff Officer Dashboard');
    }

    /** @test */
    public function staff_officer_can_view_leave_applications()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.leave-pass.index'));

        $response->assertStatus(200);
        $response->assertSee('Leave & Pass Management');
    }

    /** @test */
    public function staff_officer_can_view_manning_level()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.manning.index'));

        $response->assertStatus(200);
        $response->assertSee('Manning Level');
    }

    /** @test */
    public function staff_officer_can_view_duty_roster()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.duty-roster.index'));

        $response->assertStatus(200);
        $response->assertSee('Duty Roster');
    }

    /** @test */
    public function leave_applications_are_displayed()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.leave-pass.index'));

        $response->assertStatus(200);
        // Check that leave applications exist in the view
        $this->assertTrue(LeaveApplication::count() > 0);
    }

    /** @test */
    public function pass_applications_are_displayed()
    {
        $user = User::where('email', 'staff@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('staff-officer.leave-pass.index'));

        $response->assertStatus(200);
        // Check that pass applications exist in the view
        $this->assertTrue(PassApplication::count() > 0);
    }
}
