# Fix Storage Logs Permission Denied Error

## Error
```
Permission denied: /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
```

## Solution: Fix Directory Permissions

### Step 1: Check Current Permissions

```bash
ls -la /home/mis.ncsportal.com/public_html/storage/logs/
```

### Step 2: Fix Storage Directory Permissions

```bash
cd /home/mis.ncsportal.com/public_html

# Make storage and logs directories writable
chmod -R 775 storage
chmod -R 775 storage/logs
chmod -R 775 bootstrap/cache

# Set ownership (replace www-data with your web server user if different)
chown -R nccsc9434:www-data storage
chown -R nccsc9434:www-data storage/logs
chown -R nccsc9434:www-data bootstrap/cache
```

### Step 3: Ensure Logs Directory Exists

```bash
mkdir -p /home/mis.ncsportal.com/public_html/storage/logs
chmod 775 /home/mis.ncsportal.com/public_html/storage/logs
chown nccsc9434:www-data /home/mis.ncsportal.com/public_html/storage/logs
```

### Step 4: Create Log File if Missing

```bash
touch /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
chmod 664 /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
chown nccsc9434:www-data /home/mis.ncsportal.com/public_html/storage/logs/laravel.log
```

## Find Your Web Server User

**For Apache:**
```bash
ps aux | grep apache
# Usually: www-data or apache
```

**For Nginx:**
```bash
ps aux | grep nginx
# Usually: www-data or nginx
```

**For LiteSpeed (CyberPanel):**
```bash
ps aux | grep litespeed
# Usually: nobody or lsadm
```

## Complete Fix Command (One-Liner)

```bash
cd /home/mis.ncsportal.com/public_html && \
chmod -R 775 storage bootstrap/cache && \
chown -R nccsc9434:www-data storage bootstrap/cache && \
mkdir -p storage/logs && \
touch storage/logs/laravel.log && \
chmod 664 storage/logs/laravel.log && \
chown nccsc9434:www-data storage/logs/laravel.log
```

## Verify Permissions

```bash
ls -la /home/mis.ncsportal.com/public_html/storage/logs/
```

Should show:
```
drwxrwxr-x  nccsc9434 www-data  logs/
-rw-rw-r--  nccsc9434 www-data  laravel.log
```

## If Still Not Working

**Check what user your web server runs as:**
```bash
ps aux | grep -E 'apache|nginx|litespeed|php-fpm' | head -1
```

**Then use that user instead of www-data:**
```bash
chown -R nccsc9434:ACTUAL_WEB_USER storage
chown -R nccsc9434:ACTUAL_WEB_USER storage/logs
```

## Common Web Server Users

- **Apache:** `www-data` or `apache`
- **Nginx:** `www-data` or `nginx`
- **LiteSpeed (CyberPanel):** `nobody` or `lsadm`
- **PHP-FPM:** Usually same as web server user


