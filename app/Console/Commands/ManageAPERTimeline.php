<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\APERTimeline;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ManageAPERTimeline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aper:manage-timeline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage APER timelines - deactivate expired timelines and check for upcoming deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking APER timelines...');

        // Deactivate timelines that have passed their end date
        $expiredTimelines = APERTimeline::where('is_active', true)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Original end date has passed and not extended
                    $q->where('is_extended', false)
                      ->where('end_date', '<', Carbon::now());
                })->orWhere(function ($q) {
                    // Extended end date has passed
                    $q->where('is_extended', true)
                      ->whereNotNull('extension_end_date')
                      ->where('extension_end_date', '<', Carbon::now());
                });
            })
            ->get();

        $deactivatedCount = 0;

        foreach ($expiredTimelines as $timeline) {
            try {
                $timeline->update(['is_active' => false]);
                $deactivatedCount++;
                $this->info("Deactivated timeline for year {$timeline->year}");

                Log::info("APER timeline deactivated", [
                    'timeline_id' => $timeline->id,
                    'year' => $timeline->year,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to deactivate timeline for year {$timeline->year}: " . $e->getMessage());
                Log::error("Failed to deactivate APER timeline", [
                    'timeline_id' => $timeline->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check for timelines ending soon (within 7 days)
        $endingSoon = APERTimeline::where('is_active', true)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('is_extended', false)
                      ->where('end_date', '<=', Carbon::now()->addDays(7))
                      ->where('end_date', '>=', Carbon::now());
                })->orWhere(function ($q) {
                    $q->where('is_extended', true)
                      ->whereNotNull('extension_end_date')
                      ->where('extension_end_date', '<=', Carbon::now()->addDays(7))
                      ->where('extension_end_date', '>=', Carbon::now());
                });
            })
            ->get();

        if ($endingSoon->isNotEmpty()) {
            $this->warn("Found {$endingSoon->count()} timeline(s) ending within 7 days:");
            foreach ($endingSoon as $timeline) {
                $endDate = $timeline->is_extended && $timeline->extension_end_date
                    ? Carbon::parse($timeline->extension_end_date)
                    : Carbon::parse($timeline->end_date);
                $this->line("  - Year {$timeline->year}: ends on {$endDate->format('Y-m-d')}");
            }
        }

        $this->info("Successfully deactivated {$deactivatedCount} timeline(s).");
        return 0;
    }
}

