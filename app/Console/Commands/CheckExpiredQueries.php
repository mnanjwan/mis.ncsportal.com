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
        $startTime = microtime(true);
        $this->info('Checking for expired queries...');

        Log::info('Query expiration check started', [
            'timestamp' => now()->toDateTimeString(),
            'command' => 'queries:check-expired',
        ]);

        // Find queries that are pending response and have reached or passed their deadline
        $expiredQueries = Query::where('status', 'PENDING_RESPONSE')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '<=', now())
            ->with(['officer', 'issuedBy'])
            ->get();

        Log::info('Query expiration check - queries found', [
            'total_expired' => $expiredQueries->count(),
            'check_timestamp' => now()->toDateTimeString(),
        ]);

        if ($expiredQueries->isEmpty()) {
            $this->info('No expired queries found.');

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Query expiration check completed - no expired queries', [
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return 0;
        }

        $this->info("Found {$expiredQueries->count()} expired query/queries.");

        // Log details of all expired queries found
        Log::info('Query expiration check - expired queries details', [
            'count' => $expiredQueries->count(),
            'queries' => $expiredQueries->map(function ($query) {
                return [
                    'id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'officer_name' => $query->officer ? "{$query->officer->initials} {$query->officer->surname}" : 'N/A',
                    'service_number' => $query->officer->service_number ?? 'N/A',
                    'issued_by' => $query->issuedBy ? $query->issuedBy->email : 'N/A',
                    'deadline' => $query->response_deadline->toDateTimeString(),
                    'issued_at' => $query->issued_at ? $query->issued_at->toDateTimeString() : null,
                    'hours_overdue' => now()->diffInHours($query->response_deadline),
                ];
            })->toArray(),
        ]);

        $expiredCount = 0;
        $failedCount = 0;
        $notificationService = app(NotificationService::class);

        foreach ($expiredQueries as $query) {
            try {
                $queryStartTime = microtime(true);

                Log::info('Processing expired query', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'officer_name' => $query->officer ? "{$query->officer->initials} {$query->officer->surname}" : 'N/A',
                    'deadline' => $query->response_deadline->toDateTimeString(),
                    'status_before' => $query->status,
                ]);

                // Update query status to ACCEPTED in a tight transaction to prevent deadlocks
                DB::beginTransaction();

                $query->update([
                    'status' => 'ACCEPTED',
                    'reviewed_at' => now(),
                ]);

                DB::commit();

                $query->refresh();

                // Send notifications AFTER transaction commit to avoid holding locks during email operations
                $notificationSent = false;
                if ($query->officer && $query->officer->user) {
                    try {
                        $notificationService->notifyQueryExpired($query);
                        $notificationSent = true;
                        Log::info('Query expiration notification sent', [
                            'query_id' => $query->id,
                            'officer_id' => $query->officer_id,
                            'user_id' => $query->officer->user->id,
                            'user_email' => $query->officer->user->email,
                        ]);
                    } catch (\Exception $notifException) {
                        // Log but don't fail the expiration process if notification fails
                        Log::warning('Failed to send query expiration notification', [
                            'query_id' => $query->id,
                            'officer_id' => $query->officer_id,
                            'error' => $notifException->getMessage(),
                            'error_trace' => $notifException->getTraceAsString(),
                        ]);
                    }
                } else {
                    Log::warning('Query expiration notification skipped - no officer user', [
                        'query_id' => $query->id,
                        'officer_id' => $query->officer_id,
                    ]);
                }

                $expiredCount++;
                $queryExecutionTime = round((microtime(true) - $queryStartTime) * 1000, 2);

                $this->info("Expired query #{$query->id} for officer {$query->officer->initials} {$query->officer->surname}");

                Log::info('Query expired successfully', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'officer_name' => $query->officer ? "{$query->officer->initials} {$query->officer->surname}" : 'N/A',
                    'service_number' => $query->officer->service_number ?? 'N/A',
                    'status_after' => $query->status,
                    'reviewed_at' => $query->reviewed_at->toDateTimeString(),
                    'deadline' => $query->response_deadline->toDateTimeString(),
                    'hours_overdue' => now()->diffInHours($query->response_deadline),
                    'notification_sent' => $notificationSent,
                    'execution_time_ms' => $queryExecutionTime,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;

                $this->error("Failed to expire query #{$query->id}: {$e->getMessage()}");

                Log::error('Failed to expire query', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'officer_name' => $query->officer ? "{$query->officer->initials} {$query->officer->surname}" : 'N/A',
                    'deadline' => $query->response_deadline ? $query->response_deadline->toDateTimeString() : 'N/A',
                    'error' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->info("Successfully expired {$expiredCount} query/queries.");

        if ($failedCount > 0) {
            $this->warn("Failed to expire {$failedCount} query/queries.");
        }

        Log::info('Query expiration check completed', [
            'total_found' => $expiredQueries->count(),
            'successfully_expired' => $expiredCount,
            'failed' => $failedCount,
            'execution_time_ms' => $executionTime,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return 0;
    }
}
