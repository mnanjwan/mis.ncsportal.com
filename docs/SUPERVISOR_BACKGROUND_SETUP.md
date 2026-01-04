# Keeping Queue Worker Running in Background with Supervisor

## Supervisor Already Keeps It Running!

Supervisor is a process manager that automatically:
- ✅ Runs the worker in the background
- ✅ Restarts it if it crashes
- ✅ Starts it on server boot
- ✅ Manages logs

## Verify Supervisor is Running

### 1. Check Supervisor Service Status

```bash
sudo systemctl status supervisor
```

Should show: `active (running)`

### 2. Check Queue Worker Status

```bash
sudo supervisorctl status laravel-worker:*
```

Should show:
```
laravel-worker:laravel-worker_00  RUNNING  pid 12345, uptime 1:23:45
```

### 3. Check if Worker is Processing Jobs

```bash
tail -f /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

You should see job processing activity.

## Ensure Supervisor Starts on Boot

### 1. Enable Supervisor Service

```bash
sudo systemctl enable supervisor
```

This ensures Supervisor starts automatically when the server boots.

### 2. Verify Supervisor is Enabled

```bash
sudo systemctl is-enabled supervisor
```

Should show: `enabled`

## Managing the Queue Worker

### Start Worker

```bash
sudo supervisorctl start laravel-worker:*
```

### Stop Worker

```bash
sudo supervisorctl stop laravel-worker:*
```

### Restart Worker (after code changes)

```bash
sudo supervisorctl restart laravel-worker:*
```

### View Worker Status

```bash
sudo supervisorctl status laravel-worker:*
```

### View All Supervisor Programs

```bash
sudo supervisorctl status
```

## Supervisor Configuration File

Your config file should be at:
```
/etc/supervisor/conf.d/laravel-worker.conf
```

Make sure it has:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /home/mis.ncsportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true          # ← Starts automatically
autorestart=true        # ← Restarts if crashes
user=nccsc9434
numprocs=1
redirect_stderr=true
stdout_logfile=/home/mis.ncsportal.com/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

Key settings:
- `autostart=true` - Starts when Supervisor starts
- `autorestart=true` - Restarts if worker crashes or stops

## After Making Changes

If you edit the Supervisor config file:

```bash
# 1. Read the new configuration
sudo supervisorctl reread

# 2. Update Supervisor
sudo supervisorctl update

# 3. Restart the worker
sudo supervisorctl restart laravel-worker:*
```

## Troubleshooting

### Worker Not Running

1. **Check Supervisor status:**
   ```bash
   sudo systemctl status supervisor
   ```

2. **Start Supervisor if stopped:**
   ```bash
   sudo systemctl start supervisor
   ```

3. **Check worker status:**
   ```bash
   sudo supervisorctl status laravel-worker:*
   ```

4. **Check worker logs:**
   ```bash
   tail -50 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
   ```

### Worker Keeps Restarting

Check logs for errors:
```bash
tail -f /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

Common issues:
- PHP errors in code
- Database connection issues
- Missing dependencies

### Worker Not Starting on Boot

1. **Enable Supervisor:**
   ```bash
   sudo systemctl enable supervisor
   ```

2. **Verify:**
   ```bash
   sudo systemctl is-enabled supervisor
   ```

3. **Test by rebooting:**
   ```bash
   sudo reboot
   # After reboot, check:
   sudo supervisorctl status laravel-worker:*
   ```

## Quick Verification Commands

```bash
# Check if Supervisor is running
sudo systemctl status supervisor

# Check if worker is running
sudo supervisorctl status laravel-worker:*

# Check if Supervisor starts on boot
sudo systemctl is-enabled supervisor

# View worker activity
tail -f /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

## Summary

✅ **Supervisor already keeps your worker running in the background**
✅ **It auto-restarts if it crashes**
✅ **Make sure Supervisor service is enabled to start on boot**

You don't need to do anything manually - Supervisor handles everything!

