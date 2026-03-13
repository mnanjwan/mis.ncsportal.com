<?php

namespace Tests\Feature;

use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\LeaveType;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverlapLogicTest extends TestCase
{
    use RefreshDatabase;

    protected Officer $officer;
    protected LeaveType $annualLeave;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['email' => 'user_' . uniqid() . '@example.com']);
        $this->officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'NCS_TEST_' . uniqid(),
            'appointment_number' => 'APP_' . uniqid(),
            'initials' => 'J',
            'surname' => 'Doe',
            'sex' => 'M',
            'date_of_birth' => '1990-01-01',
            'date_of_first_appointment' => '2015-01-01',
            'date_of_present_appointment' => '2020-01-01',
            'substantive_rank' => 'ASC I',
            'salary_grade_level' => 'GL 07',
            'email' => 'officer_' . uniqid() . '@example.com',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'permanent_home_address' => 'Test Address',
            'entry_qualification' => 'B.Sc',
            'phone_number' => '080' . rand(1000000, 9999999),
        ]);

        $this->annualLeave = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL_LEAVE',
            'max_duration_days' => 30,
        ]);

        $this->actingAs($user);
    }

    /** @test */
    public function officer_cannot_apply_for_overlapping_leave()
    {
        // Create an existing approved leave
        LeaveApplication::create([
            'officer_id' => $this->officer->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-20',
            'status' => 'APPROVED',
            'number_of_days' => 11,
        ]);

        // Attempt to apply for an overlapping period
        $response = $this->post(route('leave.store'), [
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-05-15',
            'end_date' => '2026-05-25',
            'reason' => 'Overlap test',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('already have a APPROVED leave application overlapping', session('error'));
    }

    /** @test */
    public function officer_cannot_apply_for_leave_overlapping_with_pass()
    {
        // Create an existing pending pass
        PassApplication::create([
            'officer_id' => $this->officer->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'PENDING',
            'number_of_days' => 5,
        ]);

        // Attempt to apply for a leave overlapping with the pass
        $response = $this->post(route('leave.store'), [
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-06-03',
            'end_date' => '2026-06-10',
            'reason' => 'Pass overlap test',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('already have a PENDING pass application overlapping', session('error'));
    }

    /** @test */
    public function officer_cannot_apply_for_pass_overlapping_with_leave()
    {
        // Create an existing approved leave
        LeaveApplication::create([
            'officer_id' => $this->officer->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
            'status' => 'APPROVED',
            'number_of_days' => 11,
        ]);

        // Attempt to apply for a pass overlapping with the leave
        $response = $this->post(route('pass.store'), [
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'reason' => 'Leave overlap test',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('already have a APPROVED leave application overlapping', session('error'));
    }
}
