<?php

namespace App\Jobs;

use App\Models\Query;
use App\Mail\QueryResponseSubmittedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendQueryResponseSubmittedMailJob implements ShouldQueue
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
            $this->query->load(['officer', 'issuedBy']);
            
            if (!$this->query->issuedBy || !$this->query->issuedBy->email) {
                Log::warning('Cannot send query response submitted email: Staff Officer has no email', [
                    'query_id' => $this->query->id,
                    'issued_by_user_id' => $this->query->issued_by_user_id,
                ]);
                return;
            }

            Mail::to($this->query->issuedBy->email)->send(
                new QueryResponseSubmittedMail($this->query)
            );

            Log::info('Query response submitted email sent', [
                'query_id' => $this->query->id,
                'user_id' => $this->query->issuedBy->id,
                'email' => $this->query->issuedBy->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send query response submitted email', [
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
        Log::error('Query response submitted email job failed after all retries', [
            'query_id' => $this->query->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
