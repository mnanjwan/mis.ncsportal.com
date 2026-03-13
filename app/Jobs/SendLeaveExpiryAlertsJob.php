<?php

namespace App\Jobs;

use App\Models\LeaveApplication;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeaveExpiryAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $notificationService = app(\App\Services\NotificationService::class);

        // 1. Proactive Reminder: 48 hours before resumption
        $reminderDate = now()->addDays(2)->toDateString();
        $reminderLeaves = LeaveApplication::where('status', 'APPROVED')
            ->where('expiry_date', $reminderDate)
            ->where('resumption_reminder_sent', false)
            ->with('officer.user')
            ->get();

        foreach ($reminderLeaves as $leave) {
            if ($leave->officer && $leave->officer->user) {
                $notificationService->notify(
                    $leave->officer->user,
                    'LEAVE_RESUMPTION_REMINDER',
                    'Leave Resumption Reminder',
                    "This is a reminder that your leave ends soon. You are expected to resume duty in 48 hours, on {$leave->expiry_date->format('d/m/Y')}.",
                    'leave_application',
                    $leave->id
                );

                $leave->update(['resumption_reminder_sent' => true]);
            }
        }

        // 2. Resumption Day Alert: Morning of resumption (only if run at or after 08:00)
        if (now()->hour >= 8) {
            $today = now()->toDateString();
            $todayLeaves = LeaveApplication::where('status', 'APPROVED')
                ->where('expiry_date', $today)
                ->where('resumption_day_alert_sent', false)
                ->with('officer.user')
                ->get();

            foreach ($todayLeaves as $leave) {
                if ($leave->officer && $leave->officer->user) {
                    $notificationService->notify(
                        $leave->officer->user,
                        'LEAVE_RESUMPTION_ALERT',
                        'Duty Resumption Today',
                        "Your leave has ended. You are expected to resume duty today, {$leave->expiry_date->format('d/m/Y')}. Welcome back!",
                        'leave_application',
                        $leave->id
                    );

                    $leave->update(['resumption_day_alert_sent' => true]);
                }
            }
        }
    }
}

