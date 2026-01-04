# Fix Supervisor Errors

## Error 1: laravel-queue.conf Problem

The error shows:
```
ERROR: CANT_REREAD: The directory named as part of the path /home/mis.nccportal.com/storage/logs/queue.log does not exist
```

### Solution: Fix or Remove laravel-queue.conf

**Option A: Remove it (if not needed)**
```bash
sudo rm /etc/supervisor/conf.d/laravel-queue.conf
```

**Option B: Fix it (if you need it)**
```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

Check the path and fix it, or create the missing directory:
```bash
mkdir -p /home/mis.nccportal.com/storage/logs
chmod -R 775 /home/mis.nccportal.com/storage/logs
```

## Error 2: laravel-worker-mis Not Found

The error shows:
```
laravel-worker-mis: ERROR (no such group)
```

This means the config file doesn't exist or wasn't loaded.

### Solution: Create the Config File

**Step 1: Create the config file**
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker-mis.conf
```

**Step 2: Add this content**
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

**Step 3: Create log directory**
```bash
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs
chmod -R 775 /home/mis.ncsportal.com/public_html/storage/logs
chown -R nccsc9434:nccsc9434 /home/mis.ncsportal.com/public_html/storage/logs
```

**Step 4: Reload Supervisor**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker-mis:*
```

## Complete Fix Steps

Run these commands in order:

```bash
# 1. Remove or fix problematic laravel-queue.conf
sudo rm /etc/supervisor/conf.d/laravel-queue.conf
# OR fix it if you need it

# 2. Create laravel-worker-mis.conf
sudo nano /etc/supervisor/conf.d/laravel-worker-mis.conf
# Paste the config content above

# 3. Create log directory
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs
chmod -R 775 /home/mis.ncsportal.com/public_html/storage/logs
chown -R nccsc9434:nccsc9434 /home/mis.ncsportal.com/public_html/storage/logs

# 4. Reload Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# 5. Start the new worker
sudo supervisorctl start laravel-worker-mis:*

# 6. Verify both workers are running
sudo supervisorctl status
```

## Verify Everything Works

```bash
# Check all workers
sudo supervisorctl status

# Should show:
# laravel-worker:laravel-worker_00    RUNNING
# laravel-worker-mis:laravel-worker-mis_00    RUNNING
```


