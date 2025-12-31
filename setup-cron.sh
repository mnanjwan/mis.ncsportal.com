#!/bin/bash

# Laravel Scheduler Cron Setup Script
# This script sets up the cron job for Laravel's task scheduler

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the project path (directory where this script is located)
PROJECT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${GREEN}Laravel Scheduler Cron Setup${NC}"
echo "================================"
echo ""

# Check if artisan exists
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo -e "${RED}Error: artisan file not found in $PROJECT_PATH${NC}"
    exit 1
fi

# Get PHP path
PHP_PATH=$(which php)
if [ -z "$PHP_PATH" ]; then
    echo -e "${RED}Error: PHP not found in PATH${NC}"
    exit 1
fi

echo "Project Path: $PROJECT_PATH"
echo "PHP Path: $PHP_PATH"
echo ""

# Create cron command
CRON_CMD="* * * * * cd $PROJECT_PATH && $PHP_PATH artisan schedule:run >> /dev/null 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo -e "${YELLOW}Warning: Cron job already exists!${NC}"
    echo ""
    echo "Current crontab entries:"
    crontab -l
    echo ""
    read -p "Do you want to update it? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Remove old entry and add new one
        (crontab -l 2>/dev/null | grep -v "schedule:run") | crontab -
        (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
        echo -e "${GREEN}Cron job updated successfully!${NC}"
    else
        echo "No changes made."
        exit 0
    fi
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    echo -e "${GREEN}Cron job added successfully!${NC}"
fi

echo ""
echo "Current crontab entries:"
crontab -l
echo ""

# Test the scheduler
echo -e "${YELLOW}Testing scheduler...${NC}"
cd "$PROJECT_PATH"
$PHP_PATH artisan schedule:list

echo ""
echo -e "${GREEN}Setup complete!${NC}"
echo ""
echo "To verify the scheduler is working:"
echo "  1. Wait a minute and check: php artisan schedule:list"
echo "  2. Check logs: tail -f storage/logs/laravel.log"
echo "  3. Manually run: php artisan schedule:run"


