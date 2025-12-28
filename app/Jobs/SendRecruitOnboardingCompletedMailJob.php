<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Models\User;
use App\Mail\RecruitOnboardingCompletedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecruitOnboardingCompletedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Officer
     */
    public $recruit;

    /**
     * @var User
     */
    public $user;

    public ?string $recruitName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Officer $recruit,
        User $user,
        ?string $recruitName = null
    ) {
        $this->recruit = $recruit;
        $this->user = $user;
        $this->recruitName = $recruitName ?? trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send onboarding completed email: user has no email', [
                    'user_id' => $this->user->id,
                    'recruit_id' => $this->recruit->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(new RecruitOnboardingCompletedMail(
                $this->recruit,
                $this->recruitName
            ));

            Log::info('Recruit onboarding completed email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'recruit_id' => $this->recruit->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send recruit onboarding completed email', [
                'user_id' => $this->user->id,
                'recruit_id' => $this->recruit->id,
                'email' => $this->user->email ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}

