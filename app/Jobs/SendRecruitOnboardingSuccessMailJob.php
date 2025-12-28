<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Mail\RecruitOnboardingSuccessMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecruitOnboardingSuccessMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Officer
     */
    public $recruit;

    public ?string $recruitName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Officer $recruit,
        ?string $recruitName = null
    ) {
        $this->recruit = $recruit;
        $this->recruitName = $recruitName ?? trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->recruit->email) {
                Log::warning('Cannot send onboarding success email: recruit has no email', [
                    'recruit_id' => $this->recruit->id,
                ]);
                return;
            }

            Mail::to($this->recruit->email)->send(new RecruitOnboardingSuccessMail(
                $this->recruit,
                $this->recruitName
            ));

            Log::info('Recruit onboarding success email sent successfully', [
                'recruit_id' => $this->recruit->id,
                'email' => $this->recruit->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send recruit onboarding success email', [
                'recruit_id' => $this->recruit->id,
                'email' => $this->recruit->email ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}

