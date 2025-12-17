<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DeceasedOfficer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelfareFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function welfare_can_access_dashboard()
    {
        $user = User::where('email', 'welfare@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('welfare.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welfare Dashboard');
    }

    /** @test */
    public function welfare_can_view_deceased_officers()
    {
        $user = User::where('email', 'welfare@ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('welfare.dashboard'));

        $response->assertStatus(200);
        $this->assertTrue(DeceasedOfficer::count() > 0);
    }

    /** @test */
    public function deceased_officers_have_required_data()
    {
        $deceasedOfficer = DeceasedOfficer::first();

        $this->assertNotNull($deceasedOfficer);
        $this->assertNotNull($deceasedOfficer->officer_id);
        $this->assertNotNull($deceasedOfficer->reported_by);
        $this->assertNotNull($deceasedOfficer->date_of_death);
    }
}
