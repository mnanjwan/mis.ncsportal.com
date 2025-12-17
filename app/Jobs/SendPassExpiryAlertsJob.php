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
        // Find approved pass applications expiring in 24 hours
        $expiryDate = now()->addHours(24);

        $passes = PassApplication::where('status', 'APPROVED')
            ->where('end_date', '<=', $expiryDate)
            ->where('end_date', '>', now())
            ->with('officer')
            ->get();

        foreach ($passes as $pass) {
            if ($pass->officer->user_id) {
                $hoursRemaining = now()->diffInHours($pass->end_date);

                Notification::create([
                    'user_id' => $pass->officer->user_id,
                    'notification_type' => 'PASS_EXPIRY_ALERT',
                    'title' => 'Pass Expiring Soon',
                    'message' => "Your pass expires in {$hoursRemaining} hours. Please ensure you return on time.",
                    'data' => [
                        'pass_application_id' => $pass->id,
                        'end_date' => $pass->end_date->format('Y-m-d'),
                    ],
                ]);
            }
        }
    }
}

