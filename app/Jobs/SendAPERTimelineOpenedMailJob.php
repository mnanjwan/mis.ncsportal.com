<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Models\APERTimeline;
use App\Mail\APERTimelineOpenedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAPERTimelineOpenedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Officer $officer,
        public APERTimeline $timeline
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Refresh models to ensure relationships are loaded
            $this->officer->refresh();
            $this->officer->load(['user']);
            $this->timeline->refresh();
            
            if (!$this->officer->user || !$this->officer->user->email) {
                Log::warning('Cannot send APER timeline opened email: officer has no email', [
                    'officer_id' => $this->officer->id,
                    'timeline_id' => $this->timeline->id,
                ]);
                return;
            }

            Mail::to($this->officer->user->email)->send(
                new APERTimelineOpenedMail($this->officer, $this->timeline)
            );

            Log::info('APER timeline opened email sent', [
                'officer_id' => $this->officer->id,
                'email' => $this->officer->user->email,
                'timeline_id' => $this->timeline->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send APER timeline opened email', [
                'officer_id' => $this->officer->id,
                'timeline_id' => $this->timeline->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('APER timeline opened email job failed after all retries', [
            'officer_id' => $this->officer->id,
            'timeline_id' => $this->timeline->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
