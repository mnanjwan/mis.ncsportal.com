<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Mail\BirthdayGreetingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBirthdayGreetingMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [60, 300, 900];

    public function __construct(
        public Officer $officer,
        public ?string $officerName = null
    ) {
        $this->officerName = $officerName ?? trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
    }

    public function handle(): void
    {
        $email = $this->officer->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Cannot send birthday greeting: officer has no valid email', [
                'officer_id' => $this->officer->id,
            ]);
            return;
        }

        try {
            Mail::to($email)->send(new BirthdayGreetingMail($this->officer, $this->officerName));

            Log::info('Birthday greeting email sent', [
                'officer_id' => $this->officer->id,
                'email' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send birthday greeting email', [
                'officer_id' => $this->officer->id,
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
