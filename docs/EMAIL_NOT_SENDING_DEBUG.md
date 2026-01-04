# Email Not Sending - Debugging Guide

## Step 1: Check if Jobs are in Queue

```bash
cd /home/mis.ncsportal.com/public_html
php artisan tinker
```

Then:
```php
DB::table('jobs')->count();
// If > 0, jobs are queued
DB::table('jobs')->get();
// See what jobs are waiting
```

## Step 2: Check Worker Logs for Errors

```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

Look for:
- Error messages
- Failed jobs
- Email sending errors

## Step 3: Check Laravel Logs

```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/laravel.log | grep -i "mail\|email\|onboarding"
```

Look for:
- Email sending errors
- SMTP errors
- Mail configuration issues

## Step 4: Check Failed Jobs

```bash
php artisan queue:failed
```

If jobs failed, see why:
```bash
php artisan queue:failed
# Note the job ID, then check details
```

## Step 5: Test Email Configuration

```bash
php artisan tinker
```

```php
// Test if mail can be sent
Mail::raw('Test email', function($msg) {
    $msg->to('your-email@example.com')->subject('Test');
});
```

## Step 6: Check Mail Configuration

```bash
grep -i "MAIL_" /home/mis.ncsportal.com/public_html/.env
```

Should show:
- MAIL_MAILER=smtp (or your mailer)
- MAIL_HOST=...
- MAIL_PORT=...
- MAIL_USERNAME=...
- MAIL_PASSWORD=...
- MAIL_FROM_ADDRESS=...
- MAIL_FROM_NAME=...

## Step 7: Process Jobs Manually with Verbose Output

```bash
php artisan queue:work --stop-when-empty -v
```

This will show you exactly what's happening with each job.

## Step 8: Check if Worker is Actually Processing

Watch the worker log in real-time:

```bash
tail -f /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

Then create a test recruit or send an onboarding link. You should see job processing activity.

## Common Issues

### Issue 1: Jobs Stuck in Queue
**Check:**
```bash
php artisan tinker
>>> DB::table('jobs')->count();
```

**Solution:** Process manually:
```bash
php artisan queue:work --stop-when-empty -v
```

### Issue 2: Jobs Failing Silently
**Check:**
```bash
php artisan queue:failed
```

**Solution:** Check why they failed and fix the issue.

### Issue 3: Mail Configuration Wrong
**Check:**
```bash
grep MAIL_ /home/mis.ncsportal.com/public_html/.env
```

**Solution:** Verify SMTP credentials are correct.

### Issue 4: Worker Not Processing Jobs
**Check worker logs:**
```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

**Solution:** Restart worker:
```bash
sudo supervisorctl restart laravel-worker:*
```

## Quick Diagnostic Commands

Run these in order:

```bash
# 1. Check if jobs are queued
php artisan tinker
>>> DB::table('jobs')->count();

# 2. Check worker logs
tail -50 storage/logs/worker.log

# 3. Check Laravel logs for email errors
tail -50 storage/logs/laravel.log | grep -i mail

# 4. Check failed jobs
php artisan queue:failed

# 5. Process jobs manually to see errors
php artisan queue:work --stop-when-empty -v

# 6. Check mail config
grep MAIL_ .env
```

