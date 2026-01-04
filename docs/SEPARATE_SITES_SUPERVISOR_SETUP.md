# Separate Supervisor Configs for Multiple Sites

## Step-by-Step Guide

### Step 1: Restore Original Config for First Site (nccscportal.com)

**Edit the existing config:**
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**Replace with original content (for nccscportal.com):**
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

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

### Step 2: Create New Config for mis.ncsportal.com

**Create new config file:**
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker-mis.conf
```

**Add this content (for mis.ncsportal.com):**
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

**Save and exit:** `Ctrl+O`, `Enter`, `Ctrl+X`

**Important:** Notice the program name is `laravel-worker-mis` (different from the first one)

### Step 3: Create Log Directories

**For nccscportal.com:**
```bash
mkdir -p /home/nccscportal.com/public_html/storage/logs
chmod -R 775 /home/nccscportal.com/public_html/storage/logs
chown -R nccsc9434:nccsc9434 /home/nccscportal.com/public_html/storage/logs
```

**For mis.ncsportal.com:**
```bash
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs
chmod -R 775 /home/mis.ncsportal.com/public_html/storage/logs
chown -R nccsc9434:nccsc9434 /home/mis.ncsportal.com/public_html/storage/logs
```

### Step 4: Reload Supervisor Configuration

```bash
# Read all config files
sudo supervisorctl reread

# Update Supervisor with new configs
sudo supervisorctl update

# Restart both workers
sudo supervisorctl restart laravel-worker:*
sudo supervisorctl restart laravel-worker-mis:*
```

### Step 5: Verify Both Workers are Running

```bash
sudo supervisorctl status
```

Should show:
```
laravel-worker:laravel-worker_00    RUNNING    pid 12345
laravel-worker-mis:laravel-worker-mis_00    RUNNING    pid 12346
```

### Step 6: Verify Individual Workers

**Check first site (nccscportal.com):**
```bash
sudo supervisorctl status laravel-worker:*
```

**Check second site (mis.ncsportal.com):**
```bash
sudo supervisorctl status laravel-worker-mis:*
```

## Managing Each Site Separately

### Start/Stop/Restart First Site (nccscportal.com)
```bash
sudo supervisorctl start laravel-worker:*
sudo supervisorctl stop laravel-worker:*
sudo supervisorctl restart laravel-worker:*
```

### Start/Stop/Restart Second Site (mis.ncsportal.com)
```bash
sudo supervisorctl start laravel-worker-mis:*
sudo supervisorctl stop laravel-worker-mis:*
sudo supervisorctl restart laravel-worker-mis:*
```

## Summary of Files

**File 1:** `/etc/supervisor/conf.d/laravel-worker.conf`
- Program name: `laravel-worker`
- Path: `/home/nccscportal.com/public_html`
- For: First site (nccscportal.com)

**File 2:** `/etc/supervisor/conf.d/laravel-worker-mis.conf`
- Program name: `laravel-worker-mis`
- Path: `/home/mis.ncsportal.com/public_html`
- For: Second site (mis.ncsportal.com)

## Quick Reference

```bash
# View all config files
ls -la /etc/supervisor/conf.d/

# View all workers
sudo supervisorctl status

# Edit first site config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Edit second site config
sudo nano /etc/supervisor/conf.d/laravel-worker-mis.conf

# After editing, reload
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker:*
sudo supervisorctl restart laravel-worker-mis:*
```

