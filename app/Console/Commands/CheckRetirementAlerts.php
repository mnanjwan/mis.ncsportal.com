<?php

namespace App\Console\Commands;

use App\Models\Officer;
use App\Models\RetirementAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckRetirementAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retirement:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for officers approaching retirement (3 months before) and send alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for officers approaching retirement...');

        // Get all active, non-deceased officers
        $officers = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->whereNotNull('date_of_birth')
            ->whereNotNull('date_of_first_appointment')
            ->get();

        $alertCount = 0;
        $today = now()->startOfDay();

        foreach ($officers as $officer) {
            $retirementDate = $officer->calculateRetirementDate();
            $alertDate = $officer->getAlertDate();
            $retirementType = $officer->getRetirementType();

            if (!$retirementDate || !$alertDate || !$retirementType) {
                continue;
            }

            // Check if today is the alert date (3 months before retirement)
            $alertDateStart = $alertDate->startOfDay();
            $alertDateEnd = $alertDate->copy()->endOfDay();

            if ($today->between($alertDateStart, $alertDateEnd)) {
                // Check if alert already exists and hasn't been sent
                $existingAlert = RetirementAlert::where('officer_id', $officer->id)
                    ->where('retirement_date', $retirementDate->format('Y-m-d'))
                    ->first();

                if (!$existingAlert) {
                    // Create new retirement alert
                    DB::beginTransaction();
                    try {
                        RetirementAlert::create([
                            'officer_id' => $officer->id,
                            'retirement_date' => $retirementDate,
                            'retirement_type' => $retirementType,
                            'alert_date' => $alertDate,
                            'alert_sent' => true,
                            'alert_sent_at' => now(),
                        ]);

                        $this->info("Alert created for officer: {$officer->service_number} - {$officer->full_name} (Retirement: {$retirementDate->format('Y-m-d')})");
                        $alertCount++;

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Failed to create alert for officer {$officer->service_number}: {$e->getMessage()}");
                        Log::error("Retirement alert creation failed", [
                            'officer_id' => $officer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } elseif (!$existingAlert->alert_sent) {
                    // Update existing alert to mark as sent
                    $existingAlert->update([
                        'alert_sent' => true,
                        'alert_sent_at' => now(),
                    ]);

                    $this->info("Alert marked as sent for officer: {$officer->service_number} - {$officer->full_name}");
                    $alertCount++;
                }
            }
        }

        $this->info("Retirement alert check completed. {$alertCount} alert(s) processed.");

        return Command::SUCCESS;
    }
}
