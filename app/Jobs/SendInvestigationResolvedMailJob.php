<?php

namespace App\Jobs;

use App\Models\Investigation;
use App\Models\Notification;
use App\Mail\NotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInvestigationResolvedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Notification
     */
    public $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->notification->load('user');
            
            if (!$this->notification->user || !$this->notification->user->email) {
                Log::warning('Cannot send investigation resolved email: user has no email', [
                    'notification_id' => $this->notification->id,
                    'user_id' => $this->notification->user_id,
                ]);
                return;
            }

            Mail::to($this->notification->user->email)->send(
                new NotificationMail($this->notification->user, $this->notification)
            );

            Log::info('Investigation resolved email sent', [
                'notification_id' => $this->notification->id,
                'user_id' => $this->notification->user_id,
                'email' => $this->notification->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send investigation resolved email', [
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
        Log::error('Investigation resolved email job failed after all retries', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

