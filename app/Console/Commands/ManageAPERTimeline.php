<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\APERTimeline;
use App\Models\APERForm;
use App\Models\Officer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        // Check for timelines ending soon (within 7 days) and send notifications
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

        $notificationsSent = 0;
        foreach ($endingSoon as $timeline) {
            $endDate = $timeline->is_extended && $timeline->extension_end_date
                ? Carbon::parse($timeline->extension_end_date)
                : Carbon::parse($timeline->end_date);
            
            $daysRemaining = Carbon::now()->diffInDays($endDate, false);
            
            $this->warn("Timeline for year {$timeline->year} ends on {$endDate->format('Y-m-d')} ({$daysRemaining} days remaining)");
            
            // Send notifications to officers who haven't submitted
            $officers = Officer::whereHas('user', function($query) {
                $query->whereNotNull('email');
            })->get();
            
            foreach ($officers as $officer) {
                if (!$officer->user || !$officer->user->email) {
                    continue;
                }
                
                // Check if officer has submitted form for this timeline
                $submittedForm = APERForm::where('officer_id', $officer->id)
                    ->where('timeline_id', $timeline->id)
                    ->where('status', '!=', 'DRAFT')
                    ->first();
                
                if (!$submittedForm) {
                    // Check if they have a draft
                    $draftForm = APERForm::where('officer_id', $officer->id)
                        ->where('timeline_id', $timeline->id)
                        ->where('status', 'DRAFT')
                        ->first();
                    
                    try {
                        Mail::to($officer->user->email)->send(
                            new \App\Mail\APERTimelineClosingMail(
                                $officer,
                                $timeline,
                                $daysRemaining,
                                $draftForm ? true : false,
                                $draftForm ? $draftForm->id : null
                            )
                        );
                        $notificationsSent++;
                    } catch (\Exception $e) {
                        $this->error("Failed to send notification to {$officer->user->email}: " . $e->getMessage());
                        Log::error("Failed to send APER timeline closing notification", [
                            'officer_id' => $officer->id,
                            'email' => $officer->user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
        
        if ($notificationsSent > 0) {
            $this->info("Sent {$notificationsSent} deadline reminder notification(s).");
        }

        $this->info("Successfully deactivated {$deactivatedCount} timeline(s).");
        return 0;
    }
}

