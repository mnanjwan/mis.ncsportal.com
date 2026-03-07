<?php

namespace App\Jobs;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use \App\Jobs\Concerns\RateLimitsOutboundMail;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $resetUrl
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->user->email) {
            Log::warning('Cannot send password reset email: user has no email', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        Mail::to($this->user->email)->send(new PasswordResetMail($this->user, $this->resetUrl));

        Log::info('Password reset email sent successfully', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Password reset email job failed after all retries', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
