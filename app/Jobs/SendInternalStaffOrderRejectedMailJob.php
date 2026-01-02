<?php

namespace App\Jobs;

use App\Models\InternalStaffOrder;
use App\Models\User;
use App\Mail\InternalStaffOrderRejectedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInternalStaffOrderRejectedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public InternalStaffOrder $order,
        public User $user,
        public string $rejectedByName,
        public string $rejectionReason,
        public string $commandName
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send internal staff order rejected email: user has no email', [
                    'order_id' => $this->order->id,
                    'user_id' => $this->user->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(
                new InternalStaffOrderRejectedMail(
                    $this->order,
                    $this->user,
                    $this->rejectedByName,
                    $this->rejectionReason,
                    $this->commandName
                )
            );

            Log::info('Internal staff order rejected email sent', [
                'order_id' => $this->order->id,
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send internal staff order rejected email', [
                'order_id' => $this->order->id,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
