<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmolumentTimeline;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExtendEmolumentTimeline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emolument:extend-timeline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically extend emolument timelines that are ending soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for emolument timelines that need extension...');

        // Get active timelines ending within the next 3 days
        $timelines = EmolumentTimeline::where('is_active', true)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Original end date is within 3 days
                    $q->where('is_extended', false)
                      ->where('end_date', '<=', Carbon::now()->addDays(3))
                      ->where('end_date', '>=', Carbon::now());
                })->orWhere(function ($q) {
                    // Extended end date is within 3 days
                    $q->where('is_extended', true)
                      ->whereNotNull('extension_end_date')
                      ->where('extension_end_date', '<=', Carbon::now()->addDays(3))
                      ->where('extension_end_date', '>=', Carbon::now());
                });
            })
            ->get();

        if ($timelines->isEmpty()) {
            $this->info('No timelines need extension at this time.');
            return 0;
        }

        $extendedCount = 0;

        foreach ($timelines as $timeline) {
            try {
                // Determine current end date
                $currentEndDate = $timeline->is_extended && $timeline->extension_end_date
                    ? Carbon::parse($timeline->extension_end_date)
                    : Carbon::parse($timeline->end_date);

                // Extend by 7 days (configurable via system settings)
                $newEndDate = $currentEndDate->copy()->addDays(7);

                // Update timeline
                $timeline->update([
                    'is_extended' => true,
                    'extension_end_date' => $newEndDate,
                ]);

                $extendedCount++;
                $this->info("Extended timeline for year {$timeline->year} to {$newEndDate->format('Y-m-d')}");

                // Log the extension
                Log::info("Emolument timeline auto-extended", [
                    'timeline_id' => $timeline->id,
                    'year' => $timeline->year,
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                ]);

                // TODO: Send notification to all officers when notification system is available
                // $this->notifyOfficers($timeline);

            } catch (\Exception $e) {
                $this->error("Failed to extend timeline for year {$timeline->year}: " . $e->getMessage());
                Log::error("Failed to auto-extend emolument timeline", [
                    'timeline_id' => $timeline->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully extended {$extendedCount} timeline(s).");
        return 0;
    }

    /**
     * Notify all officers about timeline extension
     * TODO: Implement when notification system is available
     */
    private function notifyOfficers(EmolumentTimeline $timeline)
    {
        // Get all active officers
        // Send notification about timeline extension
        // Example:
        // $officers = Officer::where('is_active', true)->where('is_deceased', false)->get();
        // foreach ($officers as $officer) {
        //     if ($officer->user) {
        //         $officer->user->notify(new EmolumentTimelineExtendedNotification($timeline));
        //     }
        // }
    }
}

