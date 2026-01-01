<?php

namespace App\Jobs;

use App\Models\APERForm;
use App\Models\User;
use App\Mail\APERFormAcceptedToHRDMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAPERFormAcceptedToHRDMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public APERForm $form,
        public User $hrdUser
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

            if (!$this->hrdUser->email) {
                Log::warning('Cannot send APER form accepted to HRD email: HRD user has no email', [
                    'form_id' => $this->form->id,
                    'hrd_user_id' => $this->hrdUser->id,
                ]);
                return;
            }

            Mail::to($this->hrdUser->email)->send(
                new APERFormAcceptedToHRDMail($this->form, $this->hrdUser)
            );

            Log::info('APER form accepted to HRD email sent', [
                'form_id' => $this->form->id,
                'hrd_user_id' => $this->hrdUser->id,
                'email' => $this->hrdUser->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send APER form accepted to HRD email', [
                'form_id' => $this->form->id,
                'hrd_user_id' => $this->hrdUser->id,
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
        Log::error('APER form accepted to HRD email job failed after all retries', [
            'form_id' => $this->form->id,
            'hrd_user_id' => $this->hrdUser->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

