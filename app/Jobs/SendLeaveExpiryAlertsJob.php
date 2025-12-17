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
        // Find approved leave applications expiring in 72 hours
        $expiryDate = now()->addHours(72);

        $leaves = LeaveApplication::where('status', 'APPROVED')
            ->where('end_date', '<=', $expiryDate)
            ->where('end_date', '>', now())
            ->with('officer')
            ->get();

        foreach ($leaves as $leave) {
            if ($leave->officer->user_id) {
                $hoursRemaining = now()->diffInHours($leave->end_date);

                Notification::create([
                    'user_id' => $leave->officer->user_id,
                    'notification_type' => 'LEAVE_EXPIRY_ALERT',
                    'title' => 'Leave Expiring Soon',
                    'message' => "Your leave expires in {$hoursRemaining} hours. Please ensure you return on time.",
                    'data' => [
                        'leave_application_id' => $leave->id,
                        'end_date' => $leave->end_date->format('Y-m-d'),
                    ],
                ]);
            }
        }
    }
}

