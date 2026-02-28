# Inactivity Timeout Troubleshooting Guide

## Common Production Issues

### 1. **Browser Caching**
**Problem:** Browser is serving cached version of the JavaScript file.

**Solution:** 
- The script now includes cache busting with version query parameter
- Clear browser cache: `Ctrl+Shift+Delete` (Windows) or `Cmd+Shift+Delete` (Mac)
- Hard refresh: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)

### 2. **File Not Deployed**
**Problem:** The `inactivity-timeout.js` file doesn't exist on production server.

**Check:**
```bash
# On production server, verify file exists:
ls -la public/js/inactivity-timeout.js

# Check file permissions:
chmod 644 public/js/inactivity-timeout.js
```

### 3. **Script Not Loading**
**Problem:** The script tag isn't being rendered or is blocked.

**Check in Browser Console:**
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for: `✅ Inactivity timeout initialized. Timeout: 900 seconds`
4. Check Network tab to see if `inactivity-timeout.js` is loading (status 200)

**Verify Script Loaded:**
```javascript
// In browser console, run:
console.log(window.inactivityTimeoutLoaded);
// Should return: true
```

### 4. **Authentication Check Failing**
**Problem:** The `@auth` directive might not work as expected.

**Check:**
- Verify you're logged in
- Check if the script tag appears in page source (View Source)
- Look for: `<script src="/js/inactivity-timeout.js?v=..."></script>`

### 5. **JavaScript Errors**
**Problem:** Other JavaScript errors prevent this script from running.

**Check:**
1. Open browser Console
2. Look for any red error messages
3. Fix any errors that appear before this script

### 6. **Path Issues (Subdirectory Deployment)**
**Problem:** App is deployed in a subdirectory (e.g., `/app/`).

**Solution:** 
- The script now handles subdirectories automatically
- Logout path uses `window.location.origin` to get correct base URL

### 7. **Content Security Policy (CSP)**
**Problem:** CSP headers blocking inline scripts or external resources.

**Check:**
- Look for CSP errors in browser console
- Contact server admin to whitelist the script if needed

## Debugging Steps

### Step 1: Verify File Exists
```bash
# On production server
cd /path/to/your/app
ls -la public/js/inactivity-timeout.js
ls -la public/favicon-animated.svg
```

### Step 2: Check File Permissions
```bash
# Files should be readable
chmod 644 public/js/inactivity-timeout.js
chmod 644 public/favicon-animated.svg
```

### Step 3: Verify in Browser
1. Open your app in browser
2. Press F12 to open DevTools
3. Go to **Network** tab
4. Refresh page (F5)
5. Look for `inactivity-timeout.js` - should show status 200
6. Go to **Console** tab
7. Should see: `✅ Inactivity timeout initialized. Timeout: 900 seconds`

### Step 4: Test Functionality
1. Stay inactive for 15 minutes (or 1 minute if testing)
2. Warning modal should appear
3. Click anywhere - should log out

## Quick Fixes

### Force Cache Clear
Add this to your layout temporarily to force reload:
```blade
<script src="{{ asset('js/inactivity-timeout.js') }}?v={{ time() }}"></script>
```

### Verify Script is Running
Add this to browser console:
```javascript
// Check if script loaded
console.log('Script loaded:', window.inactivityTimeoutLoaded);

// Manually trigger warning (for testing)
if (window.inactivityTimeoutLoaded) {
    // The script should be running
    console.log('✅ Script is active');
} else {
    console.error('❌ Script not loaded');
}
```

## Production Deployment Checklist

- [ ] File `public/js/inactivity-timeout.js` exists on server
- [ ] File `public/favicon-animated.svg` exists on server
- [ ] File permissions are correct (644)
- [ ] Browser cache cleared
- [ ] Script appears in page source
- [ ] Console shows initialization message
- [ ] No JavaScript errors in console
- [ ] Network tab shows 200 status for script

## Still Not Working?

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Web Server Logs:**
   ```bash
   # Apache
   tail -f /var/log/apache2/error.log
   
   # Nginx
   tail -f /var/log/nginx/error.log
   ```

3. **Verify Asset Helper:**
   - Check if `asset()` helper is working
   - Try accessing directly: `https://yourdomain.com/js/inactivity-timeout.js`

4. **Check Blade Compilation:**
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```
