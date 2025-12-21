# Fix 403 Forbidden Error

## Quick Fix Commands (Run on your VPS)

### 1. Fix Directory Permissions
```bash
cd /home/mis.ncsportal.com/public_html

# Set correct permissions for directories
find . -type d -exec chmod 755 {} \;

# Set correct permissions for files
find . -type f -exec chmod 644 {} \;

# Special permissions for Laravel directories
chmod -R 775 storage bootstrap/cache
chmod -R 755 public

# Make sure public directory is accessible
chmod 755 public
chmod 644 public/index.php
chmod 644 public/.htaccess
```

### 2. Fix Ownership (if needed)
```bash
# Check current ownership
ls -la public/

# Set ownership (adjust user/group as needed)
# Option A: If using www-data
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R www-data:www-data public

# Option B: If using your user account (misnc5527)
sudo chown -R misnc5527:misnc5527 storage bootstrap/cache
sudo chown -R misnc5527:misnc5527 public
```

### 3. Check Apache Configuration
```bash
# Check if public directory is the document root
# Your Apache vhost should point to: /home/mis.ncsportal.com/public_html/public

# Check Apache error logs
sudo tail -50 /var/log/apache2/error.log
# or
sudo tail -50 /var/log/apache2/error_log
```

### 4. Verify .htaccess File
```bash
# Check if .htaccess exists
ls -la public/.htaccess

# Check its permissions
chmod 644 public/.htaccess
```

### 5. Check SELinux (if enabled)
```bash
# Check if SELinux is enabled
getenforce

# If enabled, you may need to set context
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

## Most Common Fix (Try This First)

```bash
cd /home/mis.ncsportal.com/public_html

# Fix all permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Laravel specific
chmod -R 775 storage bootstrap/cache
chmod 755 public
chmod 644 public/index.php
chmod 644 public/.htaccess

# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart Apache
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

## Check Apache Configuration

Your Apache virtual host should have something like:

```apache
<VirtualHost *:443>
    ServerName mis.ncsportal.com
    DocumentRoot /home/mis.ncsportal.com/public_html/public
    
    <Directory /home/mis.ncsportal.com/public_html/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    # ... SSL configuration ...
</VirtualHost>
```

## Verify It's Fixed

```bash
# Check permissions
ls -la public/
ls -la storage/
ls -la bootstrap/cache/

# Test from command line
curl -I https://mis.ncsportal.com

# Check Apache logs
sudo tail -f /var/log/apache2/error.log
```

