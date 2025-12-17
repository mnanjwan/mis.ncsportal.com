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
     */
    public function processMovementOrder(MovementOrder $order, $officerIds = [])
    {
        DB::beginTransaction();
        try {
            // Movement orders may have multiple officers
            // Process each officer posting
            foreach ($officerIds as $officerId) {
                $officer = Officer::find($officerId);
                if (!$officer) {
                    continue;
                }

                // Get destination command from manning request or criteria
                $toCommand = $this->getDestinationCommand($order);
                if (!$toCommand) {
                    continue;
                }

                $fromCommand = $officer->presentStation;

                // Update officer's present_station
                $officer->update([
                    'present_station' => $toCommand->id,
                    'date_posted_to_station' => now(),
                ]);

                // Log the posting
                Log::info("Movement Order {$order->order_number}: Officer {$officer->id} posted from " . 
                    ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}");

                // TODO: Implement when chat room system is available
                // $this->transferChatRoom($officer, $fromCommand, $toCommand);

                // TODO: Implement when notification system is available
                // $this->notifyStaffOfficer($toCommand, $officer, $order);
                // $this->notifyOfficer($officer, $order);

                // TODO: Implement when nominal roll system is available
                // $this->updateNominalRolls($officer, $fromCommand, $toCommand);
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
     * Get destination command for movement order
     */
    private function getDestinationCommand(MovementOrder $order)
    {
        if ($order->manningRequest) {
            return $order->manningRequest->command;
        }
        // If no manning request, destination would need to be specified
        // This is a placeholder for future implementation
        return null;
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
        $staffOfficers = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Staff Officer')
              ->wherePivot('is_active', true);
        })->whereHas('roles', function($q) use ($command) {
            $q->wherePivot('command_id', $command->id)
              ->wherePivot('is_active', true);
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
            // Log notification (can be enhanced with actual notification system)
            Log::info("Officer notification: {$officer->user->email} - Posted to {$order->toCommand->name} via {$order->order_number}");
            
            // TODO: When notification system is ready, uncomment:
            // $officer->user->notify(new \App\Notifications\OfficerPostingNotification($order));
        }
    }
}

