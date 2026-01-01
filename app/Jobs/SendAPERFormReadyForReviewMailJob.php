<?php

namespace App\Jobs;

use App\Models\APERForm;
use App\Mail\APERFormReadyForReviewMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAPERFormReadyForReviewMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public APERForm $form
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Refresh form to ensure relationships are loaded
            $this->form->refresh();
            $this->form->load(['officer.user']);
            
            if (!$this->form->officer->user || !$this->form->officer->user->email) {
                Log::warning('Cannot send APER form ready for review email: officer has no email', [
                    'form_id' => $this->form->id,
                    'officer_id' => $this->form->officer_id,
                ]);
                return;
            }

            Mail::to($this->form->officer->user->email)->send(
                new APERFormReadyForReviewMail($this->form)
            );

            Log::info('APER form ready for review email sent', [
                'form_id' => $this->form->id,
                'email' => $this->form->officer->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send APER form ready for review email', [
                'form_id' => $this->form->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('APER form ready for review email job failed after all retries', [
            'form_id' => $this->form->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

