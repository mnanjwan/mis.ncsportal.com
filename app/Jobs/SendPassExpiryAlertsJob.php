<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\PassApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPassExpiryAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $notificationService = app(\App\Services\NotificationService::class);

        // 1. Proactive Reminder: 24 hours before resumption
        $reminderDate = now()->addDay()->toDateString();
        $reminderPasses = PassApplication::where('status', 'APPROVED')
            ->where('expiry_date', $reminderDate)
            ->where('resumption_reminder_sent', false)
            ->with('officer.user')
            ->get();

        foreach ($reminderPasses as $pass) {
            if ($pass->officer && $pass->officer->user) {
                $notificationService->notify(
                    $pass->officer->user,
                    'PASS_RESUMPTION_REMINDER',
                    'Pass Resumption Reminder',
                    "This is a reminder that your pass ends soon. You are expected to resume duty in 24 hours, on {$pass->expiry_date->format('d/m/Y')}.",
                    'pass_application',
                    $pass->id
                );

                $pass->update(['resumption_reminder_sent' => true]);
            }
        }

        // 2. Resumption Day Alert: Morning of resumption (at or after 08:00)
        if (now()->hour >= 8) {
            $today = now()->toDateString();
            $todayPasses = PassApplication::where('status', 'APPROVED')
                ->where('expiry_date', $today)
                ->where('resumption_day_alert_sent', false)
                ->with('officer.user')
                ->get();

            foreach ($todayPasses as $pass) {
                if ($pass->officer && $pass->officer->user) {
                    $notificationService->notify(
                        $pass->officer->user,
                        'PASS_RESUMPTION_ALERT',
                        'Duty Resumption Today',
                        "Your pass has ended. You are expected to resume duty today, {$pass->expiry_date->format('d/m/Y')}. Welcome back!",
                        'pass_application',
                        $pass->id
                    );

                    $pass->update(['resumption_day_alert_sent' => true]);
                }
            }
        }
    }
}

