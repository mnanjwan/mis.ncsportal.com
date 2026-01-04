# Queue Worker Not Processing Jobs - Troubleshooting

## Quick Fix: Process Jobs Manually (Right Now)

If you have jobs waiting and need them processed immediately:

```bash
cd /home/mis.ncsportal.com/public_html
php artisan queue:work --stop-when-empty
```

This will process all pending jobs and exit.

## Check if Jobs are Queued

```bash
php artisan tinker
```

Then:
```php
DB::table('jobs')->count();
// If this shows a number > 0, you have pending jobs
```

## Check if Supervisor Worker is Running

```bash
sudo supervisorctl status laravel-worker:*
```

**If it shows:**
- `RUNNING` - Worker is running, but might not be processing
- `STOPPED` - Worker is stopped, need to start it
- `FATAL` or `EXITED` - Worker crashed, need to restart

## Start/Restart the Worker

```bash
# If stopped, start it
sudo supervisorctl start laravel-worker:*

# If running but not processing, restart it
sudo supervisorctl restart laravel-worker:*

# Check status again
sudo supervisorctl status laravel-worker:*
```

## Check Worker Logs for Errors

```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

Look for errors that might be preventing job processing.

## Check Supervisor Service

```bash
sudo systemctl status supervisor
```

If not running:
```bash
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

## Verify Supervisor Config

Check the config file:
```bash
cat /etc/supervisor/conf.d/laravel-worker.conf
```

Make sure it has:
- `autostart=true`
- `autorestart=true`
- Correct paths

## Reload Supervisor Config

If you made changes:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker:*
```

## Process Jobs Manually While Fixing

If Supervisor isn't working, you can process jobs manually:

```bash
# Process all pending jobs
php artisan queue:work --stop-when-empty

# Or process one job
php artisan queue:work --once
```

## Check for Failed Jobs

```bash
php artisan queue:failed
```

If jobs failed, check why:
```bash
php artisan queue:failed
# Note the job ID, then:
php artisan queue:retry <job-id>
```

## Common Issues

### Issue 1: Worker Not Running
**Solution:**
```bash
sudo supervisorctl start laravel-worker:*
```

### Issue 2: Worker Crashed
**Check logs:**
```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

**Restart:**
```bash
sudo supervisorctl restart laravel-worker:*
```

### Issue 3: Supervisor Not Running
**Solution:**
```bash
sudo systemctl start supervisor
sudo supervisorctl start laravel-worker:*
```

### Issue 4: Jobs Stuck in Queue
**Process manually:**
```bash
php artisan queue:work --stop-when-empty -v
```

The `-v` flag shows what's happening.

## Quick Diagnostic Commands

Run these to diagnose:

```bash
# 1. Check if jobs are queued
php artisan tinker
>>> DB::table('jobs')->count();

# 2. Check if worker is running
sudo supervisorctl status laravel-worker:*

# 3. Check Supervisor service
sudo systemctl status supervisor

# 4. Check worker logs
tail -20 /home/mis.ncsportal.com/public_html/storage/logs/worker.log

# 5. Process jobs manually to test
php artisan queue:work --stop-when-empty -v
```

