# Notification System Documentation

## Overview

The application includes a comprehensive notification system that sends both in-app notifications and email notifications for important events. All email notifications are processed asynchronously using Laravel queues to ensure timely delivery without blocking user requests.

## Architecture

### Components

1. **NotificationService** (`app/Services/NotificationService.php`)
   - Centralized service for creating notifications
   - Handles both in-app and email notifications
   - Provides helper methods for common notification types

2. **SendNotificationEmailJob** (`app/Jobs/SendNotificationEmailJob.php`)
   - Background job for sending email notifications
   - Includes retry logic (3 attempts with exponential backoff)
   - Processes emails asynchronously via queue

3. **NotificationMail** (`app/Mail/NotificationMail.php`)
   - Mailable class for email notifications
   - Uses a standardized email template

4. **Notification Model** (`app/Models/Notification.php`)
   - Stores in-app notifications in the database
   - Links to users and entities (officers, requests, etc.)

## Usage

### Basic Notification

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

// Notify a single user
$notificationService->notify(
    $user,
    'notification_type',
    'Title',
    'Message content',
    'entity_type', // optional: 'officer', 'leave_application', etc.
    $entityId,     // optional: ID of the related entity
    true           // send email (default: true)
);
```

### Notify Multiple Users

```php
$notificationService->notifyMany(
    [$user1, $user2, $user3],
    'notification_type',
    'Title',
    'Message'
);
```

### Notify by Role

```php
$notificationService->notifyByRole(
    'Establishment',
    'notification_type',
    'Title',
    'Message'
);
```

### Helper Methods

The NotificationService includes helper methods for common scenarios:

- `notifyRecruitCreated()` - Notify about new recruit
- `notifyAppointmentAssigned()` - Notify about appointment number assignment
- `notifyRecruitsReadyForTraining()` - Notify TRADOC about new recruits
- `notifyTrainingResultsUploaded()` - Notify Establishment about training results
- `notifyServiceNumberAssigned()` - Notify about service number assignment
- `notifyServiceNumbersForEmail()` - Notify ICT about service numbers ready for email

## Notification Types

### Current Notification Types

- `recruit_created` - New recruit added
- `appointment_assigned` - Appointment number assigned
- `recruits_ready_training` - Recruits ready for training
- `training_results_uploaded` - Training results uploaded
- `service_number_assigned` - Service number assigned
- `service_numbers_ready_email` - Service numbers ready for email creation
- `account_change_approved` - Account change request approved
- `account_change_rejected` - Account change request rejected

## Adding Notifications to Controllers

### Example: Approval Workflow

```php
use App\Services\NotificationService;

public function approve(Request $request, $id)
{
    // ... approval logic ...
    
    // Notify the requester
    $notificationService = app(NotificationService::class);
    $notificationService->notify(
        $requesterUser,
        'request_approved',
        'Request Approved',
        "Your request has been approved.",
        'request_type',
        $requestId
    );
    
    return redirect()->back()->with('success', 'Request approved.');
}
```

### Example: Status Change

```php
public function updateStatus(Request $request, $id)
{
    // ... status update logic ...
    
    // Notify relevant users
    $notificationService = app(NotificationService::class);
    
    // Notify by role
    $notificationService->notifyByRole(
        'HRD',
        'status_changed',
        'Status Updated',
        "The status has been updated to: {$newStatus}"
    );
    
    return redirect()->back()->with('success', 'Status updated.');
}
```

## Queue Configuration

### Setup

1. Ensure queue connection is configured in `.env`:
   ```
   QUEUE_CONNECTION=database
   ```

2. Create jobs table (if using database queue):
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

3. Start queue worker:
   ```bash
   php artisan queue:work
   ```

   Or use supervisor for production:
   ```ini
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/path/to/worker.log
   ```

### Queue Monitoring

Monitor failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```

## Email Template

Email notifications use the template at `resources/views/emails/notification.blade.php`. Customize this template to match your branding.

## In-App Notifications

In-app notifications are displayed in the notification panel accessible via the notification icon in the sidebar. The notification system is already integrated with the existing notification API endpoint.

## Best Practices

1. **Always use the NotificationService** - Don't create notifications directly
2. **Use background jobs for emails** - Never send emails synchronously
3. **Include entity references** - Link notifications to related entities when possible
4. **Use descriptive types** - Use clear, consistent notification type names
5. **Notify relevant users only** - Don't spam users with unnecessary notifications
6. **Handle errors gracefully** - Email failures shouldn't break the main workflow

## Controllers with Notifications

### Currently Implemented

- **EstablishmentController**
  - Recruit creation → Notifies TRADOC
  - Appointment number assignment → Notifies TRADOC
  - Service number assignment → Notifies ICT and officers

- **TRADOCController**
  - Training results upload → Notifies Establishment

- **AccountChangeRequestController**
  - Request approval → Notifies officer
  - Request rejection → Notifies officer

### To Be Implemented

- **LeaveApplicationController** - Leave approvals/rejections
- **PassApplicationController** - Pass approvals/rejections
- **NextOfKinChangeRequestController** - Change request approvals
- **ICTController** - Email creation completion
- **RetirementController** - Retirement alerts
- **PromotionController** - Promotion notifications
- **PostingController** - Posting assignments

## Testing

Test notifications in development:

```php
// In a controller or tinker
$notificationService = app(NotificationService::class);
$user = User::first();
$notificationService->notify(
    $user,
    'test',
    'Test Notification',
    'This is a test notification'
);
```

Check the notifications table:
```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
```

Check the jobs table for queued emails:
```sql
SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10;
```

## Troubleshooting

### Emails not sending

1. Check queue worker is running: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Check logs: `storage/logs/laravel.log`
4. Verify email configuration in `.env`

### Notifications not appearing

1. Check notifications table for entries
2. Verify user_id is correct
3. Check notification API endpoint is working
4. Verify frontend is polling/displaying notifications

