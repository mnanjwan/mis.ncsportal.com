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
