# Queue Worker Setup Guide

## Overview

Laravel queue workers need to run continuously to process queued jobs (like email sending). They should **NOT** be run via cron jobs, but rather through a process manager like Supervisor that keeps them running continuously.

## Supervisor Setup (Recommended)

Supervisor is the recommended way to manage queue workers on a VPS/server. It:
- Runs the worker continuously in the background
- Automatically restarts if the worker crashes
- Starts the worker on server boot
- Manages logs and process lifecycle

### Configuration File

Create/edit: `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/nccscportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=nccsc9434
numprocs=1
redirect_stderr=true
stdout_logfile=/home/nccscportal.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

### Key Configuration Options:

- `command`: Full path to artisan with queue:work command
- `autostart=true`: Start worker when Supervisor starts
- `autorestart=true`: Restart worker if it crashes
- `user`: System user to run the worker (should match your web server user)
- `numprocs=1`: Number of worker processes (1 is usually enough)
- `--sleep=3`: Wait 3 seconds when no jobs available
- `--tries=3`: Retry failed jobs up to 3 times
- `--timeout=90`: Maximum time a job can run (90 seconds)

### Setup Commands

After creating/editing the config file:

```bash
# Read the new configuration
sudo supervisorctl reread

# Update Supervisor with new config
sudo supervisorctl update

# Start the worker
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status laravel-worker:*
```

### Managing the Worker

```bash
# Check status
sudo supervisorctl status laravel-worker:*

# Restart worker (after code changes)
sudo supervisorctl restart laravel-worker:*

# Stop worker
sudo supervisorctl stop laravel-worker:*

# View logs
tail -f /home/nccscportal.com/public_html/storage/logs/worker.log
```

### Verify Supervisor Starts on Boot

```bash
# Check if Supervisor service is enabled
sudo systemctl status supervisor

# Enable Supervisor to start on boot (if not already enabled)
sudo systemctl enable supervisor

# Start Supervisor service
sudo systemctl start supervisor
```

## Why NOT Cron?

**DO NOT use cron for queue workers because:**
- Cron runs tasks at intervals (e.g., every minute)
- Queue workers need to run continuously
- Jobs would only be processed when cron runs, not immediately
- Multiple cron instances could cause conflicts

**Cron IS correct for:**
- Laravel scheduled tasks: `php artisan schedule:run` (runs every minute, then Laravel's scheduler handles timing)
- Periodic maintenance tasks
- Reports, backups, etc.

## Difference Between Queue Worker and Scheduled Tasks

### Queue Worker (Supervisor)
- Runs **continuously**
- Processes jobs as they are queued
- Used for: Email sending, notifications, background processing
- Command: `php artisan queue:work`
- Managed by: Supervisor

### Scheduled Tasks (Cron)
- Runs **at intervals** (every minute)
- Executes scheduled tasks at their specified times
- Used for: Daily reports, cleanup tasks, periodic checks
- Command: `php artisan schedule:run`
- Managed by: Cron (runs every minute)

## Troubleshooting

### Worker Not Processing Jobs

1. **Check if worker is running:**
   ```bash
   sudo supervisorctl status laravel-worker:*
   ```

2. **Check worker logs:**
   ```bash
   tail -f /home/nccscportal.com/public_html/storage/logs/worker.log
   ```

3. **Check if jobs are in queue:**
   ```bash
   php artisan queue:work --once
   ```

4. **Restart worker:**
   ```bash
   sudo supervisorctl restart laravel-worker:*
   ```

### Jobs Not Being Processed

1. **Verify queue connection:**
   Check `.env` file:
   ```
   QUEUE_CONNECTION=database
   ```

2. **Check jobs table exists:**
   ```bash
   php artisan migrate
   ```

3. **Check if jobs are failing:**
   ```bash
   php artisan queue:failed
   ```

### Worker Keeps Restarting

1. **Check logs for errors:**
   ```bash
   tail -f /home/nccscportal.com/public_html/storage/logs/worker.log
   ```

2. **Check Laravel logs:**
   ```bash
   tail -f /home/nccscportal.com/public_html/storage/logs/laravel.log
   ```

3. **Test queue worker manually:**
   ```bash
   php artisan queue:work --once
   ```

## Quick Verification Checklist

- [ ] Supervisor config file exists and is correct
- [ ] Supervisor service is running: `sudo systemctl status supervisor`
- [ ] Worker is running: `sudo supervisorctl status laravel-worker:*`
- [ ] Worker logs exist and show activity
- [ ] Jobs are being processed (check database `jobs` table)
- [ ] Emails are being sent (test by creating a recruit)

## Summary

✅ **Use Supervisor for queue workers** - Runs continuously, auto-restarts, starts on boot
✅ **Use Cron for scheduled tasks** - Runs `php artisan schedule:run` every minute
❌ **Don't use Cron for queue workers** - They need to run continuously, not at intervals

