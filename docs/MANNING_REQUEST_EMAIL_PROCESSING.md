# Manning Request Email Processing

## Overview
This document outlines how emails are sent for all manning request workflow functions.

## Current Implementation

### Email Sending Strategy
**All emails now use queued jobs by default** with synchronous fallback for development/testing.

### Main Notification Method (`notify()`)
- **Primary**: Uses `SendNotificationEmailJob` (queued)
- **Fallback**: Synchronous sending if queue fails
- **Benefits**: 
  - Non-blocking requests
  - Better error handling and retries
  - Handles bulk emails efficiently

## Manning Request Email Points

### 1. **Manning Request Submitted** (`notifyManningRequestSubmitted`)
- **Recipients**: Area Controllers, HRD Users, DC Admins
- **Method**: `notifyMany()` → `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs

### 2. **Manning Request Approved** (`notifyManningRequestApproved`)
- **Recipients**: Staff Officer (request creator)
- **Method**: `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs

### 3. **Manning Request Approved to HRD** (`notifyManningRequestApprovedToHrd`)
- **Recipients**: HRD Users
- **Method**: `notifyMany()` → `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs

### 4. **Manning Request Rejected** (`notifyManningRequestRejected`)
- **Recipients**: Staff Officer (request creator)
- **Method**: `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs

### 5. **Manning Request Fulfilled** (`notifyManningRequestFulfilled`)
- **Recipients**: Staff Officer (request creator)
- **Method**: `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs

### 6. **Officer Release Letter** (`notifyCommandOfficerRelease`)
- **Recipients**: Staff Officers, Area Controllers, DC Admins (FROM command)
- **Method**: `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs
- **Note**: Sent BEFORE documentation/posting

### 7. **Officer Posted** (`notifyOfficerPosted`)
- **Recipients**: Officer (being posted)
- **Method**: `notify()` → `SendNotificationEmailJob`
- **Status**: ✅ Uses Jobs
- **Note**: Sent AFTER release letter and posting

## Workflow Email Sequence

When a draft is published:

1. **Release Letters** (BEFORE posting)
   - For each officer being posted
   - Sent to FROM command (Staff Officers, Area Controllers, DC Admins)
   - Uses: `notifyCommandOfficerRelease()` → `notify()` → `SendNotificationEmailJob`

2. **Posting Notifications** (AFTER posting)
   - For each officer being posted
   - Sent to the officer
   - Uses: `notifyOfficerPosted()` → `notify()` → `SendNotificationEmailJob`

3. **Fulfillment Notification** (If request fully fulfilled)
   - Sent to Staff Officer
   - Uses: `notifyManningRequestFulfilled()` → `notify()` → `SendNotificationEmailJob`

## Job Configuration

### SendNotificationEmailJob
- **Queue**: Default queue
- **Retries**: 3 attempts
- **Backoff**: 60s, 300s, 900s (1min, 5min, 15min)
- **Error Handling**: Logs errors and re-throws for retry

## Benefits of Using Jobs

1. **Non-blocking**: Requests complete quickly even with many emails
2. **Scalability**: Can handle hundreds of emails without timeout
3. **Reliability**: Automatic retries on failure
4. **Performance**: Background processing doesn't slow down user experience
5. **Error Handling**: Better tracking and recovery

## Queue Worker Requirements

For emails to be sent, ensure queue worker is running:

```bash
php artisan queue:work
```

Or with supervisor for production:
```bash
php artisan queue:work --tries=3 --timeout=60
```

## Testing

To test email sending:
1. Ensure queue worker is running
2. Trigger manning request workflow
3. Check queue jobs: `php artisan queue:work --once`
4. Check logs: `storage/logs/laravel.log`

## Summary

✅ **All manning request email points use queued jobs**
✅ **Non-blocking email processing**
✅ **Automatic retries on failure**
✅ **Proper error handling and logging**

