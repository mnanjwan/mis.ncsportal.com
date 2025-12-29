<?php

namespace App\Jobs;

use App\Models\Investigation;
use App\Mail\InvestigationInvitationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInvestigationInvitationMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Investigation
     */
    public $investigation;

    /**
     * Create a new job instance.
     */
    public function __construct(Investigation $investigation)
    {
        $this->investigation = $investigation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->investigation->load(['officer.user', 'investigationOfficer']);
            
            if (!$this->investigation->officer || !$this->investigation->officer->user || !$this->investigation->officer->user->email) {
                Log::warning('Cannot send investigation invitation email: officer has no email', [
                    'investigation_id' => $this->investigation->id,
                    'officer_id' => $this->investigation->officer_id,
                ]);
                return;
            }

            Mail::to($this->investigation->officer->user->email)->send(
                new InvestigationInvitationMail($this->investigation)
            );

            Log::info('Investigation invitation email sent', [
                'investigation_id' => $this->investigation->id,
                'officer_id' => $this->investigation->officer_id,
                'email' => $this->investigation->officer->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send investigation invitation email', [
                'investigation_id' => $this->investigation->id,
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
        Log::error('Investigation invitation email job failed after all retries', [
            'investigation_id' => $this->investigation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

