<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Mail\RecruitOnboardingLinkMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecruitOnboardingLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Officer
     */
    public $recruit;

    public string $onboardingLink;
    public ?string $recruitName = null;
    public ?string $tempPassword = null; // Backward compatibility - not used for recruits

    /**
     * Create a new job instance.
     */
    public function __construct(
        Officer $recruit,
        string $onboardingLink,
        ?string $recruitName = null,
        ?string $tempPassword = null // Backward compatibility - not used for recruits
    ) {
        $this->recruit = $recruit;
        $this->onboardingLink = $onboardingLink;
        $this->recruitName = $recruitName;
        $this->tempPassword = $tempPassword ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            if (!$this->recruit->email) {
                Log::warning('Cannot send recruit onboarding email: recruit has no email', [
                    'recruit_id' => $this->recruit->id,
                ]);
                return;
            }

            $recruitName = $this->recruitName ?? trim(($this->recruit->initials ?? '') . ' ' . ($this->recruit->surname ?? ''));

            Mail::to($this->recruit->email)->send(
                new RecruitOnboardingLinkMail(
                    $this->onboardingLink,
                    $recruitName,
                    $this->recruit->email
                )
            );

            // Update recruit status
            $this->recruit->update([
                'onboarding_status' => 'link_sent',
                'onboarding_link_sent_at' => now(),
            ]);

            Log::info('Recruit onboarding email sent', [
                'recruit_id' => $this->recruit->id,
                'email' => $this->recruit->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send recruit onboarding email', [
                'recruit_id' => $this->recruit->id,
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
        Log::error('Recruit onboarding email job failed after all retries', [
            'recruit_id' => $this->recruit->id,
            'error' => $exception->getMessage(),
        ]);
    }
}


