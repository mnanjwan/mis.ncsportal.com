<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send a notification email to a specific address (e.g. old email on address change).
 * Uses the same SMTP rate limit as all other mail jobs.
 */
class SendNotificationToEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use \App\Jobs\Concerns\RateLimitsOutboundMail;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $toEmail,
        public User $user,
        public Notification $notification
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->toEmail)->send(new NotificationMail($this->user, $this->notification));

        Log::info('Notification email sent to address', [
            'to_email' => $this->toEmail,
            'notification_id' => $this->notification->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationToEmailJob failed after all retries', [
            'to_email' => $this->toEmail,
            'error' => $exception->getMessage(),
        ]);
    }
}
