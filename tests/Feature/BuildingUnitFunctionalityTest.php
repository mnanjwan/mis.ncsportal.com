<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingUnitFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function building_unit_can_access_dashboard()
    {
        $user = User::where('email', 'building.unit@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('building.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Building Unit Dashboard');
    }

    /** @test */
    public function building_unit_can_view_quarters()
    {
        $user = User::where('email', 'building.unit@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('building.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Quarters Management');
    }
}
