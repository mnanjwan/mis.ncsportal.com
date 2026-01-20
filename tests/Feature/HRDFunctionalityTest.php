<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRDFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    private User $hrdUser;
    private User $officerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal fixture setup (avoid running full DatabaseSeeder)
        Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $hrdRole = Role::firstOrCreate(['name' => 'HRD'], ['code' => 'HRD', 'description' => 'Human Resources Department', 'access_level' => 'system_wide']);
        $officerRole = Role::firstOrCreate(['name' => 'Officer'], ['code' => 'OFFICER', 'description' => 'Officer', 'access_level' => 'personal']);

        $this->hrdUser = User::create([
            'email' => 'hrd@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->hrdUser->roles()->attach($hrdRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $this->officerUser = User::create([
            'email' => 'officer@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->officerUser->roles()->attach($officerRole->id, ['is_active' => true, 'assigned_at' => now()]);
    }

    /** @test */
    public function hrd_can_access_dashboard()
    {
        $response = $this->actingAs($this->hrdUser)->get(route('hrd.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('HRD Dashboard');
    }

    /** @test */
    public function hrd_can_view_officers_list()
    {
        $response = $this->actingAs($this->hrdUser)->get(route('hrd.officers'));

        $response->assertStatus(200);
        $response->assertSee('Officers List');
    }

    /** @test */
    public function hrd_can_view_staff_orders()
    {
        $response = $this->actingAs($this->hrdUser)->get(route('hrd.staff-orders'));

        $response->assertStatus(200);
        $response->assertSee('Staff Orders');
    }

    /** @test */
    public function hrd_can_view_retirement_list()
    {
        $response = $this->actingAs($this->hrdUser)->get(route('hrd.retirement-list'));

        $response->assertStatus(200);
        $response->assertSee('Retirement List');
    }

    /** @test */
    public function hrd_can_access_emolument_timeline()
    {
        $response = $this->actingAs($this->hrdUser)->get(route('hrd.emolument-timeline'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_hrd_cannot_access_hrd_dashboard()
    {
        $response = $this->actingAs($this->officerUser)->get(route('hrd.dashboard'));

        $response->assertStatus(302); // Redirected
    }
}
