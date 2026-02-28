<?php

namespace App\Observers;

use App\Jobs\SendExpoPushNotificationJob;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Ensures every in-app notification also goes to the mobile app (Expo Push).
 * NCS_EMPLOYEE_MOBILE_APP_README.md: all notifications from the web must drop on the app.
 */
class NotificationObserver
{
    public function created(Notification $notification): void
    {
        $notification->load('user');
        $user = $notification->user;

        if (!$user || empty($user->push_token) || !str_starts_with($user->push_token, 'ExponentPushToken[')) {
            return;
        }

        try {
            SendExpoPushNotificationJob::dispatch($notification);
            Log::info('Expo push job dispatched (observer)', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch Expo push job (observer)', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
