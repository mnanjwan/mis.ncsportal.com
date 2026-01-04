# Queue Worker Testing Guide

## Quick Test Steps

### 1. Check if Queue Worker is Running

**If using Supervisor:**
```bash
sudo supervisorctl status laravel-worker:*
```

Should show: `RUNNING`

**If using Cron:**
```bash
# Check the log file
tail -f /home/mis.ncsportal.com/public_html/storage/logs/queue-cron.log
```

Wait 1 minute and check if new entries appear.

### 2. Check if Jobs Table Exists

```bash
cd /home/mis.ncsportal.com/public_html
php artisan tinker
```

Then in tinker:
```php
DB::table('jobs')->count();
```

If table doesn't exist:
```bash
php artisan migrate
```

### 3. Test by Creating a Recruit (Real Test)

1. Go to your application: `/establishment/new-recruits`
2. Create a new recruit with a valid email address
3. The onboarding link email job should be queued

### 4. Check if Job was Queued

```bash
php artisan tinker
```

```php
// Count jobs in queue
DB::table('jobs')->count();

// View queued jobs
DB::table('jobs')->get();
```

### 5. Wait and Check if Job was Processed

**If using Supervisor:**
- Wait 5-10 seconds
- Check worker log: `tail -f /home/mis.ncsportal.com/public_html/storage/logs/worker.log`

**If using Cron:**
- Wait 1 minute (for cron to run)
- Check cron log: `tail -f /home/mis.ncsportal.com/public_html/storage/logs/queue-cron.log`

**Check Laravel logs:**
```bash
tail -f /home/mis.ncsportal.com/public_html/storage/logs/laravel.log | grep -i "recruit onboarding email"
```

### 6. Verify Job was Processed

```bash
php artisan tinker
```

```php
// Should be 0 if job was processed
DB::table('jobs')->count();

// Check if email was sent (check Laravel logs or check inbox)
```

### 7. Check for Failed Jobs

```bash
php artisan queue:failed
```

If jobs failed, view details:
```bash
php artisan queue:failed-table
php artisan queue:failed
```

## Manual Queue Worker Test (One-Time)

Test if queue worker can process jobs manually:

```bash
cd /home/mis.ncsportal.com/public_html
php artisan queue:work --stop-when-empty -v
```

This will:
- Process all pending jobs
- Show verbose output
- Exit when queue is empty

If you see jobs being processed, the worker is working!

## Test Email Sending Directly

Test if emails can be sent (bypassing queue):

```bash
php artisan tinker
```

```php
// Get a recruit
$recruit = \App\Models\Officer::whereNotNull('email')->first();

// Test sending email directly
Mail::to($recruit->email)->send(new \App\Mail\RecruitOnboardingLinkMail(
    'https://test.com/onboarding?token=test123',
    'Test Name',
    $recruit->email
));
```

This tests if email configuration is working.

## Common Issues and Solutions

### Issue: Jobs stay in queue

**Check:**
1. Is queue worker running? (`supervisorctl status` or check cron log)
2. Are there errors in logs?
3. Is QUEUE_CONNECTION set to 'database' in .env?

**Solution:**
```bash
# Check queue connection
grep QUEUE_CONNECTION /home/mis.ncsportal.com/public_html/.env

# Should show: QUEUE_CONNECTION=database
```

### Issue: Jobs table doesn't exist

**Solution:**
```bash
php artisan queue:table
php artisan migrate
```

### Issue: Queue worker not processing

**Check logs:**
```bash
# Supervisor log
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log

# Cron log (if using cron)
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/queue-cron.log

# Laravel log
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
```

### Issue: Emails not sending

**Test email config:**
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('your-email@example.com')->subject('Test'); });
```

Check your email inbox.

## Complete Test Checklist

- [ ] Queue worker is running (Supervisor or Cron)
- [ ] Jobs table exists (`php artisan migrate`)
- [ ] QUEUE_CONNECTION=database in .env
- [ ] Created a recruit → job appears in jobs table
- [ ] Waited for processing → job disappears from jobs table
- [ ] Email received (check inbox)
- [ ] No errors in logs
- [ ] No failed jobs (`php artisan queue:failed`)

## Quick Test Command

Run this one-liner to test everything:

```bash
cd /home/mis.ncsportal.com/public_html && \
php artisan queue:work --stop-when-empty -v && \
echo "Queue processed. Check: php artisan tinker -> DB::table('jobs')->count();"
```

This processes all pending jobs and shows you what happened.

