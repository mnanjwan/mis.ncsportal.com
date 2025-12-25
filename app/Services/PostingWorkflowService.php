<?php

namespace App\Services;

use App\Models\Officer;
use App\Models\StaffOrder;
use App\Models\MovementOrder;
use App\Models\Command;
use App\Models\OfficerPosting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PostingWorkflowService
{
    /**
     * Process staff order posting workflow
     * Updates officer's present_station and triggers notifications
     */
    public function processStaffOrder(StaffOrder $order, $updateOfficer = true)
    {
        DB::beginTransaction();
        try {
            $officer = $order->officer;
            $fromCommand = $order->fromCommand;
            $toCommand = $order->toCommand;

            if (!$officer || !$toCommand) {
                throw new \Exception('Officer or destination command not found');
            }

            // Update officer's present_station (this automatically updates nominal roll since nominal roll is based on present_station)
            if ($updateOfficer) {
                // Mark previous posting as not current
                OfficerPosting::where('officer_id', $officer->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
                
                // Create new posting record (not yet documented)
                OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'staff_order_id' => $order->id,
                    'posting_date' => $order->effective_date ?? now(),
                    'is_current' => true,
                    'documented_at' => null, // Will be set when Staff Officer documents
                ]);
                
                $officer->update([
                    'present_station' => $toCommand->id,
                    'date_posted_to_station' => $order->effective_date ?? now(),
                ]);
                
                // Log the posting
                Log::info("Staff Order {$order->order_number}: Officer {$officer->id} ({$officer->service_number}) posted from " . 
                    ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}");
                
                // Nominal roll is automatically updated since it's based on present_station
                // When present_station changes, officer automatically:
                // - Leaves old command's nominal roll (officers are filtered by present_station)
                // - Joins new command's nominal roll
                
                // Notify Staff Officer of new posting
                $this->notifyStaffOfficer($toCommand, $officer, $order);
                
                // Notify officer of their posting
                $this->notifyOfficer($officer, $order);
                
                // Transfer chat room (when chat system is available)
                $this->transferChatRoom($officer, $fromCommand, $toCommand);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process staff order workflow: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process movement order posting workflow
     * Implements the complete workflow from specification:
     * 1. Update officer's present_station (automatically updates nominal roll)
     * 2. Create/update posting records
     * 3. Notify Staff Officer
     * 4. Notify officer
     * 5. Transfer chat room
     */
    public function processMovementOrder(MovementOrder $order, $officerIds = [])
    {
        DB::beginTransaction();
        try {
            // Get postings for this movement order
            $postings = OfficerPosting::where('movement_order_id', $order->id)
                ->whereIn('officer_id', $officerIds)
                ->with(['officer', 'command'])
                ->get();

            foreach ($postings as $posting) {
                $officer = $posting->officer;
                $toCommand = $posting->command;
                
                if (!$officer || !$toCommand) {
                    continue;
                }

                $fromCommand = $officer->presentStation;

                // Mark previous posting as not current
                OfficerPosting::where('officer_id', $officer->id)
                    ->where('id', '!=', $posting->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                // Update posting record
                $posting->update([
                    'is_current' => true,
                    'posting_date' => $posting->posting_date ?? now(),
                ]);

                // Update officer's present_station (this automatically updates nominal roll)
                $officer->update([
                    'present_station' => $toCommand->id,
                    'date_posted_to_station' => $posting->posting_date ?? now(),
                ]);

                // Log the posting
                Log::info("Movement Order {$order->order_number}: Officer {$officer->id} ({$officer->service_number}) posted from " . 
                    ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}");

                // Nominal roll is automatically updated since it's based on present_station
                // When present_station changes, officer automatically:
                // - Leaves old command's nominal roll (officers are filtered by present_station)
                // - Joins new command's nominal roll

                // Notify Staff Officer of new posting
                $this->notifyStaffOfficer($toCommand, $officer, $order);

                // Notify officer of their posting
                $this->notifyOfficer($officer, $order);

                // Transfer chat room
                $this->transferChatRoom($officer, $fromCommand, $toCommand);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process movement order workflow: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Transfer officer to new command chat room
     * Note: This will be implemented when chat room system is available
     */
    private function transferChatRoom(Officer $officer, $fromCommand, Command $toCommand)
    {
        // Implementation depends on chat room system structure
        // When chat room system is available:
        // 1. Remove officer from fromCommand chat room
        // 2. Add officer to toCommand chat room
        // 3. Log the transfer
        
        Log::info("Chat room transfer needed: Officer {$officer->id} from " . 
            ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}");
    }

    /**
     * Notify Staff Officer of new officer posting
     */
    private function notifyStaffOfficer(Command $command, Officer $officer, $order)
    {
        // Get Staff Officers for the command
        // Query users who have Staff Officer role with the specific command_id and is_active = true
        $staffOfficers = \App\Models\User::whereHas('roles', function($q) use ($command) {
            $q->where('roles.name', 'Staff Officer')
              ->where('user_roles.is_active', true)
              ->where('user_roles.command_id', $command->id);
        })->get();
        
        foreach ($staffOfficers as $staffOfficer) {
            // Log notification (can be enhanced with actual notification system)
            Log::info("Staff Officer notification: {$staffOfficer->email} - New officer {$officer->service_number} posted to {$command->name}");
            
            // TODO: When notification system is ready, uncomment:
            // $staffOfficer->notify(new \App\Notifications\NewOfficerPostingNotification($officer, $order));
        }
    }

    /**
     * Notify officer of their posting
     */
    private function notifyOfficer(Officer $officer, $order)
    {
        if ($officer->user) {
            $fromCommand = $officer->presentStation;
            $toCommand = null;
            $postingDate = null;
            
            if ($order instanceof StaffOrder) {
                $fromCommand = $order->fromCommand;
                $toCommand = $order->toCommand;
                $postingDate = $order->effective_date ?? now();
            } elseif ($order instanceof MovementOrder) {
                // Get the command from the posting
                $posting = OfficerPosting::where('movement_order_id', $order->id)
                    ->where('officer_id', $officer->id)
                    ->with('command')
                    ->first();
                $toCommand = $posting ? $posting->command : null;
                $postingDate = $posting ? ($posting->posting_date ?? now()) : now();
            }

            $commandName = $toCommand ? $toCommand->name : 'Unknown';
            Log::info("Officer notification: {$officer->user->email} - Posted to {$commandName} via {$order->order_number}");
            
            // Send notification using NotificationService
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyOfficerPosted($officer, $fromCommand, $toCommand, $postingDate);
            } catch (\Exception $e) {
                Log::warning("Failed to send posting notification: " . $e->getMessage());
            }
        }
    }
}

