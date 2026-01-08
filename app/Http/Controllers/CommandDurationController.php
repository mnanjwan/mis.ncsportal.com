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
                return redirect()->route('hrd.manning-deployments.draft')
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

