<?php

namespace App\Jobs;

use App\Mail\APERCountersigningAvailableMail;
use App\Models\APERForm;
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
                if ($csoOfficer->user && $csoOfficer->user->email) {
                    Mail::to($csoOfficer->user->email)->send(new APERCountersigningAvailableMail($this->form, $csoOfficer->user));
                }
            }
        }
    }
}
