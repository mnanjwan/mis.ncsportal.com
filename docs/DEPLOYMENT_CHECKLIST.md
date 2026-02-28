# Deployment Checklist - Inactivity Timeout

## Files That Must Be Deployed

Make sure these files are on your production server:

1. ✅ `public/js/inactivity-timeout.js`
2. ✅ `public/favicon-animated.svg`

## Quick Deployment Steps

### Option 1: Git Deployment (Recommended)
```bash
# On production server
cd /path/to/your/app
git pull origin main  # or your branch name

# Verify files exist
ls -la public/js/inactivity-timeout.js
ls -la public/favicon-animated.svg

# Set correct permissions
chmod 644 public/js/inactivity-timeout.js
chmod 644 public/favicon-animated.svg
```

### Option 2: Manual File Upload
1. Upload `public/js/inactivity-timeout.js` to `public/js/` on production
2. Upload `public/favicon-animated.svg` to `public/` on production
3. Set file permissions to 644

### Option 3: SCP/SFTP
```bash
# From your local machine
scp public/js/inactivity-timeout.js user@production-server:/path/to/app/public/js/
scp public/favicon-animated.svg user@production-server:/path/to/app/public/
```

## Verify Deployment

### 1. Check Files Exist
```bash
# SSH into production server
ssh user@production-server
cd /path/to/your/app
ls -la public/js/inactivity-timeout.js
ls -la public/favicon-animated.svg
```

### 2. Check File Permissions
```bash
# Files should be readable (644)
chmod 644 public/js/inactivity-timeout.js
chmod 644 public/favicon-animated.svg
```

### 3. Test in Browser
1. Open your production site
2. Open DevTools (F12)
3. Go to Network tab
4. Refresh page
5. Look for `inactivity-timeout.js` - should show status 200 (not 404)
6. Check Console tab - should see: `✅ Inactivity timeout initialized`

### 4. Direct URL Test
Try accessing directly:
- `https://yourdomain.com/js/inactivity-timeout.js`
- `https://yourdomain.com/favicon-animated.svg`

Both should load (not 404).

## Current Solution

The code now **inlines the script** directly in the HTML as a fallback, so even if the external file isn't accessible, the functionality will still work. However, it's still best practice to deploy the files properly.

## Troubleshooting

### If you still get 404:
1. **Check file exists on server:**
   ```bash
   ls -la public/js/inactivity-timeout.js
   ```

2. **Check web server can access it:**
   ```bash
   # Test with curl (as web server user)
   curl http://localhost/js/inactivity-timeout.js
   ```

3. **Check .htaccess (Apache) or nginx config:**
   - Make sure `public/js/` directory is accessible
   - No rewrite rules blocking `.js` files

4. **Clear Laravel caches:**
   ```bash
   php artisan view:clear
   php artisan cache:clear
   php artisan config:clear
   ```

5. **Check file ownership:**
   ```bash
   # Files should be owned by web server user
   chown www-data:www-data public/js/inactivity-timeout.js
   chown www-data:www-data public/favicon-animated.svg
   ```

## Git Status

To check if files are tracked:
```bash
git ls-files public/js/inactivity-timeout.js public/favicon-animated.svg
```

If they show up, they're tracked. If not, add them:
```bash
git add public/js/inactivity-timeout.js public/favicon-animated.svg
git commit -m "Add inactivity timeout script and animated favicon"
git push
```
