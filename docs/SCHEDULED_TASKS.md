# Scheduled Tasks Reference

This document lists all scheduled tasks configured in the application.

## Task Schedule Overview

| Task | Frequency | Time | Command/Job |
|------|-----------|------|-------------|
| Query Expiration Check | Every 3 minutes | - | `queries:check-expired` |
| Leave Expiry Alerts | Hourly | - | `SendLeaveExpiryAlertsJob` |
| Pass Expiry Alerts | Hourly | - | `SendPassExpiryAlertsJob` |
| Retirement Checks | Daily | 08:00 | `CheckRetirementJob` |
| Retirement Alerts | Daily | 08:00 | `retirement:check-alerts` |
| Pre-Retirement Status | Daily | - | `RetirementService::checkAndActivatePreRetirementStatus()` |
| Emolument Timeline Extension | Daily | 08:00 | `emolument:extend-timeline` |
| APER Timeline Management | Daily | 08:00 | `aper:manage-timeline` |

## Detailed Task Descriptions

### 1. Query Expiration Check
- **Command**: `php artisan queries:check-expired`
- **Frequency**: Every 3 minutes
- **Purpose**: Automatically expires queries that have passed their response deadline
- **Actions**:
  - Finds queries with `status = 'PENDING_RESPONSE'` and `response_deadline <= now()`
  - Updates status to `ACCEPTED`
  - Sets `reviewed_at` timestamp
  - Sends notification to officer
  - Notifies authorities (Area Controller, DC Admin, HRD)

### 2. Leave Expiry Alerts
- **Job**: `SendLeaveExpiryAlertsJob`
- **Frequency**: Hourly
- **Purpose**: Sends alerts for leave applications approaching expiry

### 3. Pass Expiry Alerts
- **Job**: `SendPassExpiryAlertsJob`
- **Frequency**: Hourly
- **Purpose**: Sends alerts for pass applications approaching expiry

### 4. Retirement Checks
- **Job**: `CheckRetirementJob`
- **Frequency**: Daily
- **Purpose**: Processes retirement-related checks

### 5. Retirement Alerts
- **Command**: `php artisan retirement:check-alerts`
- **Frequency**: Daily at 08:00
- **Purpose**: Sends alerts for officers approaching retirement (3 months before)

### 6. Pre-Retirement Status
- **Service**: `RetirementService::checkAndActivatePreRetirementStatus()`
- **Frequency**: Daily
- **Purpose**: Activates pre-retirement status for eligible officers

### 7. Emolument Timeline Extension
- **Command**: `php artisan emolument:extend-timeline`
- **Frequency**: Daily at 08:00
- **Purpose**: Automatically extends emolument timelines as configured

### 8. APER Timeline Management
- **Command**: `php artisan aper:manage-timeline`
- **Frequency**: Daily at 08:00
- **Purpose**: Manages APER timelines, deactivates expired timelines, sends notifications

## Testing Scheduled Tasks

### List All Scheduled Tasks
```bash
php artisan schedule:list
```

### Run Scheduler Manually
```bash
php artisan schedule:run
```

### Test Specific Task
```bash
# Query expiration
php artisan queries:check-expired

# Retirement alerts
php artisan retirement:check-alerts

# Emolument timeline
php artisan emolument:extend-timeline

# APER timeline
php artisan aper:manage-timeline
```

## Monitoring

### Check Task Execution Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Scheduler-specific log (if configured)
tail -f storage/logs/scheduler.log
```

### Verify Task Execution
- Check database records for updates
- Check notifications sent
- Review log entries for task execution

## Troubleshooting

### Task Not Running
1. Verify cron is set up: `crontab -l`
2. Check cron service: `sudo systemctl status cron`
3. Test manually: `php artisan schedule:run`
4. Check logs for errors

### Task Running at Wrong Time
1. Verify server timezone: `date`
2. Check Laravel timezone in `.env`: `APP_TIMEZONE`
3. Verify task schedule in `routes/console.php`

## Adding New Scheduled Tasks

To add a new scheduled task:

1. **Add to `routes/console.php`:**
   ```php
   Schedule::command('your:command')
       ->daily()
       ->at('08:00');
   ```

2. **Or schedule a job:**
   ```php
   Schedule::call(function () {
       YourJob::dispatch();
   })->hourly();
   ```

3. **Test the task:**
   ```bash
   php artisan schedule:list
   php artisan schedule:run
   ```

4. **Verify it's working:**
   - Check logs
   - Verify database updates
   - Test notifications

## Best Practices

1. **Use appropriate frequencies** - Don't run heavy tasks too frequently
2. **Log task execution** - Always log important task operations
3. **Handle errors gracefully** - Use try-catch blocks in scheduled tasks
4. **Test in staging** - Always test scheduled tasks before production
5. **Monitor execution** - Set up alerts for failed tasks
6. **Document tasks** - Keep this document updated when adding new tasks

