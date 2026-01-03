<?php

namespace App\Jobs;

use App\Models\OfficerCourse;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCourseNominationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public OfficerCourse $course
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $this->course->load(['officer.user']);
            
            // Create notification and send email (NotificationService will handle email via job)
            $notificationService->notifyCourseNominationCreated($this->course);

            Log::info('Course nomination notification processed', [
                'course_id' => $this->course->id,
                'officer_id' => $this->course->officer_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process course nomination notification', [
                'course_id' => $this->course->id,
                'officer_id' => $this->course->officer_id,
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
        Log::error('Course nomination notification job failed after all retries', [
            'course_id' => $this->course->id,
            'officer_id' => $this->course->officer_id,
            'error' => $exception->getMessage(),
        ]);
    }
}

