<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRDFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function hrd_can_access_dashboard()
    {
        $user = User::where('email', 'hrd@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('HRD Dashboard');
    }

    /** @test */
    public function hrd_can_view_officers_list()
    {
        $user = User::where('email', 'hrd@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.officers.index'));

        $response->assertStatus(200);
        $response->assertSee('Officers Management');
    }

    /** @test */
    public function hrd_can_view_staff_orders()
    {
        $user = User::where('email', 'hrd@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.staff-orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Staff Orders');
    }

    /** @test */
    public function hrd_can_view_retirement_list()
    {
        $user = User::where('email', 'hrd@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.retirement.index'));

        $response->assertStatus(200);
        $response->assertSee('Retirement List');
    }

    /** @test */
    public function hrd_can_access_emolument_timeline()
    {
        $user = User::where('email', 'hrd@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.emolument-timeline.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_hrd_cannot_access_hrd_dashboard()
    {
        $user = User::where('email', 'officer@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('hrd.dashboard'));

        $response->assertStatus(302); // Redirected
    }
}
