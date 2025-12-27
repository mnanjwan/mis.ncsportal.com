<?php

namespace App\Jobs;

use App\Models\APERForm;
use App\Models\User;
use App\Mail\APERReportingOfficerAssignedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAPERReportingOfficerAssignedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public APERForm $form,
        public User $reportingOfficer
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
            $this->form->load(['officer', 'reportingOfficer']);
            
            if (!$this->reportingOfficer->email) {
                Log::warning('Cannot send APER reporting officer assigned email: user has no email', [
                    'form_id' => $this->form->id,
                    'user_id' => $this->reportingOfficer->id,
                ]);
                return;
            }

            Mail::to($this->reportingOfficer->email)->send(
                new APERReportingOfficerAssignedMail($this->form, $this->reportingOfficer)
            );

            Log::info('APER reporting officer assigned email sent', [
                'form_id' => $this->form->id,
                'email' => $this->reportingOfficer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send APER reporting officer assigned email', [
                'form_id' => $this->form->id,
                'user_id' => $this->reportingOfficer->id,
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
        Log::error('APER reporting officer assigned email job failed after all retries', [
            'form_id' => $this->form->id,
            'user_id' => $this->reportingOfficer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
