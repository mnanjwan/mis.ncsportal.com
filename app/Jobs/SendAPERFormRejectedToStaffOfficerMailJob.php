<?php

namespace App\Jobs;

use App\Models\APERForm;
use App\Models\User;
use App\Mail\APERFormRejectedToStaffOfficerMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAPERFormRejectedToStaffOfficerMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public APERForm $form,
        public User $staffOfficer
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
            $this->form->load(['officer']);

            if (!$this->staffOfficer->email) {
                Log::warning('Cannot send APER form rejected to Staff Officer email: Staff Officer has no email', [
                    'form_id' => $this->form->id,
                    'staff_officer_id' => $this->staffOfficer->id,
                ]);
                return;
            }

            Mail::to($this->staffOfficer->email)->send(
                new APERFormRejectedToStaffOfficerMail($this->form, $this->staffOfficer)
            );

            Log::info('APER form rejected to Staff Officer email sent', [
                'form_id' => $this->form->id,
                'staff_officer_id' => $this->staffOfficer->id,
                'email' => $this->staffOfficer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send APER form rejected to Staff Officer email', [
                'form_id' => $this->form->id,
                'staff_officer_id' => $this->staffOfficer->id,
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
        Log::error('APER form rejected to Staff Officer email job failed after all retries', [
            'form_id' => $this->form->id,
            'staff_officer_id' => $this->staffOfficer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
