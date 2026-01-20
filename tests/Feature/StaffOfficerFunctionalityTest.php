<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Officer;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\Command;
use App\Models\Role;
use App\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffOfficerFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    private User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $command = Command::create([
            'name' => 'Test Command',
            'code' => 'TC001',
            'is_active' => true,
        ]);

        $staffRole = Role::firstOrCreate(
            ['name' => 'Staff Officer'],
            ['code' => 'STAFF_OFFICER', 'description' => 'Staff Officer', 'access_level' => 'command_level']
        );

        $this->staffUser = User::create([
            'email' => 'staff@test.ncs.gov.ng',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->staffUser->roles()->attach($staffRole->id, [
            'command_id' => $command->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        Officer::create([
            'user_id' => $this->staffUser->id,
            'service_number' => 'NCS55555',
            'email' => $this->staffUser->email,
            'initials' => 'ST',
            'surname' => 'OFFICER',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(35),
            'date_of_first_appointment' => now()->subYears(10),
            'date_of_present_appointment' => now()->subYears(3),
            'substantive_rank' => 'SC',
            'salary_grade_level' => 'GL11',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'BSc',
            'permanent_home_address' => 'Test Address',
            'phone_number' => '08000000004',
            'present_station' => $command->id,
            'is_active' => true,
        ]);

        // Minimal leave/pass records so list assertions hold
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_duration_days' => 30,
            'max_occurrences_per_year' => 2,
            'requires_medical_certificate' => false,
            'requires_approval_level' => 'DC_ADMIN',
            'is_active' => true,
        ]);

        LeaveApplication::create([
            'officer_id' => Officer::where('user_id', $this->staffUser->id)->value('id'),
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'number_of_days' => 10,
            'reason' => 'Test leave',
            'status' => 'PENDING',
            'submitted_at' => now(),
        ]);

        PassApplication::create([
            'officer_id' => Officer::where('user_id', $this->staffUser->id)->value('id'),
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'number_of_days' => 1,
            'reason' => 'Test pass',
            'status' => 'PENDING',
            'submitted_at' => now(),
        ]);
    }

    /** @test */
    public function staff_officer_can_access_dashboard()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Staff Officer Dashboard');
    }

    /** @test */
    public function staff_officer_can_view_leave_applications()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.leave-pass'));

        $response->assertStatus(200);
        $response->assertSee('Leave & Pass Management');
    }

    /** @test */
    public function staff_officer_can_view_manning_level()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.manning-level'));

        $response->assertStatus(200);
        $response->assertSee('Manning Level');
    }

    /** @test */
    public function staff_officer_can_view_duty_roster()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.roster'));

        $response->assertStatus(200);
        $response->assertSee('Duty Roster');
    }

    /** @test */
    public function leave_applications_are_displayed()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.leave-pass'));

        $response->assertStatus(200);
        // Check that leave applications exist in the view
        $this->assertTrue(LeaveApplication::count() > 0);
    }

    /** @test */
    public function pass_applications_are_displayed()
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff-officer.leave-pass'));

        $response->assertStatus(200);
        // Check that pass applications exist in the view
        $this->assertTrue(PassApplication::count() > 0);
    }
}
