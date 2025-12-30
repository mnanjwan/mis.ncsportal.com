<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Query;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckExpiredQueries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queries:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire queries that have passed their response deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired queries...');

        // Find queries that are pending response and have passed their deadline
        $expiredQueries = Query::where('status', 'PENDING_RESPONSE')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '<', now())
            ->with(['officer', 'issuedBy'])
            ->get();

        if ($expiredQueries->isEmpty()) {
            $this->info('No expired queries found.');
            return 0;
        }

        $this->info("Found {$expiredQueries->count()} expired query/queries.");

        $expiredCount = 0;
        $notificationService = app(NotificationService::class);

        foreach ($expiredQueries as $query) {
            try {
                DB::beginTransaction();

                // Update query status to ACCEPTED (automatically added to disciplinary record)
                $query->update([
                    'status' => 'ACCEPTED',
                    'reviewed_at' => now(),
                ]);

                // Send notification to officer about automatic expiration
                if ($query->officer && $query->officer->user) {
                    $notificationService->notifyQueryExpired($query);
                }

                DB::commit();

                $expiredCount++;
                $this->info("Expired query #{$query->id} for officer {$query->officer->initials} {$query->officer->surname}");

                Log::info('Query expired automatically', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'deadline' => $query->response_deadline,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to expire query #{$query->id}: {$e->getMessage()}");
                Log::error('Failed to expire query', [
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully expired {$expiredCount} query/queries.");

        return 0;
    }
}
