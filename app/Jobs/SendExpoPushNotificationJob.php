<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Send a push notification to the mobile app via Expo Push API.
 * Used by NotificationService so every in-app notification also reaches mobile (NCS_EMPLOYEE_MOBILE_APP_README.md).
 */
class SendExpoPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 120, 600];

    public const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Notification $notification
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = $this->notification->fresh(['user']);
        $user = $notification->user;

        if (!$user || empty($user->push_token)) {
            Log::debug('Expo push skipped: no user or push_token', [
                'notification_id' => $notification->id,
            ]);
            return;
        }

        $token = $user->push_token;
        if (!str_starts_with($token, 'ExponentPushToken[')) {
            Log::debug('Expo push skipped: invalid token format', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);
            return;
        }

        $payload = [
            'to' => $token,
            'sound' => 'default',
            'title' => $notification->title,
            'body' => $notification->message,
            'data' => [
                'notification_id' => $notification->id,
                'notification_type' => $notification->notification_type,
                'entity_type' => $notification->entity_type,
                'entity_id' => $notification->entity_id,
            ],
            'priority' => 'high',
            'channelId' => 'default',
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->post(self::EXPO_PUSH_URL, $payload);

            if (!$response->successful()) {
                Log::warning('Expo push API non-2xx', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Expo push failed: ' . $response->status());
            }

            $data = $response->json();
            if (isset($data['data']['status']) && $data['data']['status'] === 'error') {
                Log::warning('Expo push ticket error', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'ticket' => $data['data'],
                ]);
            }

            Log::info('Expo push sent', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Expo push failed', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
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
        Log::error('Expo push job failed after retries', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
