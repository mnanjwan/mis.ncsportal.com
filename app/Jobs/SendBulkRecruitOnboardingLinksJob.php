<?php

namespace App\Jobs;

use App\Models\Officer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkRecruitOnboardingLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $recruitData // Array of ['recruit_id', 'onboarding_link', 'temp_password', 'recruit_name']
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($this->recruitData as $data) {
            try {
                $recruit = Officer::find($data['recruit_id']);
                
                if (!$recruit) {
                    Log::warning('Recruit not found for bulk onboarding', [
                        'recruit_id' => $data['recruit_id'],
                    ]);
                    $failureCount++;
                    continue;
                }

                // Dispatch individual email job
                SendRecruitOnboardingLinkJob::dispatch(
                    $recruit,
                    $data['onboarding_link'],
                    $data['temp_password'],
                    $data['recruit_name'] ?? null
                );

                $successCount++;
            } catch (\Exception $e) {
                Log::error('Failed to queue recruit onboarding email', [
                    'recruit_id' => $data['recruit_id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }
        }

        Log::info('Bulk recruit onboarding emails queued', [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'total' => count($this->recruitData),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk recruit onboarding email job failed', [
            'error' => $exception->getMessage(),
            'recruit_count' => count($this->recruitData),
        ]);
    }
}

