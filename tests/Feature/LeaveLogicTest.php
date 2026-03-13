<?php

namespace Tests\Feature;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Officer;
use App\Models\User;
use App\Services\LeaveService;
use App\Services\WorkingDayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveLogicTest extends TestCase
{
    use RefreshDatabase;

    protected LeaveService $leaveService;
    protected WorkingDayService $workingDayService;
    protected Officer $gl07Officer;
    protected Officer $gl05Officer;
    protected LeaveType $annualLeave;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workingDayService = new \App\Services\WorkingDayService();
        $this->leaveService = new LeaveService();

        $this->annualLeave = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL_LEAVE',
            'max_duration_days' => 30, // Default in table but overridden by GL in service
        ]);

        $this->gl07Officer = Officer::create([
            'service_number' => 'NCS007',
            'surname' => 'High',
            'initials' => 'H',
            'first_name' => 'Officer',
            'email' => 'high@ncs.gov.ng',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(35),
            'date_of_first_appointment' => now()->subYears(10),
            'date_of_present_appointment' => now()->subYears(2),
            'substantive_rank' => 'ASC I',
            'salary_grade_level' => 'GL 07',
        ]);

        $this->gl05Officer = Officer::create([
            'service_number' => 'NCS005',
            'surname' => 'Mid',
            'initials' => 'M',
            'first_name' => 'Officer',
            'email' => 'mid@ncs.gov.ng',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(5),
            'date_of_present_appointment' => now()->subYears(1),
            'substantive_rank' => 'CA I',
            'salary_grade_level' => 'GL 05',
        ]);
    }

    /** @test */
    public function gl07_officer_can_apply_up_to_30_working_days()
    {
        // 30 working days is approx 6 weeks
        $start = '2026-04-01'; // Wednesday
        $end = Carbon::parse($start)->addDays(41)->toDateString(); // exactly 30 working days

        $result = $this->leaveService->canApplyLeave($this->gl07Officer->id, $this->annualLeave->id, $start, $end);
        $this->assertTrue($result['can_apply'], "Failed for GL 07 on 30 working days");

        // 31 working days should fail
        $end = Carbon::parse($end)->addDay()->toDateString();
        // Check if next day is weekend
        if (Carbon::parse($end)->isWeekend()) {
            $end = Carbon::parse($end)->next('Monday')->toDateString();
        }
        dump("GL 07 (Expected Fail 31 days) Start: $start, End: $end, Days: " . $this->workingDayService->workingDaysBetween($start, $end));

        $result = $this->leaveService->canApplyLeave($this->gl07Officer->id, $this->annualLeave->id, $start, $end);
        $this->assertFalse($result['can_apply'], "Failed for GL 07 on 31 working days");
        $this->assertStringContainsString('30', $result['reason']);
    }

    /** @test */
    public function gl05_officer_is_capped_at_21_working_days()
    {
        $start = '2026-04-01';
        $end = Carbon::parse($start)->addDays(28)->toDateString(); // exactly 21 working days

        $result = $this->leaveService->canApplyLeave($this->gl05Officer->id, $this->annualLeave->id, $start, $end);
        $this->assertTrue($result['can_apply'], "Failed for GL 05 on 21 working days");

        // 22 working days should fail
        $end = Carbon::parse($end)->addDay()->toDateString();
        if (Carbon::parse($end)->isWeekend()) {
            $end = Carbon::parse($end)->next('Monday')->toDateString();
        }
        $result = $this->leaveService->canApplyLeave($this->gl05Officer->id, $this->annualLeave->id, $start, $end);
        $this->assertFalse($result['can_apply']);
        $this->assertStringContainsString('21', $result['reason']);
    }

    /** @test */
    public function cooling_period_prevents_leave_within_6_months_in_same_year()
    {
        // Approved leave in Jan
        LeaveApplication::create([
            'officer_id' => $this->gl07Officer->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-15',
            'number_of_days' => 10,
            'status' => 'APPROVED'
        ]);

        // Attempt in April (3 months later) - should fail
        $result = $this->leaveService->canApplyLeave($this->gl07Officer->id, $this->annualLeave->id, '2026-04-01', '2026-04-10');
        $this->assertFalse($result['can_apply']);
        $this->assertStringContainsString('6 months', $result['reason']);

        // Attempt in August (7 months later) - should pass
        $result = $this->leaveService->canApplyLeave($this->gl07Officer->id, $this->annualLeave->id, '2026-08-01', '2026-08-10');
        $this->assertTrue($result['can_apply']);
    }
}
