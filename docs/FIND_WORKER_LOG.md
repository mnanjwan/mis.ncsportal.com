# Finding Worker Log Location

## Check Supervisor Config for Log Path

```bash
cat /etc/supervisor/conf.d/laravel-worker.conf
```

Look for the `stdout_logfile` line - that's where logs are written.

## Check if Log Directory Exists

```bash
ls -la /home/mis.ncsportal.com/public_html/storage/logs/
```

If the directory doesn't exist:
```bash
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs/
chmod -R 775 /home/mis.ncsportal.com/public_html/storage/logs/
```

## Check Laravel Logs Instead

The worker might be logging to Laravel's main log:

```bash
tail -50 /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
```

## Check Supervisor Logs

Supervisor itself logs to:
```bash
tail -50 /var/log/supervisor/supervisord.log
```

## Check if Worker is Actually Running

```bash
sudo supervisorctl status laravel-worker:*
```

If it shows RUNNING, check what command it's using:
```bash
ps aux | grep "queue:work"
```

This shows the actual process and might reveal the log location.

## Create Log File Manually

If the log file doesn't exist, create it:

```bash
touch /home/mis.ncsportal.com/public_html/storage/logs/worker.log
chmod 664 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
chown nccsc9434:nccsc9434 /home/mis.ncsportal.com/public_html/storage/logs/worker.log
```

Then restart the worker:
```bash
sudo supervisorctl restart laravel-worker:*
```


