<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Query;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendQueryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queries:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for queries approaching their deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Checking for queries requiring reminders...');

        Log::info('Query reminder check started', [
            'timestamp' => now()->toDateTimeString(),
            'command' => 'queries:send-reminders',
        ]);

        // Find queries that are:
        // 1. Still pending response
        // 2. Deadline is within next 24 hours
        // 3. Deadline hasn't passed yet
        $twentyFourHoursFromNow = now()->addHours(24);

        $queries = Query::where('status', 'PENDING_RESPONSE')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '>', now()) // Not yet expired
            ->where('response_deadline', '<=', $twentyFourHoursFromNow) // Within 24 hours
            ->with(['officer.user', 'issuedBy'])
            ->get();

        Log::info('Query reminder check - queries found', [
            'total_found' => $queries->count(),
            'check_timestamp' => now()->toDateTimeString(),
        ]);

        if ($queries->isEmpty()) {
            $this->info('No queries requiring reminders.');

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('Query reminder check completed - no reminders needed', [
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return 0;
        }

        $this->info("Found {$queries->count()} query/queries requiring reminders.");

        $sentCount = 0;
        $failedCount = 0;
        $notificationService = app(NotificationService::class);

        foreach ($queries as $query) {
            try {
                $officer = $query->officer;
                $hoursRemaining = now()->diffInHours($query->response_deadline, false);

                if (!$officer || !$officer->user) {
                    Log::warning('Query reminder skipped - no officer user', [
                        'query_id' => $query->id,
                        'officer_id' => $query->officer_id,
                    ]);
                    continue;
                }

                // Send reminder notification
                $notificationService->notifyQueryDeadlineReminder($query, $hoursRemaining);

                $sentCount++;

                $this->info("Sent reminder for query #{$query->id} to officer {$officer->initials} {$officer->surname}");

                Log::info('Query reminder sent successfully', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'officer_name' => "{$officer->initials} {$officer->surname}",
                    'service_number' => $officer->service_number ?? 'N/A',
                    'deadline' => $query->response_deadline->toDateTimeString(),
                    'hours_remaining' => $hoursRemaining,
                    'user_id' => $officer->user->id,
                    'user_email' => $officer->user->email,
                ]);

            } catch (\Exception $e) {
                $failedCount++;

                $this->error("Failed to send reminder for query #{$query->id}: {$e->getMessage()}");

                Log::error('Failed to send query reminder', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'error' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->info("Successfully sent {$sentCount} reminder(s).");

        if ($failedCount > 0) {
            $this->warn("Failed to send {$failedCount} reminder(s).");
        }

        Log::info('Query reminder check completed', [
            'total_found' => $queries->count(),
            'successfully_sent' => $sentCount,
            'failed' => $failedCount,
            'execution_time_ms' => $executionTime,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return 0;
    }
}
