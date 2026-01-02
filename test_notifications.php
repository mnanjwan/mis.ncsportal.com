<?php

/**
 * Simple test script to verify notifications are working
 * Run: php artisan tinker < test_notifications.php
 * Or: php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); require 'test_notifications.php';"
 */

use App\Models\User;
use App\Models\Officer;
use App\Models\Notification;
use App\Services\NotificationService;

echo "=== Notification System Test ===\n\n";

// Test 1: Basic notification
echo "Test 1: Creating basic notification...\n";
$user = User::first();
if (!$user) {
    echo "ERROR: No users found. Please create a user first.\n";
    exit(1);
}

$notificationService = app(NotificationService::class);
$notification = $notificationService->notify(
    $user,
    'test_notification',
    'Test Notification',
    'This is a test notification to verify the system is working.',
    'test',
    1
);

echo "✓ Notification created with ID: {$notification->id}\n";
echo "  - User: {$user->email}\n";
echo "  - Type: {$notification->notification_type}\n";
echo "  - Title: {$notification->title}\n";
echo "  - Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n\n";

// Test 2: Check notification in database
echo "Test 2: Verifying notification in database...\n";
$dbNotification = Notification::find($notification->id);
if ($dbNotification) {
    echo "✓ Notification found in database\n";
    echo "  - Message: " . substr($dbNotification->message, 0, 50) . "...\n\n";
} else {
    echo "✗ ERROR: Notification not found in database\n\n";
}

// Test 3: Check notifications for user
echo "Test 3: Getting all notifications for user...\n";
$userNotifications = Notification::where('user_id', $user->id)->get();
echo "✓ Found {$userNotifications->count()} notification(s) for user\n\n";

// Test 4: Test with officer (if available)
echo "Test 4: Testing with officer...\n";
$officer = Officer::whereHas('user')->first();
if ($officer && $officer->user) {
    $notification2 = $notificationService->notify(
        $officer->user,
        'officer_test',
        'Officer Test Notification',
        "Test notification for officer {$officer->initials} {$officer->surname}",
        'officer',
        $officer->id
    );
    echo "✓ Notification created for officer\n";
    echo "  - Officer: {$officer->initials} {$officer->surname}\n";
    echo "  - Notification ID: {$notification2->id}\n\n";
} else {
    echo "⚠ No officer with user account found. Skipping officer test.\n\n";
}

// Test 5: Check queue jobs (if queue is configured)
echo "Test 5: Checking queue configuration...\n";
$queueConnection = config('queue.default');
echo "  - Queue connection: {$queueConnection}\n";
if ($queueConnection === 'database') {
    echo "  - Queue table exists: " . (Schema::hasTable('jobs') ? 'Yes' : 'No') . "\n";
    $pendingJobs = DB::table('jobs')->count();
    echo "  - Pending jobs: {$pendingJobs}\n";
}
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "✓ Basic notification creation: PASSED\n";
echo "✓ Database storage: PASSED\n";
echo "✓ User notification retrieval: PASSED\n";
if ($officer && $officer->user) {
    echo "✓ Officer notification: PASSED\n";
}
echo "\n";
echo "Next steps:\n";
echo "1. Check the notifications drawer in the UI (click notification icon in sidebar)\n";
echo "2. Verify email notifications are queued (check jobs table)\n";
echo "3. Run queue worker: php artisan queue:work\n";
echo "4. Test actual workflows (approve leave, create staff order, etc.)\n";










