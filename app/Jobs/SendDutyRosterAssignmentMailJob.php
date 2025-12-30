<?php

namespace App\Jobs;

use App\Models\DutyRoster;
use App\Models\Officer;
use App\Mail\DutyRosterAssignmentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDutyRosterAssignmentMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DutyRoster $roster,
        public Officer $officer,
        public string $role,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd,
        public ?string $oicName = null,
        public ?string $secondInCommandName = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->officer->user || !$this->officer->user->email) {
                Log::warning('Cannot send duty roster assignment email: officer has no email', [
                    'roster_id' => $this->roster->id,
                    'officer_id' => $this->officer->id,
                ]);
                return;
            }

            Mail::to($this->officer->user->email)->send(
                new DutyRosterAssignmentMail(
                    $this->roster,
                    $this->officer,
                    $this->role,
                    $this->commandName,
                    $this->periodStart,
                    $this->periodEnd,
                    $this->oicName,
                    $this->secondInCommandName
                )
            );

            Log::info('Duty roster assignment email sent', [
                'roster_id' => $this->roster->id,
                'officer_id' => $this->officer->id,
                'email' => $this->officer->user->email,
                'role' => $this->role,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send duty roster assignment email', [
                'roster_id' => $this->roster->id,
                'officer_id' => $this->officer->id,
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
        Log::error('Duty roster assignment email job failed after all retries', [
            'roster_id' => $this->roster->id,
            'officer_id' => $this->officer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
