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

            // Create a PENDING posting (awaiting release letter and acceptance)
            if ($updateOfficer) {
                // Create new posting record (pending - awaiting release letter and acceptance)
                OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'staff_order_id' => $order->id,
                    'posting_date' => $order->effective_date ?? now(),
                    'is_current' => false, // becomes current only after acceptance
                    'documented_by' => null, // Will be set when new command accepts
                    'documented_at' => null, // Set when new command accepts
                    'release_letter_printed' => false, // Will be set when old command prints release letter
                    'release_letter_printed_at' => null,
                    'release_letter_printed_by' => null,
                    'accepted_by_new_command' => false, // Will be set when new command accepts
                    'accepted_at' => null,
                    'accepted_by' => null,
                ]);
                
                // Log the posting
                Log::info("Staff Order {$order->order_number}: Pending posting created for Officer {$officer->id} ({$officer->service_number}) from " . 
                    ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}. Awaiting release letter and acceptance.");
                
                // Notify FROM command Staff Officers about pending release letter
                $notificationService = app(\App\Services\NotificationService::class);
                try {
                    $notificationService->notifyCommandOfficerRelease($officer, $fromCommand, $toCommand, $order);
                } catch (\Exception $e) {
                    Log::warning("Failed to send release letter notification: " . $e->getMessage());
                }
                
                // DO NOT notify officer yet - notification happens when release letter is printed
                // DO NOT notify Staff Officer of new command yet - they will see pending arrivals after release letter is printed
                
                // Transfer chat room (when chat system is available) - will happen after acceptance
                // $this->transferChatRoom($officer, $fromCommand, $toCommand);
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

                // Keep officer in old command until release letter is printed AND new command accepts.
                // Ensure posting stays pending (is_current=false) until acceptance.
                // Note: This method is called when movement order is published, but postings should already be created
                // with the new workflow fields. This is a legacy method that may not be used anymore.
                
                // Ensure new workflow fields are set if not already
                if (!$posting->release_letter_printed && !$posting->accepted_by_new_command) {
                    $posting->update([
                        'is_current' => false,
                        'posting_date' => $posting->posting_date ?? now(),
                        'release_letter_printed' => false,
                        'accepted_by_new_command' => false,
                    ]);
                }

                // Log the posting
                Log::info("Movement Order {$order->order_number}: Posting for Officer {$officer->id} ({$officer->service_number}) from " . 
                    ($fromCommand ? $fromCommand->name : 'Unknown') . " to {$toCommand->name}. Awaiting release letter and acceptance.");

                // DO NOT notify Staff Officer or officer yet - notifications happen at appropriate stages
                // Staff Officer will see pending arrivals after release letter is printed
                // Officer will be notified when release letter is printed
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

