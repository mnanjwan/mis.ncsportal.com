<?php

namespace App\Jobs;

use App\Models\StaffOrder;
use App\Models\User;
use App\Mail\StaffOrderCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendStaffOrderCreatedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public StaffOrder $staffOrder,
        public User $user,
        public string $fromCommandName,
        public string $toCommandName
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->user->email) {
                Log::warning('Cannot send staff order created email: user has no email', [
                    'staff_order_id' => $this->staffOrder->id,
                    'user_id' => $this->user->id,
                ]);
                return;
            }

            Mail::to($this->user->email)->send(
                new StaffOrderCreatedMail(
                    $this->staffOrder,
                    $this->user,
                    $this->fromCommandName,
                    $this->toCommandName
                )
            );

            Log::info('Staff order created email sent', [
                'staff_order_id' => $this->staffOrder->id,
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send staff order created email', [
                'staff_order_id' => $this->staffOrder->id,
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

