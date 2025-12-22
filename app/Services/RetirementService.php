<?php

namespace App\Services;

use App\Models\RetirementList;
use App\Models\RetirementListItem;
use App\Models\Officer;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
                
                if (now()->greaterThanOrEqualTo($preRetirementDate) && 
                    !$item->notified && 
                    !$item->preretirement_leave_status) {
                    
                    DB::beginTransaction();
                    try {
                        // Automatically place officer on preretirement leave
                        $item->update([
                            'notified' => true,
                            'notified_at' => now(),
                            'preretirement_leave_status' => 'AUTO_PLACED',
                            'auto_placed_at' => now(),
                        ]);

                        // Update officer status
                        $officer->update([
                            'preretirement_leave_status' => 'ON_PRERETIREMENT_LEAVE',
                            'preretirement_leave_started_at' => now(),
                        ]);

                        // Send notifications
                        $this->notifyRetiringOfficer($officer, $item);
                        $this->notifyCGC($officer, $item);
                        $this->notifyAccounts($officer, $item);
                        $this->notifyWelfare($officer, $item);

                        DB::commit();

                        Log::info("Pre-retirement leave automatically placed for officer", [
                            'officer_id' => $officer->id,
                            'retirement_list_id' => $list->id,
                            'pre_retirement_date' => $preRetirementDate->format('Y-m-d'),
                            'retirement_date' => $item->retirement_date->format('Y-m-d'),
                        ]);

                        $activatedCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to auto-place preretirement leave for officer {$officer->id}: " . $e->getMessage());
                        throw $e;
                    }
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
     * Notify retiring officer about automatic preretirement leave placement
     */
    private function notifyRetiringOfficer(Officer $officer, RetirementListItem $item)
    {
        if ($officer->user) {
            Notification::create([
                'user_id' => $officer->user->id,
                'notification_type' => 'PRERETIREMENT_LEAVE_PLACED',
                'title' => 'Preretirement Leave Automatically Placed',
                'message' => "You have been automatically placed on preretirement leave effective {$item->date_of_pre_retirement_leave->format('Y-m-d')}. Your retirement date is {$item->retirement_date->format('Y-m-d')}.",
                'data' => [
                    'officer_id' => $officer->id,
                    'retirement_list_item_id' => $item->id,
                    'preretirement_leave_date' => $item->date_of_pre_retirement_leave->format('Y-m-d'),
                    'retirement_date' => $item->retirement_date->format('Y-m-d'),
                    'status' => 'AUTO_PLACED',
                ],
            ]);
        }
    }

    /**
     * Notify CGC about officers placed on preretirement leave
     */
    private function notifyCGC(Officer $officer, RetirementListItem $item)
    {
        $cgcUsers = User::whereHas('roles', function ($query) {
            $query->where('code', 'CGC');
        })->get();

        foreach ($cgcUsers as $cgcUser) {
            Notification::create([
                'user_id' => $cgcUser->id,
                'notification_type' => 'PRERETIREMENT_LEAVE_AUTO_PLACED',
                'title' => 'Officer Automatically Placed on Preretirement Leave',
                'message' => "Officer {$officer->service_number} ({$officer->full_name}) has been automatically placed on preretirement leave. Retirement date: {$item->retirement_date->format('Y-m-d')}",
                'data' => [
                    'officer_id' => $officer->id,
                    'retirement_list_item_id' => $item->id,
                    'preretirement_leave_date' => $item->date_of_pre_retirement_leave->format('Y-m-d'),
                    'retirement_date' => $item->retirement_date->format('Y-m-d'),
                ],
            ]);
        }
    }

    /**
     * Notify Accounts department
     */
    private function notifyAccounts(Officer $officer, RetirementListItem $item)
    {
        $accountsUsers = User::whereHas('roles', function ($query) {
            $query->where('code', 'ACCOUNTS');
        })->get();

        foreach ($accountsUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'OFFICER_PRERETIREMENT',
                'title' => 'Officer on Preretirement Leave',
                'message' => "Officer {$officer->service_number} ({$officer->full_name}) is on preretirement leave. Retirement date: {$item->retirement_date->format('Y-m-d')}",
                'data' => [
                    'officer_id' => $officer->id,
                    'retirement_list_item_id' => $item->id,
                    'retirement_date' => $item->retirement_date->format('Y-m-d'),
                ],
            ]);
        }
    }

    /**
     * Notify Welfare department
     */
    private function notifyWelfare(Officer $officer, RetirementListItem $item)
    {
        $welfareUsers = User::whereHas('roles', function ($query) {
            $query->where('code', 'WELFARE');
        })->get();

        foreach ($welfareUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'OFFICER_PRERETIREMENT',
                'title' => 'Officer on Preretirement Leave',
                'message' => "Officer {$officer->service_number} ({$officer->full_name}) is on preretirement leave. Retirement date: {$item->retirement_date->format('Y-m-d')}",
                'data' => [
                    'officer_id' => $officer->id,
                    'retirement_list_item_id' => $item->id,
                    'retirement_date' => $item->retirement_date->format('Y-m-d'),
                ],
            ]);
        }
    }
}

