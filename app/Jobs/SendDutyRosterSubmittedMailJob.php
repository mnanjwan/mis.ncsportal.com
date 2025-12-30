<?php

namespace App\Jobs;

use App\Models\DutyRoster;
use App\Models\User;
use App\Mail\DutyRosterSubmittedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDutyRosterSubmittedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DutyRoster $roster,
        public User $user,
        public string $commandName,
        public string $periodStart,
        public string $periodEnd,
        public string $preparedByName,
        public int $assignmentsCount,
        public ?string $oicName = null,
        public ?string $secondInCommandName = null,
        public string $approvalRoute = 'area-controller/roster'
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send duty roster submitted email: user has no email', [
                    'roster_id' => $this->roster->id,
                    'user_id' => $this->user->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(
                new DutyRosterSubmittedMail(
                    $this->roster,
                    $this->user,
                    $this->commandName,
                    $this->periodStart,
                    $this->periodEnd,
                    $this->preparedByName,
                    $this->assignmentsCount,
                    $this->oicName,
                    $this->secondInCommandName,
                    $this->approvalRoute
                )
            );

            Log::info('Duty roster submitted email sent', [
                'roster_id' => $this->roster->id,
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send duty roster submitted email', [
                'roster_id' => $this->roster->id,
                'user_id' => $this->user->id,
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
        Log::error('Duty roster submitted email job failed after all retries', [
            'roster_id' => $this->roster->id,
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
