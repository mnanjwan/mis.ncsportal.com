#!/bin/bash

# Laravel Deployment Script for VPS
# This script should be run on your VPS server after pulling changes
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting Laravel deployment..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Are you in the Laravel project directory?${NC}"
    exit 1
fi

# Step 1: Pull latest changes (if not already done)
echo -e "${YELLOW}📥 Step 1: Checking git status...${NC}"
if [ -d ".git" ]; then
    echo "Git repository detected. Make sure you've pulled the latest changes:"
    echo "  git pull origin main"
    read -p "Press Enter to continue after pulling changes..."
else
    echo "No git repository found. Skipping git pull step."
fi

# Step 2: Install/Update Composer dependencies
echo ""
echo -e "${YELLOW}📦 Step 2: Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✅ Composer dependencies installed${NC}"

# Step 3: Clear and cache config
echo ""
echo -e "${YELLOW}⚙️  Step 3: Clearing and caching configuration...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Only cache config in production (optional - comment out if you prefer not to cache)
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

echo -e "${GREEN}✅ Caches cleared${NC}"

# Step 4: Run migrations (optional - uncomment if needed)
# echo ""
# echo -e "${YELLOW}🗄️  Step 4: Running database migrations...${NC}"
# php artisan migrate --force
# echo -e "${GREEN}✅ Migrations completed${NC}"

# Step 5: Build frontend assets (if npm/node is available)
echo ""
echo -e "${YELLOW}🎨 Step 5: Building frontend assets...${NC}"
if command -v npm &> /dev/null; then
    npm install
    npm run build
    echo -e "${GREEN}✅ Frontend assets built${NC}"
else
    echo -e "${YELLOW}⚠️  npm not found. Skipping frontend build.${NC}"
    echo "   Make sure to build assets manually or copy from local build."
fi

# Step 6: Set permissions (if needed)
echo ""
echo -e "${YELLOW}🔐 Step 6: Setting storage permissions...${NC}"
# Set directory permissions
find storage -type d -exec chmod 775 {} \; 2>/dev/null || chmod -R 775 storage
find bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || chmod -R 775 bootstrap/cache

# Set file permissions
find storage -type f -exec chmod 664 {} \; 2>/dev/null || chmod -R 664 storage
find bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || chmod -R 664 bootstrap/cache

# Ensure logs directory exists and is writable
mkdir -p storage/logs
chmod 775 storage/logs
touch storage/logs/laravel.log 2>/dev/null || true
chmod 664 storage/logs/*.log 2>/dev/null || true

# Set ownership (try www-data first, fallback to current user)
if id "www-data" &>/dev/null; then
    sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || {
        echo "⚠️  Could not set ownership to www-data, trying current user..."
        CURRENT_USER=$(whoami)
        sudo chown -R $CURRENT_USER:$CURRENT_USER storage bootstrap/cache 2>/dev/null || echo "⚠️  Could not set ownership (may need manual fix)"
    }
else
    CURRENT_USER=$(whoami)
    sudo chown -R $CURRENT_USER:$CURRENT_USER storage bootstrap/cache 2>/dev/null || echo "⚠️  Could not set ownership (may need manual fix)"
fi

echo -e "${GREEN}✅ Permissions set${NC}"

# Step 7: Restart queue workers (if using queues)
echo ""
echo -e "${YELLOW}🔄 Step 7: Queue workers...${NC}"
if command -v supervisorctl &> /dev/null; then
    echo "Restarting queue workers with supervisor..."
    supervisorctl restart laravel-worker:* 2>/dev/null || echo "⚠️  No supervisor workers found"
else
    echo "⚠️  Supervisor not found. Make sure to restart queue workers manually:"
    echo "   php artisan queue:restart"
fi

# Step 8: Verify deployment
echo ""
echo -e "${YELLOW}✔️  Step 8: Verifying deployment...${NC}"
php artisan --version
echo ""
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "📋 Next steps:"
echo "   1. Check your .env file has correct mail settings"
echo "   2. Test sending an email"
echo "   3. Check logs: tail -f storage/logs/laravel.log"
echo ""

