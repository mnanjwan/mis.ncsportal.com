# Timezone Configuration Guide

## Current Configuration

The application is configured to use **Africa/Lagos** timezone (West Africa Time - WAT).

## Configuration Files

### 1. Environment File (`.env`)
```env
APP_TIMEZONE=Africa/Lagos
```

### 2. Config File (`config/app.php`)
```php
'timezone' => env('APP_TIMEZONE', 'Africa/Lagos'),
```

## Verification

### Check Current Timezone
```bash
php artisan tinker --execute="echo config('app.timezone');"
```

### Check Current Time
```bash
php artisan tinker --execute="echo now()->format('Y-m-d H:i:s T');"
```

### Expected Output
- Timezone: `Africa/Lagos`
- Time Format: `2025-12-31 13:04:45 WAT` (West Africa Time)

## Server Timezone (Production)

For production servers, ensure the server timezone matches:

### Linux/Unix
```bash
# Check current timezone
timedatectl

# Set timezone
sudo timedatectl set-timezone Africa/Lagos

# Verify
date
```

### macOS
```bash
# Check current timezone
systemsetup -gettimezone

# Set timezone (requires admin)
sudo systemsetup -settimezone Africa/Lagos
```

## Database Timezone

Laravel stores timestamps in UTC in the database, but displays them in the application timezone. This is the recommended approach.

### MySQL
```sql
-- Check timezone
SELECT @@global.time_zone, @@session.time_zone;

-- Set timezone (optional, Laravel handles conversion)
SET time_zone = '+01:00';  -- WAT is UTC+1
```

## Important Notes

1. **Database Storage**: All timestamps are stored in UTC
2. **Application Display**: All dates/times are displayed in Africa/Lagos timezone
3. **Carbon**: Automatically uses the application timezone
4. **Scheduled Tasks**: Run in the application timezone

## Testing Timezone

```php
// In tinker or code
use Carbon\Carbon;

// Current time in application timezone
echo now()->format('Y-m-d H:i:s T'); // Africa/Lagos

// Convert to UTC
echo now()->utc()->format('Y-m-d H:i:s T'); // UTC

// Convert to another timezone
echo now()->setTimezone('America/New_York')->format('Y-m-d H:i:s T');
```

## Troubleshooting

### Issue: Times showing in UTC
**Solution**: Clear config cache
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Scheduled tasks running at wrong time
**Solution**: 
1. Verify `APP_TIMEZONE` in `.env`
2. Clear config cache
3. Restart queue workers if using queues

### Issue: Database times incorrect
**Solution**: 
- Laravel automatically converts between UTC (database) and application timezone
- Ensure `APP_TIMEZONE` is set correctly
- Don't manually set database timezone

## Timezone Reference

- **Timezone**: Africa/Lagos
- **UTC Offset**: UTC+1 (WAT - West Africa Time)
- **No DST**: West Africa Time does not observe daylight saving time

## Related Files

- `config/app.php` - Application timezone configuration
- `.env` - Environment-specific timezone setting
- `app/Providers/AppServiceProvider.php` - Can add timezone initialization here if needed

