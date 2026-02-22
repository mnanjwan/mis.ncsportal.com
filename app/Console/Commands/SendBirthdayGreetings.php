<?php

namespace App\Console\Commands;

use App\Jobs\SendBirthdayGreetingMailJob;
use App\Models\Officer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'birthday:send-greetings';

    protected $description = 'Send birthday greeting emails to officers whose birthday is today';

    public function handle(): int
    {
        $this->info('Checking for officers with birthday today...');

        $officers = Officer::query()
            ->whereNotNull('date_of_birth')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();

        $count = $officers->count();
        if ($count === 0) {
            $this->info('No officers have a birthday today.');
            return self::SUCCESS;
        }

        $this->info("Sending birthday greetings to {$count} officer(s).");

        foreach ($officers as $officer) {
            SendBirthdayGreetingMailJob::dispatch($officer);
            $this->line('  Queued: ' . trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')) . " ({$officer->email})");
        }

        Log::info('Birthday greetings queued', [
            'count' => $count,
            'date' => now()->toDateString(),
        ]);

        $this->info('Done.');
        return self::SUCCESS;
    }
}
