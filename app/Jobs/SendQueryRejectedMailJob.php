<?php

namespace App\Jobs;

use App\Models\Query;
use App\Mail\QueryRejectedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendQueryRejectedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * @var Query
     */
    public $query;

    /**
     * Create a new job instance.
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->query->load(['officer.user']);
            
            if (!$this->query->officer || !$this->query->officer->user || !$this->query->officer->user->email) {
                Log::warning('Cannot send query rejected email: officer has no email', [
                    'query_id' => $this->query->id,
                    'officer_id' => $this->query->officer_id,
                ]);
                return;
            }

            Mail::to($this->query->officer->user->email)->send(
                new QueryRejectedMail($this->query)
            );

            Log::info('Query rejected email sent', [
                'query_id' => $this->query->id,
                'officer_id' => $this->query->officer_id,
                'email' => $this->query->officer->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send query rejected email', [
                'query_id' => $this->query->id,
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
        Log::error('Query rejected email job failed after all retries', [
            'query_id' => $this->query->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
