<?php

namespace App\Jobs;

use App\Models\DutyRoster;
use App\Mail\DutyRosterApprovedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDutyRosterApprovedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DutyRoster $roster,
        public string $approvedByName,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Ensure roster has relationships loaded
            if (!$this->roster->relationLoaded('preparedBy')) {
                $this->roster->load('preparedBy');
            }

            $staffOfficer = $this->roster->preparedBy;
            if (!$staffOfficer || !$staffOfficer->email) {
                Log::warning('Cannot send duty roster approved email: staff officer has no email', [
                    'roster_id' => $this->roster->id,
                ]);
                return;
            }

            Mail::to($staffOfficer->email)->send(
                new DutyRosterApprovedMail(
                    $this->roster,
                    $this->approvedByName,
                    $this->commandName,
                    $this->periodStart,
                    $this->periodEnd
                )
            );

            Log::info('Duty roster approved email sent', [
                'roster_id' => $this->roster->id,
                'email' => $staffOfficer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send duty roster approved email', [
                'roster_id' => $this->roster->id,
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
        Log::error('Duty roster approved email job failed after all retries', [
            'roster_id' => $this->roster->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
