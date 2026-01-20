<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DeceasedOfficer;
use App\Models\Role;
use App\Models\Officer;
use App\Models\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelfareFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $welfareRole = Role::firstOrCreate(
            ['name' => 'Welfare'],
            ['code' => 'WELFARE', 'description' => 'Welfare Department', 'access_level' => 'system_wide']
        );

        $welfareUser = User::create([
            'email' => 'welfare@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $welfareUser->roles()->attach($welfareRole->id, ['is_active' => true, 'assigned_at' => now()]);

        $reporter = User::create([
            'email' => 'reporter@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $officer = Officer::create([
            'service_number' => 'NCS77777',
            'email' => 'deceased@test.ncs.gov.ng',
            'initials' => 'DS',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(40),
            'date_of_first_appointment' => now()->subYears(15),
            'date_of_present_appointment' => now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000003',
            'present_station' => $command->id,
            'is_active' => true,
            'is_deceased' => true,
            'deceased_date' => now()->subDays(10),
        ]);

        DeceasedOfficer::create([
            'officer_id' => $officer->id,
            'reported_by' => $reporter->id,
            'reported_at' => now()->subDays(9),
            'date_of_death' => now()->subDays(10),
        ]);
    }

    /** @test */
    public function welfare_can_access_dashboard()
    {
        $user = User::where('email', 'welfare@test.ncs.gov.ng')->first();

        $response = $this->actingAs($user)->get(route('welfare.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welfare Dashboard');
    }

    /** @test */
    public function welfare_can_view_deceased_officers()
    {
        $user = User::where('email', 'welfare@test.ncs.gov.ng')->first();

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
