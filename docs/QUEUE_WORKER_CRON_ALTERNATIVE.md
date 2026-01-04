# Queue Worker via Cron (Alternative Method)

## Important Note

⚠️ **This is NOT the recommended approach** for production. Using Supervisor is better because:
- Jobs process immediately when queued
- More efficient (no overhead of starting/stopping process every minute)
- Better error handling and logging
- No delay in processing jobs

However, if you cannot use Supervisor, you can use cron with the `--stop-when-empty` flag.

## Setup via Cron

### Option 1: Process All Pending Jobs Every Minute (Recommended if using cron)

Add this to your crontab (runs every minute, processes all pending jobs, then exits):

```bash
* * * * * cd /home/nccscportal.com/public_html && /usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**How it works:**
- Runs every minute
- Processes ALL pending jobs in the queue
- Exits when queue is empty
- No continuous process running

**Pros:**
- No continuous process (saves resources)
- Processes jobs in batches
- Simple setup

**Cons:**
- Jobs wait up to 1 minute before processing (not immediate)
- Slight overhead of starting PHP process every minute
- If many jobs are queued, they all process at once (could cause temporary load spike)

### Option 2: Process One Job Per Minute (Slower but more controlled)

```bash
* * * * * cd /home/nccscportal.com/public_html && /usr/bin/php artisan queue:work --once >> /dev/null 2>&1
```

**How it works:**
- Runs every minute
- Processes only ONE job
- Exits immediately after processing one job

**Pros:**
- Very controlled (one job at a time)
- Minimal resource usage

**Cons:**
- VERY slow if you have many jobs (only 1 per minute = 60 per hour max)
- Jobs can wait a very long time

## Recommended: Option 1 with --stop-when-empty

### Complete Crontab Setup

Edit crontab:
```bash
crontab -e
```

Add/update these lines:

```bash
# Laravel Scheduled Tasks (runs every minute)
* * * * * cd /home/nccscportal.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Laravel Queue Worker (processes all pending jobs every minute)
* * * * * cd /home/nccscportal.com/public_html && /usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### Verification

1. **Queue a test job:**
   ```bash
   php artisan tinker
   >>> \App\Jobs\SendRecruitOnboardingLinkJob::dispatch($recruit, $link, 'Test');
   ```

2. **Wait 1 minute, then check if job was processed:**
   ```bash
   # Check jobs table (should be empty if processed)
   php artisan tinker
   >>> DB::table('jobs')->count();
   ```

3. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Comparison: Supervisor vs Cron

| Feature | Supervisor | Cron (--stop-when-empty) |
|---------|-----------|-------------------------|
| Processing Speed | Immediate | Up to 1 minute delay |
| Resource Usage | Low (one continuous process) | Very Low (process starts/stops) |
| Setup Complexity | Medium (need Supervisor) | Simple (just cron) |
| Reliability | High (auto-restart) | Medium (depends on cron) |
| Job Processing | Continuous | Batch (every minute) |
| Error Handling | Better | Basic |

## Why Supervisor Won't Crash Your Site

Supervisor is very lightweight:
- Uses minimal memory (~5-10MB per worker)
- Doesn't affect web requests (runs separately)
- Your site already runs PHP-FPM, MySQL, etc. - one more process won't crash it
- Supervisor is the standard way to run queue workers on production servers

## Recommendation

**For production:** Use Supervisor (it's designed for this purpose)

**If Supervisor is not available:** Use cron with `--stop-when-empty` (Option 1 above)

**Avoid:** Using `queue:work` without flags in cron (will create multiple processes and cause issues)

## Troubleshooting

### Jobs Not Processing

1. **Check if cron is running:**
   ```bash
   crontab -l
   ```

2. **Check cron logs:**
   ```bash
   grep CRON /var/log/syslog
   ```

3. **Test manually:**
   ```bash
   cd /home/nccscportal.com/public_html
   php artisan queue:work --stop-when-empty -v
   ```

4. **Check queue connection:**
   ```bash
   # In .env file
   QUEUE_CONNECTION=database
   ```

### Jobs Processing Too Slowly

If jobs are taking too long to process, consider:
- Using Supervisor instead (processes immediately)
- Running cron more frequently (not recommended - every 30 seconds: `*/30 * * * *`)
- Using `--max-jobs` flag to limit jobs per run: `queue:work --stop-when-empty --max-jobs=10`

## Summary

**If you MUST use cron only:**
```bash
* * * * * cd /home/nccscportal.com/public_html && /usr/bin/php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

**But Supervisor is still the better choice** - it won't crash your site and provides better performance.

