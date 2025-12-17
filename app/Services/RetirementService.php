<?php

namespace App\Services;

use App\Models\RetirementList;
use App\Models\RetirementListItem;
use App\Models\Officer;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RetirementService
{
    /**
     * Activate pre-retirement status for officers in retirement lists
     */
    public function activatePreRetirementStatus(RetirementList $list)
    {
        try {
            $items = $list->items()->with('officer')->get();
            $activatedCount = 0;

            foreach ($items as $item) {
                $officer = $item->officer;
                if (!$officer) {
                    continue;
                }

                // Check if pre-retirement leave date has arrived
                $preRetirementDate = Carbon::parse($item->date_of_pre_retirement_leave);
                
                if (now()->greaterThanOrEqualTo($preRetirementDate) && !$item->notified) {
                    // Activate pre-retirement status
                    // Note: This assumes a status field or separate table exists
                    // For now, we'll log the activation
                    
                    Log::info("Pre-retirement status activated for officer", [
                        'officer_id' => $officer->id,
                        'retirement_list_id' => $list->id,
                        'pre_retirement_date' => $preRetirementDate->format('Y-m-d'),
                        'retirement_date' => $item->retirement_date->format('Y-m-d'),
                    ]);

                    // Mark as notified
                    $item->update(['notified' => true]);
                    $activatedCount++;

                    // TODO: Send notifications when notification system is available
                    // $this->notifyRetiringOfficer($officer, $item);
                    // $this->notifyAccounts($officer, $item);
                    // $this->notifyWelfare($officer, $item);
                }
            }

            return $activatedCount;
        } catch (\Exception $e) {
            Log::error("Failed to activate pre-retirement status: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check and activate pre-retirement status for all active retirement lists
     */
    public function checkAndActivatePreRetirementStatus()
    {
        $lists = RetirementList::where('status', '!=', 'CANCELLED')
            ->with('items.officer')
            ->get();

        $totalActivated = 0;

        foreach ($lists as $list) {
            try {
                $activated = $this->activatePreRetirementStatus($list);
                $totalActivated += $activated;
            } catch (\Exception $e) {
                Log::error("Failed to process retirement list {$list->id}: " . $e->getMessage());
            }
        }

        return $totalActivated;
    }

    /**
     * Notify retiring officer
     * TODO: Implement when notification system is available
     */
    private function notifyRetiringOfficer(Officer $officer, RetirementListItem $item)
    {
        // Send notification to officer about approaching retirement
        // Example:
        // if ($officer->user) {
        //     $officer->user->notify(new PreRetirementNotification($item));
        // }
    }

    /**
     * Notify Accounts department
     * TODO: Implement when notification system is available
     */
    private function notifyAccounts(Officer $officer, RetirementListItem $item)
    {
        // Send notification to Accounts for benefits processing
        // Example:
        // $accountsUsers = User::whereHas('roles', function($q) {
        //     $q->where('name', 'Accounts');
        // })->get();
        // foreach ($accountsUsers as $user) {
        //     $user->notify(new OfficerRetiringNotification($officer, $item));
        // }
    }

    /**
     * Notify Welfare department
     * TODO: Implement when notification system is available
     */
    private function notifyWelfare(Officer $officer, RetirementListItem $item)
    {
        // Send notification to Welfare for transition support
        // Example:
        // $welfareUsers = User::whereHas('roles', function($q) {
        //     $q->where('name', 'Welfare');
        // })->get();
        // foreach ($welfareUsers as $user) {
        //     $user->notify(new OfficerRetiringNotification($officer, $item));
        // }
    }
}

