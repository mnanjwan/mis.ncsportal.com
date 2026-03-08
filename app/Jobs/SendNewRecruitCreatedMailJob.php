<?php

namespace App\Jobs;

use App\Mail\NewRecruitCreatedMail;
use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewRecruitCreatedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use \App\Jobs\Concerns\RateLimitsOutboundMail;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(public Officer $recruit)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->recruit->email)) {
            Log::warning('NewRecruitCreatedMail: Skipping - recruit has no email', [
                'recruit_id' => $this->recruit->id,
            ]);
            return;
        }

        Mail::to($this->recruit->email)->send(new NewRecruitCreatedMail($this->recruit));

        Log::info('NewRecruitCreatedMail: Email sent successfully', [
            'recruit_id' => $this->recruit->id,
            'email' => $this->recruit->email,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('NewRecruitCreatedMail job failed after all retries', [
            'recruit_id' => $this->recruit->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
