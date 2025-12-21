# Deployment Guide for VPS

## Overview

Since you're using a VPS and pulling changes via git, here's how to properly deploy and verify everything works.

## Deployment Process

### On Your VPS Server:

1. **SSH into your VPS**
   ```bash
   ssh user@your-vps-ip
   ```

2. **Navigate to your project directory**
   ```bash
   cd /path/to/your/project
   ```

3. **Pull latest changes**
   ```bash
   git pull origin main
   # or whatever branch you're using
   ```

4. **Run the deployment script**
   ```bash
   ./deploy.sh
   ```

   The script will:
   - ✅ Install/update Composer dependencies
   - ✅ Clear all Laravel caches
   - ✅ Build frontend assets (if npm is available)
   - ✅ Set proper permissions
   - ✅ Restart queue workers (if using supervisor)

## How to Verify Deployment Works

### 1. Check if `config/mail.php` was pulled
```bash
ls -la config/mail.php
cat config/mail.php | grep encryption
```
You should see the `encryption` line we added.

### 2. Verify Laravel is working
```bash
php artisan --version
php artisan config:show mail.default
php artisan config:show mail.from
```

### 3. Check your `.env` file
```bash
cat .env | grep MAIL
```
Make sure you have:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@ncsportal.com
MAIL_FROM_NAME="NCS Employee Portal"
```

### 4. Test email sending
```bash
# Clear config cache first
php artisan config:clear

# Test by triggering an action that sends email
# Then check logs:
tail -f storage/logs/laravel.log
```

### 5. Check if files are up to date
```bash
# Check when config/mail.php was last modified
ls -lh config/mail.php

# Compare with git log
git log -1 --format="%ai %s" config/mail.php
```

## Troubleshooting

### If `config/mail.php` is not updating:

1. **Check if file exists on server**
   ```bash
   ls -la config/mail.php
   ```

2. **Check git status**
   ```bash
   git status
   git log -1 config/mail.php
   ```

3. **Force pull (if needed)**
   ```bash
   git fetch origin
   git reset --hard origin/main
   ```

4. **Clear config cache**
   ```bash
   php artisan config:clear
   ```

### If emails still don't send:

1. **Verify SMTP settings in .env**
   ```bash
   php artisan config:show mail.mailers.smtp
   ```

2. **Test SMTP connection** (create a test file)
   ```php
   <?php
   // test-smtp.php
   require 'vendor/autoload.php';
   
   $transport = (new Swift_SmtpTransport('your-smtp-host', 587, 'tls'))
     ->setUsername('your-username')
     ->setPassword('your-password');
   
   $mailer = new Swift_Mailer($transport);
   
   $message = (new Swift_Message('Test Email'))
     ->setFrom(['no-reply@ncsportal.com' => 'NCS Employee Portal'])
     ->setTo(['your-email@example.com'])
     ->setBody('This is a test email');
   
   $result = $mailer->send($message);
   echo $result ? "Email sent!" : "Failed to send";
   ```

3. **Check Laravel logs**
   ```bash
   tail -50 storage/logs/laravel.log | grep -i mail
   ```

## Quick Deployment Checklist

- [ ] Pulled latest changes: `git pull origin main`
- [ ] Ran deployment script: `./deploy.sh`
- [ ] Verified `config/mail.php` exists and has encryption line
- [ ] Checked `.env` has correct MAIL settings
- [ ] Cleared config cache: `php artisan config:clear`
- [ ] Tested sending an email
- [ ] Checked logs for errors

## Notes

- **`deploy-build.sh`**: Only builds frontend assets (npm). Use this locally before deploying assets.
- **`deploy.sh`**: Full Laravel deployment script. Run this on your VPS after pulling changes.
- **`.env` file**: Never commit this! Create it manually on your VPS with correct settings.

