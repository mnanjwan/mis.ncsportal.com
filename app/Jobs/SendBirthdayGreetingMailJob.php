<?php

namespace App\Jobs;

use App\Models\Officer;
use App\Mail\BirthdayGreetingMail;
use App\Services\NotificationService;
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

        // In-app notification (only if officer has a linked user)
        $user = $this->officer->user;
        if ($user) {
            try {
                app(NotificationService::class)->notify(
                    $user,
                    'birthday_greeting',
                    'Happy Birthday!',
                    'Wishing you a wonderful birthday filled with joy, good health, and success. Thank you for your dedication and service.',
                    'officer',
                    $this->officer->id,
                    false // do not send extra email; we already sent the birthday email
                );
            } catch (\Exception $e) {
                Log::warning('Failed to create birthday in-app notification', [
                    'officer_id' => $this->officer->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
