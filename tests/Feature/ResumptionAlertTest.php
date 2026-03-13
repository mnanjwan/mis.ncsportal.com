<?php

namespace Tests\Feature;

use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\Officer;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Jobs\SendLeaveExpiryAlertsJob;
use App\Jobs\SendPassExpiryAlertsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumptionAlertTest extends TestCase
{
    use RefreshDatabase;

    protected $officer;
    protected $leaveType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL_' . uniqid(),
            'days_allowed' => 30,
        ]);
        
        $user = User::factory()->create(['email' => 'user_' . uniqid() . '@example.com']);
        $this->officer = Officer::create([
            'user_id' => $user->id,
            'service_number' => 'TEST' . strtoupper(uniqid()),
            'appointment_number' => 'APP' . strtoupper(uniqid()),
            'initials' => 'J',
            'surname' => 'Doe',
            'sex' => 'M',
            'date_of_birth' => '1990-01-01',
            'date_of_first_appointment' => '2015-01-01',
            'date_of_present_appointment' => '2020-01-01',
            'substantive_rank' => 'ASC I',
            'salary_grade_level' => 'GL 07',
            'email' => 'officer' . uniqid() . '@example.com',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'geopolitical_zone' => 'South West',
            'entry_qualification' => 'B.Sc',
            'permanent_home_address' => 'No 1 Test St',
            'phone_number' => '080' . rand(1000000, 9999999),
        ]);
    }

    /** @test */
    public function leave_resumption_alerts_are_sent_correctly()
    {
        // 1. Create a leave for 48h reminder
        $reminderLeave = LeaveApplication::create([
            'officer_id' => $this->officer->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
            'number_of_days' => 10,
            'status' => 'APPROVED',
            'expiry_date' => now()->addDays(2)->toDateString(),
            'resumption_reminder_sent' => false,
        ]);

        // 2. Create a leave for today alert
        $todayLeave = LeaveApplication::create([
            'officer_id' => $this->officer->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->subDays(15)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'number_of_days' => 10,
            'status' => 'APPROVED',
            'expiry_date' => now()->toDateString(),
            'resumption_day_alert_sent' => false,
        ]);

        // Mock time to 09:00 to trigger day alert
        $this->travelTo(now()->setHour(9));

        // Run the job
        (new SendLeaveExpiryAlertsJob())->handle();

        // Assert notifications were created
        $this->assertTrue(Notification::where('notification_type', 'LEAVE_RESUMPTION_REMINDER')->exists());
        $this->assertTrue(Notification::where('notification_type', 'LEAVE_RESUMPTION_ALERT')->exists());

        // Assert flags were updated
        $this->assertTrue($reminderLeave->fresh()->resumption_reminder_sent);
        $this->assertTrue($todayLeave->fresh()->resumption_day_alert_sent);
    }

    /** @test */
    public function pass_resumption_alerts_are_sent_correctly()
    {
        // 1. Create a pass for 24h reminder
        $reminderPass = PassApplication::create([
            'officer_id' => $this->officer->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
            'number_of_days' => 3,
            'status' => 'APPROVED',
            'expiry_date' => now()->addDay()->toDateString(),
            'resumption_reminder_sent' => false,
        ]);

        // 2. Create a pass for today alert
        $todayPass = PassApplication::create([
            'officer_id' => $this->officer->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'number_of_days' => 3,
            'status' => 'APPROVED',
            'expiry_date' => now()->toDateString(),
            'resumption_day_alert_sent' => false,
        ]);

        // Mock time to 09:00 to trigger day alert
        $this->travelTo(now()->setHour(9));

        // Run the job
        (new SendPassExpiryAlertsJob())->handle();

        // Assert notifications were created
        $this->assertTrue(Notification::where('notification_type', 'PASS_RESUMPTION_REMINDER')->exists());
        $this->assertTrue(Notification::where('notification_type', 'PASS_RESUMPTION_ALERT')->exists());

        // Assert flags were updated
        $this->assertTrue($reminderPass->fresh()->resumption_reminder_sent);
        $this->assertTrue($todayPass->fresh()->resumption_day_alert_sent);
    }
}
