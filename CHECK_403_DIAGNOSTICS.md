# 403 Error Diagnostics - Run These Commands

## 1. Check Apache Access Logs (shows 403 errors)
```bash
sudo tail -50 /var/log/apache2/access.log | grep 403
# or
sudo grep "403" /var/log/apache2/access.log | tail -20
```

## 2. Check Current Permissions
```bash
cd /home/mis.ncsportal.com/public_html

# Check public directory
ls -la public/
ls -la public/index.php
ls -la public/.htaccess

# Check parent directory
ls -la .
```

## 3. Check Apache Virtual Host Configuration
```bash
# Find your site's config file
sudo apache2ctl -S | grep mis.ncsportal.com

# View the virtual host config
sudo cat /etc/apache2/sites-enabled/*mis.ncsportal.com*.conf
# or
sudo cat /etc/apache2/sites-enabled/*.conf | grep -A 20 mis.ncsportal.com
```

## 4. Test Directory Access
```bash
cd /home/mis.ncsportal.com/public_html/public

# Test if Apache user can read files
sudo -u www-data ls -la
sudo -u www-data cat index.php | head -5
```

## 5. Check if mod_rewrite is enabled
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 6. Verify .htaccess is being read
```bash
# Check if AllowOverride is set
sudo grep -r "AllowOverride" /etc/apache2/sites-enabled/
```

## Quick Fix to Try

```bash
cd /home/mis.ncsportal.com/public_html

# Set safe permissions
chmod 755 .
chmod -R 755 public
chmod 644 public/index.php
chmod 644 public/.htaccess

# Check ownership
ls -la public/ | head -5

# If owned by root, change it
sudo chown -R misnc5527:misnc5527 public

# Restart Apache
sudo systemctl restart apache2
```

