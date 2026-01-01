<?php

namespace App\Jobs;

use App\Mail\APERCountersigningAvailableMail;
use App\Models\APERForm;
use App\Models\Notification;
use App\Models\Officer;
use App\Models\User;
use App\Services\RankComparisonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAPERCountersigningPoolMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public APERForm $form)
    {
    }

    public function handle(RankComparisonService $rankService)
    {
        $evaluatedOfficer = $this->form->officer;
        $reportingOfficer = $this->form->reportingOfficer->officer ?? null;

        if (!$evaluatedOfficer || !$reportingOfficer) {
            return;
        }

        $commandId = $evaluatedOfficer->present_station;

        // Find all officers in the same command
        $potentialCSOs = Officer::where('present_station', $commandId)
            ->where('id', '!=', $evaluatedOfficer->id) // Not the officer being evaluated
            ->where('id', '!=', $reportingOfficer->id) // Not the officer who filled the form initially
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email');
            })
            ->with('user')
            ->get();

        foreach ($potentialCSOs as $csoOfficer) {
            // Rank Verification: CSO Rank >= Reporting Officer Rank
            if ($rankService->isRankHigherOrEqual($csoOfficer->id, $reportingOfficer->id)) {
                if ($csoOfficer->user) {
                    // Send email notification
                    if ($csoOfficer->user->email) {
                        Mail::to($csoOfficer->user->email)->send(new APERCountersigningAvailableMail($this->form, $csoOfficer->user));
                    }
                    
                    // Create app notification
                    Notification::create([
                        'user_id' => $csoOfficer->user->id,
                        'notification_type' => 'APER_COUNTERSIGNING_AVAILABLE',
                        'title' => 'APER Form Available for Countersigning',
                        'message' => "APER form for {$evaluatedOfficer->initials} {$evaluatedOfficer->surname} ({$evaluatedOfficer->service_number}) is ready for countersigning.",
                        'entity_type' => 'APERForm',
                        'entity_id' => $this->form->id,
                        'is_read' => false,
                    ]);
                }
            }
        }
    }
}
