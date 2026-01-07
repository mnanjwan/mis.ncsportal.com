# Multiple Sites on VPS - Cron and Supervisor Setup

## Overview

When you have multiple Laravel sites on one VPS:
- ✅ Each site needs its own cron entry for `schedule:run`
- ✅ Each site needs its own Supervisor config for queue workers
- ✅ They can all coexist without conflicts

## Cron Jobs Setup (One Entry Per Site)

### View Current Crontab

```bash
crontab -l
```

### Example: Multiple Sites in Crontab

```bash
# Site 1: mis.ncsportal.com
* * * * * cd /home/mis.ncsportal.com/public_html && /usr/bin/php artisan schedule:run >> /home/mis.ncsportal.com/public_html/storage/logs/schedule.log 2>&1

# Site 2: another-site.com
* * * * * cd /home/another-site.com/public_html && /usr/bin/php artisan schedule:run >> /home/another-site.com/public_html/storage/logs/schedule.log 2>&1

# Site 3: third-site.com
* * * * * cd /home/third-site.com/public_html && /usr/bin/php artisan schedule:run >> /home/third-site.com/public_html/storage/logs/schedule.log 2>&1
```

**Key Points:**
- Each site has its own line
- Each uses its own path
- Each logs to its own file
- They all run every minute (cron handles timing)

## Supervisor Setup (One Config File Per Site)

### Supervisor Config Files

Each site gets its own config file in `/etc/supervisor/conf.d/`:

**File 1:** `/etc/supervisor/conf.d/laravel-worker-mis.conf`
```ini
[program:laravel-worker-mis]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/mis.ncsportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=nccsc9434
numprocs=1
redirect_stderr=true
stdout_logfile=/home/mis.ncsportal.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

**File 2:** `/etc/supervisor/conf.d/laravel-worker-another.conf`
```ini
[program:laravel-worker-another]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/another-site.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=another-user
numprocs=1
redirect_stderr=true
stdout_logfile=/home/another-site.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

**File 3:** `/etc/supervisor/conf.d/laravel-worker-third.conf`
```ini
[program:laravel-worker-third]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/third-site.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=third-user
numprocs=1
redirect_stderr=true
stdout_logfile=/home/third-site.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

**Important:**
- Each config has a unique `[program:name]` (e.g., `laravel-worker-mis`, `laravel-worker-another`)
- Each uses its own site path
- Each uses its own user (the site owner)
- Each logs to its own file

## Managing Multiple Sites

### View All Workers

```bash
sudo supervisorctl status
```

Shows all workers from all sites:
```
laravel-worker-mis:laravel-worker-mis_00    RUNNING    pid 12345
laravel-worker-another:laravel-worker-another_00    RUNNING    pid 12346
laravel-worker-third:laravel-worker-third_00    RUNNING    pid 12347
```

### Manage Individual Site Workers

```bash
# Start specific site worker
sudo supervisorctl start laravel-worker-mis:*

# Stop specific site worker
sudo supervisorctl stop laravel-worker-mis:*

# Restart specific site worker
sudo supervisorctl restart laravel-worker-mis:*

# Check status of specific site
sudo supervisorctl status laravel-worker-mis:*
```

### After Adding New Site Config

```bash
# Read new configs
sudo supervisorctl reread

# Update Supervisor
sudo supervisorctl update

# Start the new worker
sudo supervisorctl start laravel-worker-new-site:*
```

## Naming Convention

Use descriptive names that include the site identifier:

**Good Names:**
- `laravel-worker-mis`
- `laravel-worker-another`
- `laravel-worker-portal`

**Bad Names (conflicts):**
- `laravel-worker` (too generic, will conflict)
- `worker` (too generic)

## Complete Setup Example

### For mis.ncsportal.com

**1. Cron Entry:**
```bash
* * * * * cd /home/mis.ncsportal.com/public_html && /usr/bin/php artisan schedule:run >> /home/mis.ncsportal.com/public_html/storage/logs/schedule.log 2>&1
```

**2. Supervisor Config:** `/etc/supervisor/conf.d/laravel-worker-mis.conf`
```ini
[program:laravel-worker-mis]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/mis.ncsportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=nccsc9434
numprocs=1
redirect_stderr=true
stdout_logfile=/home/mis.ncsportal.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

**3. Apply Supervisor Config:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker-mis:*
```

## Quick Reference Commands

```bash
# View all cron jobs
crontab -l

# Edit cron jobs
crontab -e

# View all Supervisor workers
sudo supervisorctl status

# View Supervisor config files
ls -la /etc/supervisor/conf.d/

# Check Supervisor service
sudo systemctl status supervisor
```

## Summary

✅ **Cron:** One line per site in crontab (all can run every minute)
✅ **Supervisor:** One config file per site in `/etc/supervisor/conf.d/`
✅ **No Conflicts:** Each site has unique names and paths
✅ **Easy Management:** Use descriptive names to identify each site



