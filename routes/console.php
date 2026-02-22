<?php

use App\Jobs\CheckRetirementJob;
use App\Jobs\SendLeaveExpiryAlertsJob;
use App\Jobs\SendPassExpiryAlertsJob;
use App\Services\RetirementService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
Schedule::call(function () {
    CheckRetirementJob::dispatch();
})->daily();

// Check and send retirement alerts (3 months before retirement)
Schedule::command('retirement:check-alerts')
    ->daily()
    ->at('08:00');

// Check and activate pre-retirement status
Schedule::call(function () {
    $retirementService = new RetirementService();
    $retirementService->checkAndActivatePreRetirementStatus();
})->daily();

Schedule::call(function () {
    SendLeaveExpiryAlertsJob::dispatch();
})->hourly();

Schedule::call(function () {
    SendPassExpiryAlertsJob::dispatch();
})->hourly();

// Emolument timeline auto-extension
Schedule::command('emolument:extend-timeline')
    ->daily()
    ->at('08:00');

// APER timeline management - check and deactivate expired timelines, send notifications
Schedule::command('aper:manage-timeline')
    ->daily()
    ->at('08:00');

// Query expiration - check and automatically expire queries that have passed their deadline
// Run every 3 minutes to ensure timely expiration
Schedule::command('queries:check-expired')
    ->everyThreeMinutes();

// Query deadline reminders - send reminders to officers 24 hours before deadline
// Run every 6 hours to catch queries approaching deadline
Schedule::command('queries:send-reminders')
    ->everySixHours();

// Pharmacy: move expired stock from pharmacy_stocks to pharmacy_expired_drug_records
Schedule::command('pharmacy:move-expired-stock')
    ->daily()
    ->at('00:05');

// Birthday greetings - send email to officers on their birthday
Schedule::command('birthday:send-greetings')
    ->daily()
    ->at('08:00');
