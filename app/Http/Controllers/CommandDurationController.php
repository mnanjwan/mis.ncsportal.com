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
use App\Services\ZonalPostingValidationService;
use Carbon\Carbon;

class CommandDurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow HRD, Zone Coordinator, and Super Admin
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->hasRole('HRD') && !$user->hasRole('Zone Coordinator') && !$user->hasRole('Super Admin')) {
                abort(403, 'Access denied. You must be HRD, Zone Coordinator, or Super Admin.');
            }
            return $next($request);
        });
    }

    /**
     * Display the command duration search page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $validationService = app(ZonalPostingValidationService::class);
        
        $zones = collect();
        $commands = collect();
        $selectedZoneId = null;
        $zoneReadOnly = false;
        
        // For Zone Coordinators, pre-fill zone and make it read-only
        if ($isZoneCoordinator && !$isHRD) {
            $zone = $validationService->getZoneCoordinatorZone($user);
            if ($zone) {
                $selectedZoneId = $zone->id;
                $zones = collect([$zone]);
                $zoneReadOnly = true;
                
                // Load all commands in the Zone Coordinator's zone
                $commands = Command::where('zone_id', $zone->id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
            }
        } else {
            // HRD can select any zone
            $zones = Zone::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $selectedZoneId = $request->filled('zone_id') ? $request->zone_id : null;
            if ($selectedZoneId) {
                $commands = Command::where('zone_id', $selectedZoneId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            }
        }
        
        // If AJAX request, return JSON with commands
        if ($request->wantsJson() || $request->ajax()) {
            // For AJAX requests, ensure commands are loaded based on zone_id parameter or pre-selected zone
            if (!$isZoneCoordinator || $isHRD) {
                // HRD: Load commands based on zone_id parameter
                $ajaxZoneId = $request->filled('zone_id') ? $request->zone_id : null;
                if ($ajaxZoneId) {
                    $commands = Command::where('zone_id', $ajaxZoneId)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
                }
            } else {
                // Zone Coordinator: Always use their zone, ignore zone_id parameter
                // Commands should already be loaded above, but reload to ensure they're fresh
                if ($selectedZoneId) {
                    $commands = Command::where('zone_id', $selectedZoneId)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
                }
            }
            
            return response()->json([
                'commands' => $commands->map(function($cmd) {
                    return ['id' => $cmd->id, 'name' => $cmd->name];
                })
            ]);
        }

        // Get unique ranks for filter and sort from lowest to highest
        // For Zone Coordinators, only show ranks for GL 07 and below
        $ranksQuery = Officer::whereNotNull('substantive_rank');
        
        if ($isZoneCoordinator && !$isHRD) {
            $ranksQuery->where(function($q) {
                $q->where('salary_grade_level', 'GL05')
                  ->orWhere('salary_grade_level', 'GL06')
                  ->orWhere('salary_grade_level', 'GL07')
                  ->orWhere('salary_grade_level', '05')
                  ->orWhere('salary_grade_level', '06')
                  ->orWhere('salary_grade_level', '07')
                  ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7")
                  ->orWhereRaw("CAST(salary_grade_level AS UNSIGNED) <= 7");
            });
        }
        
        $ranks = $ranksQuery->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->values()
            ->toArray();
        
        // Sort ranks from lowest to highest
        $ranks = $this->sortRanks($ranks);
        
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';

        return view('dashboards.hrd.command-duration.index', compact(
            'zones',
            'commands',
            'ranks',
            'zoneReadOnly',
            'routePrefix'
        ))->with([
            'selected_zone_id' => $selectedZoneId,
        ]);
    }

    /**
     * Search officers based on filters
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';
        
        // If GET request, redirect to index with parameters
        if ($request->isMethod('get')) {
            return redirect()->route($routePrefix . '.command-duration.index', $request->only(['zone_id', 'command_id', 'rank', 'sex', 'duration_years']));
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

        $validationService = app(ZonalPostingValidationService::class);
        
        // Build query
        $query = Officer::with(['presentStation.zone', 'currentPosting'])
            ->where('present_station', $commandId)
            ->whereHas('currentPosting', function($q) use ($commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
            });
        
        // Filter for Zone Coordinators
        if ($isZoneCoordinator && !$isHRD) {
            // Verify command is in zone
            if (!$validationService->isCommandInZone($commandId, $user)) {
                return back()->withErrors(['command_id' => 'Selected command is not in your zone.']);
            }
            // GL 07 filtering will be done after query execution
        }

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

        // For Zone Coordinators, filter out officers above GL 07
        if ($isZoneCoordinator && !$isHRD) {
            $officers = $officers->filter(function($officer) use ($validationService) {
                return $validationService->isOfficerGL07OrBelow($officer->id);
            })->values();
        }

        // Calculate duration and status for each officer
        $officers = $officers->map(function($officer) use ($isZoneCoordinator, $isHRD, $validationService) {
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
            
            // For Zone Coordinators, add validation status
            if ($isZoneCoordinator && !$isHRD) {
                $officer->meets_command_duration = $validationService->checkCommandDuration($officer->id);
                $officer->is_gl07_or_below = true; // Already filtered above
                $officer->command_duration_message = $validationService->getCommandDurationMessage($officer->id);
            }

            return $officer;
        });

        // Get filter data for view
        // For Zone Coordinators, only show their zone
        if ($isZoneCoordinator && !$isHRD) {
            $zone = $validationService->getZoneCoordinatorZone($user);
            if ($zone) {
                $zones = collect([$zone]);
            } else {
                $zones = collect();
            }
        } else {
        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        }
        
        $commands = Command::where('zone_id', $zoneId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get unique ranks for filter and sort from lowest to highest
        // For Zone Coordinators, only show ranks for GL 07 and below
        $ranksQuery = Officer::whereNotNull('substantive_rank');
        
        if ($isZoneCoordinator && !$isHRD) {
            $ranksQuery->where(function($q) {
                $q->where('salary_grade_level', 'GL05')
                  ->orWhere('salary_grade_level', 'GL06')
                  ->orWhere('salary_grade_level', 'GL07')
                  ->orWhere('salary_grade_level', '05')
                  ->orWhere('salary_grade_level', '06')
                  ->orWhere('salary_grade_level', '07')
                  ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7")
                  ->orWhereRaw("CAST(salary_grade_level AS UNSIGNED) <= 7");
            });
        }
        
        $ranks = $ranksQuery->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->values()
            ->toArray();
        
        // Sort ranks from lowest to highest
        $ranks = $this->sortRanks($ranks);
        
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';
        $zoneReadOnly = $isZoneCoordinator && !$isHRD;

        return view('dashboards.hrd.command-duration.index', compact(
            'officers',
            'zones',
            'commands',
            'ranks',
            'routePrefix',
            'zoneReadOnly'
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
        Log::info('Command Duration - Add to Draft: Starting', [
            'user_id' => auth()->id(),
            'request_data' => $request->except(['officer_ids']), // Don't log full officer_ids array
            'has_officer_ids' => $request->has('officer_ids'),
        ]);

        $request->validate([
            'officer_ids' => 'required|string', // JSON string
            'command_id' => 'required|exists:commands,id',
        ]);

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $routePrefix = $isZoneCoordinator && !$isHRD ? 'zone-coordinator' : 'hrd';
        $validationService = app(ZonalPostingValidationService::class);

        Log::info('Command Duration - Add to Draft: User roles detected', [
            'user_id' => $user->id,
            'isZoneCoordinator' => $isZoneCoordinator,
            'isHRD' => $isHRD,
            'routePrefix' => $routePrefix,
            'command_id' => $request->command_id,
        ]);

        try {
            DB::beginTransaction();

            // Decode JSON string to array
            $officerIds = json_decode($request->officer_ids, true);
            Log::info('Command Duration - Add to Draft: Decoded officer IDs', [
                'officer_ids_count' => is_array($officerIds) ? count($officerIds) : 0,
                'officer_ids' => $officerIds,
            ]);

            if (!is_array($officerIds) || empty($officerIds)) {
                Log::warning('Command Duration - Add to Draft: No officers selected', [
                    'routePrefix' => $routePrefix,
                ]);
                $command = Command::find($request->command_id);
                return redirect()->route($routePrefix . '.command-duration.index', [
                    'zone_id' => $command?->zone_id,
                    'command_id' => $request->command_id,
                ])->withErrors(['officers' => 'No officers selected.']);
            }

            // Validate officer IDs exist
            $validOfficerIds = Officer::whereIn('id', $officerIds)->pluck('id')->toArray();
            if (count($validOfficerIds) !== count($officerIds)) {
                Log::warning('Command Duration - Add to Draft: Invalid officer IDs', [
                    'requested_count' => count($officerIds),
                    'valid_count' => count($validOfficerIds),
                    'routePrefix' => $routePrefix,
                ]);
                $command = Command::find($request->command_id);
                return redirect()->route($routePrefix . '.command-duration.index', [
                    'zone_id' => $command?->zone_id,
                    'command_id' => $request->command_id,
                ])->withErrors(['officers' => 'Some selected officers are invalid.']);
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

            Log::info('Command Duration - Add to Draft: Validating officers', [
                'total_officers' => $officers->count(),
                'isZoneCoordinator' => $isZoneCoordinator && !$isHRD,
            ]);

            foreach ($officers as $officer) {
                if (!$this->isEligibleForMovement($officer)) {
                    $ineligible[] = $officer->full_name;
                    Log::info('Command Duration - Add to Draft: Officer ineligible for movement', [
                        'officer_id' => $officer->id,
                        'officer_name' => $officer->full_name,
                        'reason' => 'Not eligible for movement',
                    ]);
                    continue;
                }
                
                // Additional validations for Zone Coordinators
                if ($isZoneCoordinator && !$isHRD) {
                    // Check rank ceiling - only officers GL 07 and below are allowed
                    $isGL07OrBelow = $validationService->isOfficerGL07OrBelow($officer->id);
                    if (!$isGL07OrBelow) {
                        $ineligible[] = $officer->full_name . ' (above GL 07)';
                        Log::info('Command Duration - Add to Draft: Officer above GL 07', [
                            'officer_id' => $officer->id,
                            'officer_name' => $officer->full_name,
                            'rank' => $officer->substantive_rank,
                            'grade_level' => $officer->salary_grade_level,
                        ]);
                        continue;
                    }
                    // Note: Command duration check removed - only GL 07 ceiling applies
                }
                
                $eligible[] = $officer;
            }

            Log::info('Command Duration - Add to Draft: Officer validation complete', [
                'eligible_count' => count($eligible),
                'ineligible_count' => count($ineligible),
                'ineligible_names' => $ineligible,
            ]);

            if (!empty($ineligible)) {
                DB::rollBack();
                Log::warning('Command Duration - Add to Draft: Rolling back due to ineligible officers', [
                    'ineligible' => $ineligible,
                    'routePrefix' => $routePrefix,
                ]);
                
                // Get zone_id from command to redirect back with proper parameters
                $command = Command::find($request->command_id);
                $redirectParams = [
                    'command_id' => $request->command_id,
                ];
                
                if ($command) {
                    $redirectParams['zone_id'] = $command->zone_id;
                }
                
                // Build detailed error message
                $errorMessage = 'Some officers are not eligible for movement: ' . implode(', ', $ineligible);
                
                Log::info('Command Duration - Add to Draft: Redirecting with errors', [
                    'routePrefix' => $routePrefix,
                    'redirectParams' => $redirectParams,
                    'errorMessage' => $errorMessage,
                ]);
                
                // Redirect directly to index (not search) to preserve errors
                return redirect()->route($routePrefix . '.command-duration.index', $redirectParams)
                    ->withErrors([
                        'officers' => $errorMessage
                ]);
            }

            // Get or create draft deployment
            // For Zone Coordinators, find/create drafts that have assignments in their zone
            // For HRD, find/create any draft
            $deployment = null;
            
            if ($isZoneCoordinator && !$isHRD) {
                // Zone Coordinator: Find draft with assignments in their zone ONLY
                // Don't reuse HRD drafts - create new ones for Zone Coordinators
                $zoneCommandIds = $validationService->getZoneCommandIds($user);
                
                if (!empty($zoneCommandIds)) {
                    // Find existing draft that has assignments in their zone
            $deployment = ManningDeployment::draft()
                        ->whereHas('assignments', function($q) use ($zoneCommandIds) {
                            $q->where(function($subQ) use ($zoneCommandIds) {
                                $subQ->whereIn('to_command_id', $zoneCommandIds)
                                     ->orWhereIn('from_command_id', $zoneCommandIds);
                            });
                        })
                ->latest()
                ->first();
                    
                    Log::info('Command Duration - Add to Draft: Zone Coordinator draft lookup', [
                        'zone_command_ids' => $zoneCommandIds,
                        'found_deployment' => $deployment ? $deployment->id : null,
                    ]);
                }
                // For Zone Coordinators, don't fall back to general drafts - create new one
            } else {
                // HRD: Get any draft deployment
                $deployment = ManningDeployment::draft()
                    ->latest()
                    ->first();
            }

            if (!$deployment) {
                Log::info('Command Duration - Add to Draft: Creating new draft deployment', [
                    'isZoneCoordinator' => $isZoneCoordinator && !$isHRD,
                    'routePrefix' => $routePrefix,
                ]);
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
                Log::info('Command Duration - Add to Draft: Created new draft deployment', [
                    'deployment_id' => $deployment->id,
                    'deployment_number' => $deploymentNumber,
                    'created_by' => auth()->id(),
                    'routePrefix' => $routePrefix,
                ]);
            } else {
                Log::info('Command Duration - Add to Draft: Using existing draft deployment', [
                    'deployment_id' => $deployment->id,
                    'deployment_number' => $deployment->deployment_number,
                    'created_by' => $deployment->created_by,
                    'routePrefix' => $routePrefix,
                ]);
            }

            // Add officers to draft
            $added = 0;
            $skipped = [];
            $skippedNames = [];
            foreach ($eligible as $officer) {
                $fromCommand = $officer->presentStation;

                // Check if officer is in a PUBLISHED deployment (they're actually posted)
                $inPublishedDeployment = ManningDeploymentAssignment::where('officer_id', $officer->id)
                    ->whereHas('deployment', function($q) {
                        $q->where('status', 'PUBLISHED');
                    })
                    ->exists();

                if ($inPublishedDeployment) {
                    $skipped[] = $officer->full_name . ' (already posted)';
                    $skippedNames[] = $officer->full_name;
                    Log::info('Command Duration - Add to Draft: Officer in published deployment (already posted)', [
                        'officer_id' => $officer->id,
                        'officer_name' => $officer->full_name,
                    ]);
                    continue;
                }

                // Check if officer is already in ANY draft deployment (skip them)
                $inDraftDeployment = ManningDeploymentAssignment::where('officer_id', $officer->id)
                    ->whereHas('deployment', function($q) {
                        $q->where('status', 'DRAFT');
                    })
                    ->first();

                if ($inDraftDeployment) {
                    $skipped[] = $officer->full_name . ' (already in draft)';
                    $skippedNames[] = $officer->full_name;
                    Log::info('Command Duration - Add to Draft: Officer already in draft deployment (skipping)', [
                        'officer_id' => $officer->id,
                        'officer_name' => $officer->full_name,
                        'existing_deployment_id' => $inDraftDeployment->manning_deployment_id,
                        'existing_assignment_id' => $inDraftDeployment->id,
                    ]);
                    continue;
                }

                // Now add the officer to draft
                // Use from_command_id as temporary to_command_id (database requires NOT NULL)
                // HRD will change this in the draft view to the actual destination
                $tempToCommandId = $fromCommand?->id;
                
                // If no from_command, we need a valid command - use the search command as fallback
                if (!$tempToCommandId) {
                    $searchCommand = Command::find($request->command_id);
                    $tempToCommandId = $searchCommand?->id;
                }
                
                if (!$tempToCommandId) {
                    $skipped[] = $officer->full_name . ' (cannot determine command)';
                    $skippedNames[] = $officer->full_name;
                    Log::warning('Command Duration - Add to Draft: Skipping officer - cannot determine command', [
                        'officer_id' => $officer->id,
                        'officer_name' => $officer->full_name,
                    ]);
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
                Log::info('Command Duration - Add to Draft: Added officer to draft', [
                    'officer_id' => $officer->id,
                    'officer_name' => $officer->full_name,
                    'deployment_id' => $deployment->id,
                    'from_command_id' => $fromCommand?->id,
                    'to_command_id' => $tempToCommandId,
                ]);
            }

            DB::commit();

            Log::info('Command Duration - Add to Draft: Transaction committed', [
                'added' => $added,
                'skipped_count' => count($skipped),
                'skipped' => $skipped,
                'routePrefix' => $routePrefix,
                'deployment_id' => $deployment->id,
            ]);

            // Build success message with details
            $message = '';
            if ($added > 0) {
                $message = "{$added} officer(s) added to draft deployment.";
            }
            
            if (!empty($skipped)) {
                if ($message) {
                    $message .= ' ';
                }
                $message .= count($skipped) . ' officer(s) skipped: ' . implode(', ', $skipped);
            }

            // Get zone_id from command to redirect back with proper parameters
            $command = Command::find($request->command_id);
            $redirectParams = [
                'command_id' => $request->command_id,
            ];
            
            if ($command) {
                $redirectParams['zone_id'] = $command->zone_id;
            }

            if ($added > 0) {
                $redirectRoute = $routePrefix . '.manning-deployments.draft';
                Log::info('Command Duration - Add to Draft: Redirecting to draft page', [
                    'route' => $redirectRoute,
                    'routePrefix' => $routePrefix,
                    'message' => $message,
                    'added' => $added,
                    'skipped_count' => count($skipped),
                ]);
                
                return redirect()->route($redirectRoute)
                    ->with('success', $message);
            } else {
                Log::info('Command Duration - Add to Draft: No officers added, redirecting back to search', [
                    'routePrefix' => $routePrefix,
                    'skipped_count' => count($skipped),
                    'skipped' => $skipped,
                ]);
                
                // If all officers were skipped, show info message with details
                $infoMessage = !empty($skipped) 
                    ? 'No new officers added. ' . implode(', ', $skipped) . '.'
                    : 'No new officers added. All selected officers are already in the draft.';
                
                return redirect()->route($routePrefix . '.command-duration.index', $redirectParams)
                    ->with('info', $infoMessage);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Command Duration - Add to Draft: Exception occurred', [
                'exception' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'routePrefix' => $routePrefix ?? 'unknown',
                'command_id' => $request->command_id ?? null,
                'officer_ids' => $officerIds ?? [],
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Get zone_id from command to redirect back with proper parameters
            $command = Command::find($request->command_id);
            $redirectParams = [
                'command_id' => $request->command_id,
            ];
            
            if ($command) {
                $redirectParams['zone_id'] = $command->zone_id;
            }

            Log::info('Command Duration - Add to Draft: Exception - Redirecting back to search', [
                'routePrefix' => $routePrefix ?? 'unknown',
                'redirectParams' => $redirectParams,
            ]);

            return redirect()->route($routePrefix . '.command-duration.index', $redirectParams)
                ->withErrors(['error' => 'Failed to add officers to draft. Please try again.']);
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

        $user = auth()->user();
        $isZoneCoordinator = $user->hasRole('Zone Coordinator');
        $isHRD = $user->hasRole('HRD');
        $validationService = app(ZonalPostingValidationService::class);

        // Build same query as search method
        $query = Officer::with(['presentStation.zone', 'currentPosting'])
            ->where('present_station', $commandId)
            ->whereHas('currentPosting', function($q) use ($commandId) {
                $q->where('command_id', $commandId)
                  ->where('is_current', true);
            });

        // Filter for Zone Coordinators - verify command is in their zone
        if ($isZoneCoordinator && !$isHRD) {
            // Verify command is in zone
            if (!$validationService->isCommandInZone($commandId, $user)) {
                abort(403, 'Selected command is not in your zone.');
            }
        }

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

        // For Zone Coordinators, filter out officers above GL 07 (same as search method)
        if ($isZoneCoordinator && !$isHRD) {
            $officers = $officers->filter(function($officer) use ($validationService) {
                return $validationService->isOfficerGL07OrBelow($officer->id);
            })->values();
        }

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

