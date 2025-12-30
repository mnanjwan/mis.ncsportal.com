# Cron Jobs Setup Guide

This guide explains how to keep all Laravel scheduled tasks running.

## Overview

Laravel's task scheduler requires a single cron entry that runs every minute. This cron job calls `php artisan schedule:run`, which then executes all scheduled tasks defined in `routes/console.php` at their specified times.

## Current Scheduled Tasks

The following tasks are configured in `routes/console.php`:

1. **Retirement Checks** - Daily at 08:00
2. **Retirement Alerts** - Daily at 08:00
3. **Pre-Retirement Status** - Daily
4. **Leave Expiry Alerts** - Hourly
5. **Pass Expiry Alerts** - Hourly
6. **Emolument Timeline Extension** - Daily at 08:00
7. **APER Timeline Management** - Daily at 08:00
8. **Query Expiration Check** - Every 3 minutes

## Setup Instructions

### For Production Server (Linux/Unix)

1. **Open the crontab editor:**
   ```bash
   crontab -e
   ```

2. **Add the following line:**
   ```bash
   * * * * * cd /path/to/pisportal && php artisan schedule:run >> /dev/null 2>&1
   ```

   Replace `/path/to/pisportal` with your actual project path.

   **Example:**
   ```bash
   * * * * * cd /var/www/pisportal && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Save and exit** (in vim: press `Esc`, type `:wq`, press Enter)

4. **Verify the cron job is set:**
   ```bash
   crontab -l
   ```

5. **Check if cron service is running:**
   ```bash
   # For systemd (Ubuntu/Debian)
   sudo systemctl status cron
   
   # For systemd (CentOS/RHEL)
   sudo systemctl status crond
   ```

### For Local Development (macOS/Linux)

1. **Open the crontab editor:**
   ```bash
   crontab -e
   ```

2. **Add the following line:**
   ```bash
   * * * * * cd /Users/macintosh/Developer/pisportal && php artisan schedule:run >> /dev/null 2>&1
   ```

   Adjust the path to match your local project directory.

3. **For macOS, ensure cron has Full Disk Access:**
   - Go to System Preferences → Security & Privacy → Privacy → Full Disk Access
   - Add `/usr/sbin/cron` if it's not already there

### Alternative: Using Laravel Scheduler Service (Production)

For production environments, you can use a process manager like Supervisor to keep the scheduler running:

1. **Install Supervisor:**
   ```bash
   sudo apt-get install supervisor  # Ubuntu/Debian
   sudo yum install supervisor      # CentOS/RHEL
   ```

2. **Create supervisor config file:**
   ```bash
   sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf
   ```

3. **Add the following configuration:**
   ```ini
   [program:laravel-scheduler]
   process_name=%(program_name)s
   command=/usr/bin/php /path/to/pisportal/artisan schedule:work
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/path/to/pisportal/storage/logs/scheduler.log
   ```

4. **Update Supervisor and start:**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-scheduler
   ```

## Testing the Scheduler

### Test if cron is working:

1. **Manually run the scheduler:**
   ```bash
   php artisan schedule:run
   ```

2. **List all scheduled tasks:**
   ```bash
   php artisan schedule:list
   ```

3. **Test a specific task:**
   ```bash
   php artisan queries:check-expired
   php artisan retirement:check-alerts
   ```

### Monitor scheduler execution:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check cron logs (Linux):**
   ```bash
   # Ubuntu/Debian
   grep CRON /var/log/syslog
   
   # CentOS/RHEL
   grep CRON /var/log/cron
   ```

3. **Check if tasks are running:**
   ```bash
   # Check database for updated records
   # Check notifications sent
   # Check log files for scheduled task entries
   ```

## Troubleshooting

### Cron job not running:

1. **Check cron service status:**
   ```bash
   sudo systemctl status cron
   ```

2. **Check cron logs for errors:**
   ```bash
   grep CRON /var/log/syslog
   ```

3. **Verify PHP path:**
   ```bash
   which php
   # Use full path in crontab if needed
   ```

4. **Check file permissions:**
   ```bash
   ls -la /path/to/pisportal/artisan
   # Should be executable
   ```

5. **Test with verbose output:**
   ```bash
   # Temporarily change crontab to log output
   * * * * * cd /path/to/pisportal && php artisan schedule:run >> /tmp/scheduler.log 2>&1
   ```

### Tasks not executing at expected times:

1. **Verify server timezone:**
   ```bash
   date
   # Should match your application timezone
   ```

2. **Check Laravel timezone in `.env`:**
   ```
   APP_TIMEZONE=Africa/Lagos
   ```

3. **Verify task schedule in code:**
   ```bash
   php artisan schedule:list
   ```

### Permission issues:

1. **Ensure proper file ownership:**
   ```bash
   sudo chown -R www-data:www-data /path/to/pisportal
   sudo chmod -R 755 /path/to/pisportal
   ```

2. **Ensure storage is writable:**
   ```bash
   sudo chmod -R 775 storage bootstrap/cache
   ```

## Production Best Practices

1. **Use absolute paths** in crontab
2. **Log scheduler output** for debugging:
   ```bash
   * * * * * cd /path/to/pisportal && php artisan schedule:run >> /path/to/pisportal/storage/logs/scheduler.log 2>&1
   ```
3. **Monitor scheduler execution** regularly
4. **Set up alerts** for failed scheduled tasks
5. **Use process managers** (Supervisor) for critical applications
6. **Test in staging** before deploying to production

## Quick Setup Script

Create a setup script `setup-cron.sh`:

```bash
#!/bin/bash

PROJECT_PATH="/var/www/pisportal"
CRON_CMD="* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "Cron job already exists!"
    crontab -l
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    echo "Cron job added successfully!"
    crontab -l
fi
```

Make it executable and run:
```bash
chmod +x setup-cron.sh
sudo ./setup-cron.sh
```

## Verification Checklist

- [ ] Cron service is running
- [ ] Cron job is added to crontab
- [ ] `php artisan schedule:list` shows all tasks
- [ ] `php artisan schedule:run` executes without errors
- [ ] Tasks execute at their scheduled times
- [ ] Logs show successful task execution
- [ ] Database records are updated as expected
- [ ] Notifications are sent when tasks run

## Additional Resources

- [Laravel Task Scheduling Documentation](https://laravel.com/docs/scheduling)
- [Linux Cron Guide](https://www.cyberciti.biz/faq/how-do-i-add-jobs-to-cron-under-linux-or-unix-oses/)

