<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Mail\RecruitVerificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecruitVerificationMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Officer
     */
    public $recruit;

    public string $verificationStatus;
    public ?string $verificationNotes;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Officer $recruit,
        string $verificationStatus,
        ?string $verificationNotes = null
    ) {
        $this->recruit = $recruit;
        $this->verificationStatus = $verificationStatus;
        $this->verificationNotes = $verificationNotes;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->recruit->email) {
                Log::warning('Cannot send verification email: recruit has no email', [
                    'recruit_id' => $this->recruit->id,
                ]);
                return;
            }

            Mail::to($this->recruit->email)->send(new RecruitVerificationMail(
                $this->recruit,
                $this->verificationStatus,
                $this->verificationNotes
            ));

            Log::info('Recruit verification email sent successfully', [
                'recruit_id' => $this->recruit->id,
                'email' => $this->recruit->email,
                'status' => $this->verificationStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send recruit verification email', [
                'recruit_id' => $this->recruit->id,
                'email' => $this->recruit->email ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}

