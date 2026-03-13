<?php

namespace Tests\Feature;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Officer;
use App\Models\PassApplication;
use App\Services\PassService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PassApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Officer $officer;

    protected function setUp(): void
    {
        parent::setUp();

        LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'ANNUAL_LEAVE',
            'max_duration_days' => 30,
            'max_occurrences_per_year' => 2,
            'requires_medical_certificate' => false,
            'description' => 'Annual leave',
        ]);

        $this->user = User::create([
            'email' => 'officer@ncs.gov.ng',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->officer = Officer::create([
            'user_id' => $this->user->id,
            'service_number' => 'NCS00001',
            'initials' => 'T',
            'surname' => 'Officer',
            'first_name' => 'Test',
            'email' => 'officer@ncs.gov.ng',
            'sex' => 'M',
            'date_of_birth' => now()->subYears(30),
            'date_of_first_appointment' => now()->subYears(5),
            'date_of_present_appointment' => now()->subYears(1),
            'substantive_rank' => 'CA I',
            'salary_grade_level' => 'GL 05',
            'present_station' => null,
        ]);

        // Exhaust annual leave: 2 approved applications for current year
        $annualLeaveType = LeaveType::where('code', 'ANNUAL_LEAVE')->first();
        for ($i = 0; $i < 2; $i++) {
            LeaveApplication::create([
                'officer_id' => $this->officer->id,
                'leave_type_id' => $annualLeaveType->id,
                'start_date' => now()->startOfYear()->addDays($i * 20),
                'end_date' => now()->startOfYear()->addDays($i * 20 + 5),
                'number_of_days' => 6,
                'status' => 'APPROVED',
            ]);
        }
    }

    /** @test */
    public function pass_submission_stores_working_days_not_calendar_days(): void
    {
        $passService = new PassService();
        // Find next Monday and Wednesday (3 working days)
        $start = Carbon::now()->startOfWeek()->addWeek();
        if ($start->lte(Carbon::today())) {
            $start->addWeek();
        }
        $end = $start->copy()->addDays(2);
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $this->assertEquals(3, $passService->workingDaysBetween($startDate, $endDate));

        $response = $this->actingAs($this->user)->post(route('pass.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Personal',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('officer.pass-applications'));
        $response->assertSessionHas('success');

        $pass = PassApplication::where('officer_id', $this->officer->id)->latest()->first();
        $this->assertNotNull($pass);
        $this->assertEquals(3, $pass->number_of_days);
    }

    /** @test */
    public function pass_submission_with_weekend_stores_only_working_days(): void
    {
        $passService = new PassService();
        // Thu to following Mon = 3 working days (Thu, Fri, Mon)
        $start = Carbon::now()->startOfWeek()->addDays(3)->addWeek();
        if ($start->lte(Carbon::today())) {
            $start->addWeek();
        }
        $end = $start->copy()->addDays(4);
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $this->assertEquals(3, $passService->workingDaysBetween($startDate, $endDate));

        $response = $this->actingAs($this->user)->post(route('pass.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Personal',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('officer.pass-applications'));
        $pass = PassApplication::where('officer_id', $this->officer->id)->latest()->first();
        $this->assertEquals(3, $pass->number_of_days);
    }

    /** @test */
    public function pass_submission_over_gl_limit_is_rejected(): void
    {
        $passService = new PassService();
        // GL 05 officer has max 21 working days. Build a range with 22+ working days (~32 calendar days)
        $start = Carbon::now()->addDays(1);
        $end = $start->copy()->addDays(35);
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $workingDays = $passService->workingDaysBetween($startDate, $endDate);
        $this->assertGreaterThan(21, $workingDays, 'Test range should have more than 21 working days');

        $response = $this->actingAs($this->user)->post(route('pass.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Personal',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('working days', session('error'));
        $this->assertStringContainsString('21', session('error'));
    }

    /** @test */
    public function pass_submission_within_gl07_limit_succeeds(): void
    {
        $this->officer->update(['salary_grade_level' => 'GL 07']);

        $passService = new PassService();
        $start = Carbon::now()->addDays(1);
        $end = $start->copy()->addDays(14); // ~10 working days
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        $expectedWorkingDays = $passService->workingDaysBetween($startDate, $endDate);
        $this->assertLessThanOrEqual(30, $expectedWorkingDays);

        $response = $this->actingAs($this->user)->post(route('pass.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Personal',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('officer.pass-applications'));
        $pass = PassApplication::where('officer_id', $this->officer->id)->latest()->first();
        $this->assertEquals($expectedWorkingDays, $pass->number_of_days);
    }
}
