<?php

namespace App\Jobs;

use App\Mail\OnboardingLinkMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOnboardingLinkMailJob implements ShouldQueue
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
        public string $onboardingLink,
        public string $tempPassword,
        public ?string $officerName = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->toEmail)->send(new OnboardingLinkMail(
            $this->onboardingLink,
            $this->tempPassword,
            $this->officerName,
            $this->toEmail
        ));

        Log::info('Onboarding link email sent', [
            'email' => $this->toEmail,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Onboarding link email job failed after all retries', [
            'email' => $this->toEmail,
            'error' => $exception->getMessage(),
        ]);
    }
}
