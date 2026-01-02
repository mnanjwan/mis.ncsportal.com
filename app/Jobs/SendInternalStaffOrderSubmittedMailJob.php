<?php

namespace App\Jobs;

use App\Models\InternalStaffOrder;
use App\Models\User;
use App\Mail\InternalStaffOrderSubmittedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInternalStaffOrderSubmittedMailJob implements ShouldQueue
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
        public string $commandName,
        public string $preparedByName,
        public string $officerName,
        public string $serviceNumber,
        public string $targetUnit,
        public string $targetRole
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send internal staff order submitted email: user has no email', [
                    'order_id' => $this->order->id,
                    'user_id' => $this->user->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(
                new InternalStaffOrderSubmittedMail(
                    $this->order,
                    $this->user,
                    $this->commandName,
                    $this->preparedByName,
                    $this->officerName,
                    $this->serviceNumber,
                    $this->targetUnit,
                    $this->targetRole
                )
            );

            Log::info('Internal staff order submitted email sent', [
                'order_id' => $this->order->id,
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send internal staff order submitted email', [
                'order_id' => $this->order->id,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
