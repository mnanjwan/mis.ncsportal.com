<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Officer;
use App\Models\OfficerPosting;
use App\Models\Command;
use App\Models\Zone;
use App\Models\ManningDeployment;
use App\Models\ManningDeploymentAssignment;
use App\Models\MovementOrder;
use Carbon\Carbon;

class CommandDurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD|Super Admin');
    }

    /**
     * Display the command duration search page
     */
    public function index(Request $request)
    {
        // Get all zones for dropdown
        $zones = Zone::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get commands based on selected zone
        $commands = collect();
        $selectedZoneId = $request->filled('zone_id') ? $request->zone_id : null;
        if ($selectedZoneId) {
            $commands = Command::where('zone_id', $selectedZoneId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // If AJAX request, return JSON with commands
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'commands' => $commands->map(function($cmd) {
                    return ['id' => $cmd->id, 'name' => $cmd->name];
                })
            ]);
        }

        // Get unique ranks for filter and sort from lowest to highest
        $ranks = Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->values()
            ->toArray();
        
        // Sort ranks from lowest to highest
        $ranks = $this->sortRanks($ranks);

        return view('dashboards.hrd.command-duration.index', compact(
            'zones',
            'commands',
            'ranks'
        ))->with([
            'selected_zone_id' => $selectedZoneId,
        ]);
    }

    /**
     * Search officers based on filters
     */
    public function search(Request $request)
    {
        // If GET request, redirect to index with parameters
        if ($request->isMethod('get')) {
            return redirect()->route('hrd.command-duration.index', $request->only(['zone_id', 'command_id', 'rank', 'sex', 'duration_years']));
        }
        
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'command_id' => 'required|exists:commands,id',
        ]);

        $zoneId = $request->zone_id;
        $commandId = $request->command_id;

        // Verify command belongs to zone
        $command = Command::findOrFail($commandId);
        if ($command->zone_id != $zoneId) {
            return back()->withErrors(['command_id' => 'Selected command does not belong to the selected zone.']);
        }

        // Build query
        $query = Officer::with(['presentStation.zone', 'currentPosting'])
            ->where('present_station', $commandId)
            ->whereHas('currentPosting', function($q) use ($commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
            });

        // Optional filters
        if ($request->filled('rank')) {
            $query->where('substantive_rank', $request->rank);
        }

        if ($request->filled('sex') && $request->sex !== 'Any') {
            // Convert "Male"/"Female" to "M"/"F" for database query
            $sexValue = $request->sex === 'Male' ? 'M' : ($request->sex === 'Female' ? 'F' : $request->sex);
            $query->where('sex', $sexValue);
        }

        // Command Duration Filter
        if ($request->filled('duration_years') && $request->duration_years !== '') {
            $durationYears = (int) $request->duration_years;
            
            $query->whereHas('currentPosting', function($q) use ($durationYears, $commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
                
                if ($durationYears == 10) {
                    // 10+ years: >= 10 years
                    $dateThreshold = now()->subYears(10);
                    $q->where('posting_date', '<=', $dateThreshold);
                } else {
                    // Exact year: between X and X+1 years
                    $dateThreshold = now()->subYears($durationYears);
                    $nextYear = now()->subYears($durationYears + 1);
                    $q->where('posting_date', '<=', $dateThreshold)
                      ->where('posting_date', '>', $nextYear);
                }
            });
        }

        // Get officers
        $officers = $query->get();

        // Calculate duration and status for each officer
        $officers = $officers->map(function($officer) {
            $posting = $officer->currentPosting;
            
            if ($posting && $posting->posting_date) {
                $diff = $posting->posting_date->diff(now());
                $officer->duration_years = $diff->y;
                $officer->duration_months = $diff->m;
                $officer->duration_display = "{$diff->y} Years {$diff->m} Months";
                $officer->date_posted_to_command = $posting->posting_date;
            } else {
                $officer->duration_years = 0;
                $officer->duration_months = 0;
                $officer->duration_display = 'N/A';
                $officer->date_posted_to_command = null;
            }

            // Get status
            $officer->current_status = $this->getOfficerStatus($officer);
            $officer->is_eligible_for_movement = $this->isEligibleForMovement($officer);
            $officer->is_in_draft = $this->isInDraft($officer->id);

            return $officer;
        });

        // Get filter data for view
        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        $commands = Command::where('zone_id', $zoneId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $ranks = Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->values()
            ->toArray();
        
        // Sort ranks from lowest to highest
        $ranks = $this->sortRanks($ranks);

        return view('dashboards.hrd.command-duration.index', compact(
            'officers',
            'zones',
            'commands',
            'ranks'
        ))->with([
            'selected_zone_id' => $zoneId,
            'selected_command_id' => $commandId,
            'selected_rank' => $request->rank,
            'selected_sex' => $request->sex,
            'selected_duration' => $request->duration_years,
        ]);
    }

    /**
     * Add selected officers to draft deployment
     */
    public function addToDraft(Request $request)
    {
        $request->validate([
            'officer_ids' => 'required|string', // JSON string
            'command_id' => 'required|exists:commands,id',
        ]);

        try {
            DB::beginTransaction();

            // Decode JSON string to array
            $officerIds = json_decode($request->officer_ids, true);
            if (!is_array($officerIds) || empty($officerIds)) {
                return back()->withErrors(['officers' => 'No officers selected.']);
            }

            // Validate officer IDs exist
            $validOfficerIds = Officer::whereIn('id', $officerIds)->pluck('id')->toArray();
            if (count($validOfficerIds) !== count($officerIds)) {
                return back()->withErrors(['officers' => 'Some selected officers are invalid.']);
            }

            // For command duration search, officers are added to draft without a specific destination
            // HRD will select the destination command in the draft view before publishing
            // Set to_command_id to from_command_id as placeholder (will be changed in draft view)
            // Note: to_command_id cannot be NULL in database, so we use from_command as temporary value
            $toCommandId = null; // Will be set to from_command_id for each officer

            // Validate officers are eligible
            $officers = Officer::whereIn('id', $officerIds)->get();
            $ineligible = [];
            $eligible = [];

            foreach ($officers as $officer) {
                if (!$this->isEligibleForMovement($officer)) {
                    $ineligible[] = $officer->full_name;
                } else {
                    $eligible[] = $officer;
                }
            }

            if (!empty($ineligible)) {
                DB::rollBack();
                return back()->withErrors([
                    'officers' => 'Some officers are not eligible for movement: ' . implode(', ', $ineligible)
                ]);
            }

            // Get or create draft deployment
            $deployment = ManningDeployment::draft()
                ->latest()
                ->first();

            if (!$deployment) {
                $datePrefix = 'DEP-' . date('Y') . '-' . date('md') . '-';
                $lastDeployment = ManningDeployment::where('deployment_number', 'LIKE', $datePrefix . '%')
                    ->orderBy('deployment_number', 'desc')
                    ->first();

                $newNumber = $lastDeployment ? ((int)substr($lastDeployment->deployment_number, -3)) + 1 : 1;
                $deploymentNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

                $deployment = ManningDeployment::create([
                    'deployment_number' => $deploymentNumber,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                ]);
            }

            // Add officers to draft
            $added = 0;
            $skipped = 0;
            foreach ($eligible as $officer) {
                $fromCommand = $officer->presentStation;

                // Check if officer is already in this deployment
                $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                    ->where('officer_id', $officer->id)
                    ->first();

                if (!$existing) {
                    // Use from_command_id as temporary to_command_id (database requires NOT NULL)
                    // HRD will change this in the draft view to the actual destination
                    $tempToCommandId = $fromCommand?->id;
                    
                    // If no from_command, we need a valid command - use the search command as fallback
                    if (!$tempToCommandId) {
                        $searchCommand = Command::find($request->command_id);
                        $tempToCommandId = $searchCommand?->id;
                    }
                    
                    if (!$tempToCommandId) {
                        // Skip if we can't determine a command
                        continue;
                    }
                    
                    ManningDeploymentAssignment::create([
                        'manning_deployment_id' => $deployment->id,
                        'manning_request_id' => null, // Not from manning request
                        'manning_request_item_id' => null,
                        'officer_id' => $officer->id,
                        'from_command_id' => $fromCommand?->id,
                        'to_command_id' => $tempToCommandId, // Temporary: same as from_command, HRD will change in draft view
                        'rank' => $officer->substantive_rank,
                        'notes' => 'Added from Command Duration search - Destination to be selected',
                    ]);
                    $added++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            if ($added > 0) {
                $message = "{$added} officer(s) added to draft deployment.";
                if ($skipped > 0) {
                    $message .= " {$skipped} officer(s) were already in the draft.";
                }
                return redirect()->route('hrd.command-duration.draft')
                    ->with('success', $message);
            } else {
                return back()->with('info', 'No new officers added. All selected officers are already in the draft.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add officers to draft from command duration: ' . $e->getMessage(), [
                'exception' => $e,
                'officer_ids' => $officerIds,
            ]);

            return back()->withErrors(['error' => 'Failed to add officers to draft. Please try again.']);
        }
    }

    /**
     * Print command duration search results
     */
    public function print(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'command_id' => 'required|exists:commands,id',
        ]);

        $zoneId = $request->zone_id;
        $commandId = $request->command_id;

        $zone = Zone::findOrFail($zoneId);
        $command = Command::findOrFail($commandId);

        // Build same query as search method
        $query = Officer::with(['presentStation.zone', 'currentPosting'])
            ->where('present_station', $commandId)
            ->whereHas('currentPosting', function($q) use ($commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
            });

        // Apply same filters if provided
        if ($request->filled('rank')) {
            $query->where('substantive_rank', $request->rank);
        }

        if ($request->filled('sex') && $request->sex !== 'Any') {
            // Convert "Male"/"Female" to "M"/"F" for database query
            $sexValue = $request->sex === 'Male' ? 'M' : ($request->sex === 'Female' ? 'F' : $request->sex);
            $query->where('sex', $sexValue);
        }

        if ($request->filled('duration_years') && $request->duration_years !== '') {
            $durationYears = (int) $request->duration_years;
            
            $query->whereHas('currentPosting', function($q) use ($durationYears, $commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
                
                if ($durationYears == 10) {
                    $dateThreshold = now()->subYears(10);
                    $q->where('posting_date', '<=', $dateThreshold);
                } else {
                    $dateThreshold = now()->subYears($durationYears);
                    $nextYear = now()->subYears($durationYears + 1);
                    $q->where('posting_date', '<=', $dateThreshold)
                      ->where('posting_date', '>', $nextYear);
                }
            });
        }

        $officers = $query->get();

        // Calculate duration for each officer
        $officers = $officers->map(function($officer) {
            $posting = $officer->currentPosting;
            
            if ($posting && $posting->posting_date) {
                $diff = $posting->posting_date->diff(now());
                $officer->duration_years = $diff->y;
                $officer->duration_months = $diff->m;
                $officer->duration_display = "{$diff->y} Years {$diff->m} Months";
                $officer->date_posted_to_command = $posting->posting_date;
            } else {
                $officer->duration_years = 0;
                $officer->duration_months = 0;
                $officer->duration_display = 'N/A';
                $officer->date_posted_to_command = null;
            }

            $officer->current_status = $this->getOfficerStatus($officer);
            return $officer;
        });

        return view('prints.command-duration', compact('officers', 'zone', 'command', 'request'));
    }

    /**
     * Get officer status string
     */
    private function getOfficerStatus($officer): string
    {
        if ($officer->dismissed) return 'Dismissed';
        if ($officer->suspended) return 'Suspended';
        if ($officer->interdicted) return 'Interdicted';
        if ($officer->ongoing_investigation) return 'Under Investigation';
        return 'Active';
    }

    /**
     * Check if officer is eligible for movement
     */
    private function isEligibleForMovement($officer): bool
    {
        return !$officer->suspended 
            && !$officer->dismissed 
            && !$officer->ongoing_investigation
            && !$officer->interdicted
            && $officer->is_active;
    }

    /**
     * Display Command Duration draft page
     */
    public function draftIndex()
    {
        // Get active draft deployment with assignments from Command Duration only (manning_request_id is null)
        $activeDraft = ManningDeployment::draft()
            ->latest()
            ->first();
        
        $assignmentsByCommand = collect();
        $manningLevels = [];
        
        // Get all commands for searchable select
        $commands = Command::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        if ($activeDraft) {
            // Get only assignments from Command Duration (manning_request_id is null)
            $commandDurationAssignments = $activeDraft->assignments()
                ->whereNull('manning_request_id')
                ->with(['officer.presentStation.zone', 'fromCommand', 'toCommand'])
                ->get();
            
            // Group by destination command (to_command_id)
            $assignmentsByCommand = $commandDurationAssignments->groupBy('to_command_id');
            
            // Get manning levels summary
            foreach ($commandDurationAssignments as $assignment) {
                $commandId = $assignment->to_command_id ?? 'unassigned';
                $commandName = $assignment->toCommand->name ?? 'Unassigned';
                if (!isset($manningLevels[$commandId])) {
                    $manningLevels[$commandId] = [
                        'command_id' => $commandId,
                        'command_name' => $commandName,
                        'officers' => [],
                        'by_rank' => [],
                    ];
                }
                $manningLevels[$commandId]['officers'][] = $assignment->officer;
                $rank = $assignment->officer->substantive_rank ?? 'Unknown';
                if (!isset($manningLevels[$commandId]['by_rank'][$rank])) {
                    $manningLevels[$commandId]['by_rank'][$rank] = 0;
                }
                $manningLevels[$commandId]['by_rank'][$rank]++;
            }
        }
        
        return view('dashboards.hrd.command-duration-draft', compact('activeDraft', 'assignmentsByCommand', 'manningLevels', 'commands'));
    }

    /**
     * Update destination command for an assignment
     */
    public function updateDestination($deploymentId, $assignmentId, Request $request)
    {
        $request->validate([
            'to_command_id' => 'required|exists:commands,id',
        ]);

        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);
            
            if ($deployment->status !== 'DRAFT') {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Can only update destination command in draft deployments.'], 400);
                }
                return redirect()->back()
                    ->with('error', 'Can only update destination command in draft deployments.');
            }
            
            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->whereNull('manning_request_id') // Only allow updates for Command Duration assignments
                ->firstOrFail();
            
            $assignment->update([
                'to_command_id' => $request->to_command_id,
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Destination command updated successfully.',
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Destination command updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to update destination command: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update destination command.'], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to update destination command: ' . $e->getMessage());
        }
    }

    /**
     * Publish Command Duration deployment
     */
    public function publish($id, Request $request)
    {
        try {
            DB::beginTransaction();
            
            $deployment = ManningDeployment::with(['assignments.officer', 'assignments.toCommand', 'assignments.fromCommand'])
                ->findOrFail($id);
            
            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only publish draft deployments.');
            }
            
            // Get only Command Duration assignments (manning_request_id is null)
            $assignmentsToPublish = $deployment->assignments()->whereNull('manning_request_id')->get();
            
            if ($assignmentsToPublish->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No Command Duration assignments found to publish.');
            }
            
            // Validate all assignments have destination commands and are different from from_command
            $assignmentsWithoutDestination = $assignmentsToPublish->filter(function($assignment) {
                return empty($assignment->to_command_id) || $assignment->to_command_id == $assignment->from_command_id;
            });
            
            if ($assignmentsWithoutDestination->isNotEmpty()) {
                $count = $assignmentsWithoutDestination->count();
                return redirect()->back()
                    ->with('error', "Cannot publish deployment. {$count} officer(s) do not have a valid destination command selected (must be different from current command). Please select destination commands for all officers before publishing.");
            }
            
            // Generate unique movement order number
            $datePrefix = 'MO-' . date('Y') . '-' . date('md') . '-';
            $lastOrder = MovementOrder::where('order_number', 'LIKE', $datePrefix . '%')
                ->orderBy('order_number', 'desc')
                ->first();
            
            $newNumber = $lastOrder ? ((int)substr($lastOrder->order_number, -3)) + 1 : 1;
            $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness
            $counter = 0;
            while (MovementOrder::where('order_number', $orderNumber)->exists() && $counter < 100) {
                $newNumber++;
                $orderNumber = $datePrefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                $counter++;
            }
            
            $movementOrder = MovementOrder::create([
                'order_number' => $orderNumber,
                'manning_request_id' => null, // Command Duration deployments are not from manning requests
                'criteria_months_at_station' => null,
                'status' => 'PUBLISHED', // Mark as published since we're publishing immediately
                'created_by' => auth()->id(),
            ]);
            
            // STEP 1: Notify FROM commands about pending release letters
            // This notifies Staff Officers in the old command that they need to print release letters
            $notificationService = app(\App\Services\NotificationService::class);
            foreach ($assignmentsToPublish as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;
                
                if ($fromCommand) {
                    // Notify FROM command Staff Officers about pending release letter
                    try {
                        $notificationService->notifyCommandOfficerRelease($officer, $fromCommand, $toCommand, $movementOrder);
                    } catch (\Exception $e) {
                        Log::warning("Failed to send release letter notification: " . $e->getMessage());
                    }
                }
            }
            
            // STEP 2: Create posting records (pending - awaiting release letter and acceptance)
            // DO NOT notify officers yet - they will be notified when release letter is printed
            foreach ($assignmentsToPublish as $assignment) {
                $officer = $assignment->officer;
                $fromCommand = $assignment->fromCommand;
                $toCommand = $assignment->toCommand;
                
                if (!$toCommand) {
                    continue; // Skip if no destination
                }
                
                // Create new posting record (pending - awaiting release letter and acceptance)
                OfficerPosting::create([
                    'officer_id' => $officer->id,
                    'command_id' => $toCommand->id,
                    'movement_order_id' => $movementOrder->id,
                    'posting_date' => now(),
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
                
                // DO NOT notify officer yet - notification happens when release letter is printed
            }
            
            // Update deployment status
            $deployment->update([
                'status' => 'PUBLISHED',
                'published_by' => auth()->id(),
                'published_at' => now(),
            ]);
            
            DB::commit();
            
            $successMessage = "Movement Order {$orderNumber} created successfully! Published {$assignmentsToPublish->count()} officer(s) from Command Duration deployment.";
            
            // Redirect to published deployments page
            return redirect()->route('hrd.manning-deployments.published')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish Command Duration deployment: ' . $e->getMessage(), [
                'exception' => $e,
                'deployment_id' => $id,
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to publish deployment: ' . $e->getMessage());
        }
    }

    /**
     * Swap officer in Command Duration draft
     */
    public function swapOfficer($deploymentId, $assignmentId, Request $request)
    {
        $request->validate([
            'new_officer_id' => 'required|exists:officers,id',
        ]);

        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);
            
            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only swap officers in draft deployments.');
            }
            
            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->whereNull('manning_request_id') // Only allow swaps for Command Duration assignments
                ->firstOrFail();
            
            $newOfficer = Officer::findOrFail($request->new_officer_id);
            
            // Check if new officer is already in this deployment
            $existing = ManningDeploymentAssignment::where('manning_deployment_id', $deployment->id)
                ->where('officer_id', $newOfficer->id)
                ->where('id', '!=', $assignmentId)
                ->first();
            
            if ($existing) {
                return redirect()->back()
                    ->with('error', 'New officer is already in this deployment.');
            }
            
            $assignment->update([
                'officer_id' => $newOfficer->id,
                'from_command_id' => $newOfficer->presentStation?->id,
                'rank' => $newOfficer->substantive_rank,
            ]);
            
            return redirect()->back()
                ->with('success', 'Officer swapped successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to swap officer in Command Duration draft: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to swap officer: ' . $e->getMessage());
        }
    }

    /**
     * Remove officer from Command Duration draft
     */
    public function removeOfficer($deploymentId, $assignmentId)
    {
        try {
            $deployment = ManningDeployment::findOrFail($deploymentId);
            
            if ($deployment->status !== 'DRAFT') {
                return redirect()->back()
                    ->with('error', 'Can only remove officers from draft deployments.');
            }
            
            $assignment = ManningDeploymentAssignment::where('id', $assignmentId)
                ->where('manning_deployment_id', $deployment->id)
                ->whereNull('manning_request_id') // Only allow removal of Command Duration assignments
                ->firstOrFail();
            
            $assignment->delete();
            
            return redirect()->back()
                ->with('success', 'Officer removed from deployment successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to remove officer from Command Duration draft: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to remove officer: ' . $e->getMessage());
        }
    }

    /**
     * Check if officer is already in a draft deployment
     */
    private function isInDraft($officerId): bool
    {
        return ManningDeploymentAssignment::where('officer_id', $officerId)
            ->whereHas('deployment', function($q) {
                $q->where('status', 'DRAFT');
            })
            ->exists();
    }

    /**
     * Sort ranks from lowest to highest
     * Rank hierarchy (lowest to highest):
     * CA III → CA II → CA I → AIC → IC → ASC II → ASC I → DSC → SC → CSC → AC → DC → CC → ACG → DCG → CGC
     */
    private function sortRanks(array $ranks): array
    {
        // Define rank hierarchy (lower number = lower rank)
        $rankOrder = [
            // CA III (lowest)
            'CA III' => 1,
            'Customs Assistant Grade III' => 1,
            'Customs Assistant III' => 1,
            // CA II
            'CA II' => 2,
            'Customs Assistant Grade II' => 2,
            'Customs Assistant II' => 2,
            // CA I
            'CA I' => 3,
            'Customs Assistant Grade I' => 3,
            'Customs Assistant I' => 3,
            'CA' => 3,
            'Customs Assistant' => 3,
            // AIC
            'AIC' => 4,
            'Assistant Inspector of Customs' => 4,
            // IC
            'IC' => 5,
            'Inspector' => 5,
            'Inspector of Customs' => 5,
            // ASC II
            'ASC II' => 6,
            'Assistant Superintendent Grade II' => 6,
            'Assistant Superintendent of Customs Grade II' => 6,
            // ASC I
            'ASC I' => 7,
            'ASC' => 7,
            'Assistant Superintendent Grade I' => 7,
            'Assistant Superintendent' => 7,
            'Assistant Superintendent of Customs Grade I' => 7,
            // DSC
            'DSC' => 8,
            'Deputy Superintendent' => 8,
            'Deputy Superintendent of Customs' => 8,
            // SC
            'SC' => 9,
            'Superintendent' => 9,
            'Superintendent of Customs' => 9,
            // CSC
            'CSC' => 10,
            'Chief Superintendent' => 10,
            'Chief Superintendent of Customs' => 10,
            // AC
            'AC' => 11,
            'Assistant Comptroller' => 11,
            'Assistant Comptroller of Customs' => 11,
            // DC
            'DC' => 12,
            'Deputy Comptroller' => 12,
            'Deputy Comptroller of Customs' => 12,
            // CC
            'CC' => 13,
            'Comptroller' => 13,
            'Comptroller of Customs' => 13,
            // ACG
            'ACG' => 14,
            'Assistant Comptroller General' => 14,
            'Assistant Comptroller General of Customs' => 14,
            // DCG
            'DCG' => 15,
            'Deputy Comptroller General' => 15,
            'Deputy Comptroller General of Customs' => 15,
            // CGC (highest)
            'CGC' => 16,
            'Comptroller General' => 16,
            'Comptroller General of Customs' => 16,
        ];

        usort($ranks, function($a, $b) use ($rankOrder) {
            $aOrder = $rankOrder[$a] ?? 999; // Unknown ranks go to end
            $bOrder = $rankOrder[$b] ?? 999;
            
            if ($aOrder === $bOrder) {
                // If same order, sort alphabetically
                return strcmp($a, $b);
            }
            
            return $aOrder <=> $bOrder;
        });

        return $ranks;
    }
}

