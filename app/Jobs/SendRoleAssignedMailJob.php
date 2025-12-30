<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Notification;
use App\Mail\NotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRoleAssignedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public Notification $notification
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send role assignment email: user has no email', [
                    'user_id' => $this->user->id,
                    'notification_id' => $this->notification->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(
                new NotificationMail($this->user, $this->notification)
            );

            Log::info('Role assignment email sent', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'notification_id' => $this->notification->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send role assignment email', [
                'user_id' => $this->user->id,
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Role assignment email job failed after all retries', [
            'user_id' => $this->user->id,
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}


