<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Models\Notification;
use App\Mail\NotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDeceasedOfficerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Officer $officer,
        public Notification $notification
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload relationships to ensure we have fresh data
            $this->officer->load('user');
            $this->notification->refresh();

            if (!$this->officer->user || !$this->officer->user->email) {
                Log::warning('Cannot send deceased officer notification email: officer has no user or email', [
                    'officer_id' => $this->officer->id,
                    'notification_id' => $this->notification->id,
                ]);
                return;
            }

            Mail::to($this->officer->user->email)->send(
                new NotificationMail($this->officer->user, $this->notification)
            );

            Log::info('Deceased officer notification email sent', [
                'officer_id' => $this->officer->id,
                'user_id' => $this->officer->user->id,
                'email' => $this->officer->user->email,
                'notification_id' => $this->notification->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send deceased officer notification email', [
                'officer_id' => $this->officer->id,
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
        Log::error('Deceased officer notification email job failed after all retries', [
            'officer_id' => $this->officer->id,
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}






