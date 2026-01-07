# Fix Supervisor Configuration

## Issue Found

Your Supervisor config has the **wrong path**:
- Current (WRONG): `/home/nccscportal.com/public_html`
- Should be: `/home/mis.ncsportal.com/public_html`

## Fix Steps

### 1. Edit Supervisor Config

```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

### 2. Update the Paths

Change:
```ini
command=/usr/bin/php /home/nccscportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
stdout_logfile=/home/nccscportal.com/public_html/storage/logs/worker.log
```

To:
```ini
command=/usr/bin/php /home/mis.ncsportal.com/public_html/artisan queue:work --sleep=3 --tries=3 --timeout=90
stdout_logfile=/home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

### 3. Create Log Directory (if needed)

```bash
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs
chmod -R 775 /home/mis.ncsportal.com/public_html/storage/logs
chown -R nccsc9434:nccsc9434 /home/mis.ncsportal.com/public_html/storage/logs
```

### 4. Reload Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker:*
```

### 5. Verify

```bash
sudo supervisorctl status laravel-worker:*
```

Should show: `RUNNING`

## Fix laravel-queue Config Error

There's also an error about `laravel-queue.conf`. Check if that file exists:

```bash
cat /etc/supervisor/conf.d/laravel-queue.conf
```

If it has wrong paths, fix them too, or remove it if not needed:

```bash
sudo rm /etc/supervisor/conf.d/laravel-queue.conf
sudo supervisorctl reread
sudo supervisorctl update
```

## Good News!

Your Laravel logs show emails ARE being sent:
```
[2026-01-04 02:20:00] production.INFO: Recruit onboarding email sent {"recruit_id":651,"email":"ribetlive@gmail.com"}
```

So the queue worker IS working, but the Supervisor config path is wrong which might cause issues later.



