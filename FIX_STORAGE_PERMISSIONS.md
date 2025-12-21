# Fix Laravel Storage Permissions Error

## The Problem
Laravel cannot write to `storage/logs/laravel.log` because of incorrect permissions.

## Quick Fix (Run on Your VPS)

```bash
cd /home/mis.ncsportal.com/public_html

# Fix storage directory permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Fix ownership (use your actual user)
sudo chown -R misnc5527:www-data storage bootstrap/cache

# Or if Apache runs as www-data:
sudo chown -R www-data:www-data storage bootstrap/cache

# Make sure the logs directory exists and is writable
mkdir -p storage/logs
chmod 775 storage/logs
chmod 664 storage/logs/*.log 2>/dev/null || true

# Verify permissions
ls -la storage/
ls -la storage/logs/
```

## Complete Fix Script

```bash
#!/bin/bash
cd /home/mis.ncsportal.com/public_html

echo "Fixing Laravel storage permissions..."

# Set directory permissions
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

# Set file permissions
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Set ownership (adjust user/group as needed)
sudo chown -R misnc5527:www-data storage bootstrap/cache

# Create log file if it doesn't exist
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
chown misnc5527:www-data storage/logs/laravel.log

# Verify
ls -la storage/logs/
echo "Done! Check permissions above."
```

## Alternative: If www-data is the web server user

```bash
cd /home/mis.ncsportal.com/public_html

# Set ownership to www-data (Apache user)
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache

# Create log file
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
```

## Verify It's Fixed

```bash
# Test if web server can write
sudo -u www-data touch storage/logs/test.log
sudo -u www-data rm storage/logs/test.log

# Or test with PHP
php artisan tinker
# Then in tinker:
# file_put_contents('storage/logs/test.txt', 'test');
# Should work without errors
```

## Update deploy.sh to Prevent This

Make sure your deploy script sets these permissions correctly.

