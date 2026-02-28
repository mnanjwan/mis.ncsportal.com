# VPS Cron Job Setup Guide

## Quick Setup Steps

### 1. SSH into Your VPS
```bash
ssh your-username@your-vps-ip
```

### 2. Navigate to Your Project
```bash
cd /path/to/pisportal
# Replace with your actual project path, e.g.:
# cd /var/www/pisportal
# or
# cd /home/your-username/pisportal
```

### 3. Find Your PHP Path
```bash
which php
# Common paths: /usr/bin/php, /usr/local/bin/php
```

### 4. Open Crontab Editor
```bash
crontab -e
```

### 5. Add the Cron Job
Add this line (replace paths with your actual paths):
```bash
* * * * * cd /path/to/pisportal && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Example with actual paths:**
```bash
* * * * * cd /var/www/pisportal && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**For nccscportal.com (Current Setup):**
```bash
* * * * * /usr/bin/php /home/nccscportal.com/public_html/artisan schedule:run >> /home/nccscportal.com/public_html/storage/logs/scheduler.log 2>&1
```

**Note:** Your current setup is working, but consider completing the log path for better debugging:
- Current: `>> /home/nccscportal.com/public_html/storage/log`
- Recommended: `>> /home/nccscportal.com/public_html/storage/logs/scheduler.log 2>&1`

### 6. Save and Exit
- **If using nano**: Press `Ctrl+X`, then `Y`, then `Enter`
- **If using vim**: Press `Esc`, type `:wq`, press `Enter`

### 7. Verify the Cron Job
```bash
crontab -l
```

You should see your cron job listed.

## Verify Cron Service is Running

### Ubuntu/Debian:
```bash
sudo systemctl status cron
```

If not running, start it:
```bash
sudo systemctl start cron
sudo systemctl enable cron  # Enable on boot
```

### CentOS/RHEL:
```bash
sudo systemctl status crond
```

If not running, start it:
```bash
sudo systemctl start crond
sudo systemctl enable crond  # Enable on boot
```

## Test Your Setup

### 1. List Scheduled Tasks
```bash
cd /path/to/pisportal
php artisan schedule:list
```

You should see all your scheduled tasks:
- Query expiration check - Every 3 minutes
- Leave/Pass expiry alerts - Hourly
- Retirement checks - Daily at 08:00
- Emolument timeline extension - Daily at 08:00
- APER timeline management - Daily at 08:00

### 2. Manually Run the Scheduler
```bash
php artisan schedule:run
```

### 3. Check Logs
```bash
tail -f storage/logs/laravel.log
```

### 4. Monitor Cron Execution
```bash
# Check cron logs (Ubuntu/Debian)
grep CRON /var/log/syslog | tail -20

# Check cron logs (CentOS/RHEL)
grep CRON /var/log/cron | tail -20
```

## Better Logging (Recommended for Production)

Instead of discarding output, log it for debugging:

```bash
* * * * * cd /path/to/pisportal && /usr/bin/php artisan schedule:run >> /path/to/pisportal/storage/logs/scheduler.log 2>&1
```

This will create a `scheduler.log` file you can monitor.

## Troubleshooting

### Cron Job Not Running

1. **Check cron service:**
   ```bash
   sudo systemctl status cron
   ```

2. **Check cron logs for errors:**
   ```bash
   grep CRON /var/log/syslog | grep -i error
   ```

3. **Verify PHP path:**
   ```bash
   which php
   # Use the full path in crontab
   ```

4. **Test with verbose logging:**
   Temporarily change your crontab to:
   ```bash
   * * * * * cd /path/to/pisportal && /usr/bin/php artisan schedule:run >> /tmp/scheduler-debug.log 2>&1
   ```
   Then check `/tmp/scheduler-debug.log` for errors.

### Permission Issues

1. **Ensure proper file ownership:**
   ```bash
   sudo chown -R www-data:www-data /path/to/pisportal
   # Or your web server user (nginx, apache, etc.)
   ```

2. **Ensure storage is writable:**
   ```bash
   sudo chmod -R 775 storage bootstrap/cache
   ```

3. **Check artisan is executable:**
   ```bash
   ls -la artisan
   chmod +x artisan
   ```

### Timezone Issues

1. **Check server timezone:**
   ```bash
   date
   timedatectl  # On systemd systems
   ```

2. **Set timezone if needed:**
   ```bash
   sudo timedatectl set-timezone Africa/Lagos
   ```

3. **Verify Laravel timezone in `.env`:**
   ```
   APP_TIMEZONE=Africa/Lagos
   ```

## Production Best Practices

1. **Use absolute paths** in crontab (not relative paths)
2. **Log scheduler output** for debugging
3. **Monitor logs regularly** to ensure tasks are running
4. **Set up log rotation** to prevent log files from growing too large
5. **Test in staging** before deploying to production
6. **Use Supervisor** for critical applications (alternative to cron)

## Alternative: Using Supervisor (Advanced)

For production environments, you can use Supervisor instead of cron:

1. **Install Supervisor:**
   ```bash
   sudo apt-get install supervisor  # Ubuntu/Debian
   sudo yum install supervisor      # CentOS/RHEL
   ```

2. **Create config file:**
   ```bash
   sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf
   ```

3. **Add configuration:**
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

4. **Update and start:**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-scheduler
   ```

## Verification Checklist

- [ ] Cron service is running
- [ ] Cron job is added to crontab (`crontab -l`)
- [ ] `php artisan schedule:list` shows all tasks
- [ ] `php artisan schedule:run` executes without errors
- [ ] Logs show successful task execution
- [ ] Server timezone matches application timezone
- [ ] File permissions are correct
- [ ] Storage directory is writable

## Quick Reference

```bash
# View crontab
crontab -l

# Edit crontab
crontab -e

# Remove all cron jobs (be careful!)
crontab -r

# Check cron service
sudo systemctl status cron

# View cron logs
grep CRON /var/log/syslog | tail -20

# Test scheduler
cd /path/to/pisportal && php artisan schedule:run

# List scheduled tasks
php artisan schedule:list
```

