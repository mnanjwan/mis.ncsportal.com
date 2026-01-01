<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class DutyRosterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Allow Staff Officer, Area Controller, and DC Admin access
        $this->middleware('role:Staff Officer|Area Controller|DC Admin');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? \App\Models\Command::find($commandId) : null;

        // Get month from request or use current month
        $month = $request->get('month', date('Y-m'));

        // Get duty rosters for this command and month
        $rosters = \App\Models\DutyRoster::where('command_id', $commandId)
            ->whereYear('roster_period_start', date('Y', strtotime($month . '-01')))
            ->whereMonth('roster_period_start', date('m', strtotime($month . '-01')))
            ->with(['assignments.officer', 'oicOfficer', 'secondInCommandOfficer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.staff-officer.roster', compact('rosters', 'command', 'month'));
    }

    public function create()
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? \App\Models\Command::find($commandId) : null;

        // Get predefined units
        $predefinedUnits = [
            'Revenue',
            'Admin',
            'Enforcement',
            'ICT',
            'Accounts',
            'Transport and Logistics',
            'Medical',
            'Escort',
            'Guard Duty'
        ];

        // Get custom units from database (units that are not in predefined list)
        $customUnits = \App\Models\DutyRoster::whereNotNull('unit')
            ->whereNotIn('unit', $predefinedUnits)
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->toArray();

        // Merge all units and sort alphabetically
        $allUnits = array_merge($predefinedUnits, $customUnits);
        sort($allUnits);

        // Get officers for OIC/2IC selection with assignment information
        $officers = [];
        if ($commandId) {
            $officers = \App\Models\Officer::where('present_station', $commandId)
                ->where('is_active', true)
                ->orderBy('surname')
                ->get()
                ->map(function ($officer) {
                    $assignedRoster = $this->getOfficerAssignedRoster($officer->id);
                    return [
                        'officer' => $officer,
                        'is_assigned' => $assignedRoster !== null,
                        'assigned_roster' => $assignedRoster,
                    ];
                });
        }

        return view('forms.roster.create', compact('command', 'allUnits', 'officers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        if (!$commandId) {
            return redirect()->back()->with('error', 'You are not assigned to a command. Please contact HRD.')->withInput();
        }

        $request->validate([
            'unit' => 'required|string|max:255',
            'roster_period_start' => 'required|date',
            'roster_period_end' => 'required|date|after:roster_period_start',
            'command_id' => 'required|exists:commands,id',
            'oic_officer_id' => 'nullable|exists:officers,id',
            'second_in_command_officer_id' => 'nullable|exists:officers,id|different:oic_officer_id',
        ], [
            'second_in_command_officer_id.different' => 'The Second In Command (2IC) cannot be the same as the Officer in Charge (OIC).',
        ]);

        // Verify command matches Staff Officer's command
        if ($request->command_id != $commandId) {
            return redirect()->back()->with('error', 'You can only create rosters for your assigned command.')->withInput();
        }

        // Handle custom unit (if unit is __NEW__, use unit_custom)
        $unit = $request->unit;
        if ($unit === '__NEW__' && $request->has('unit_custom')) {
            $unit = trim($request->unit_custom);
            if (empty($unit)) {
                return redirect()->back()->with('error', 'Please enter a unit name.')->withInput();
            }
        }

        // Trim and validate unit
        $unit = trim($unit);
        if (empty($unit)) {
            return redirect()->back()->with('error', 'Unit is required.')->withInput();
        }

        try {
            $roster = \App\Models\DutyRoster::create([
                'command_id' => $commandId,
                'unit' => $unit,
                'roster_period_start' => $request->roster_period_start,
                'roster_period_end' => $request->roster_period_end,
                'prepared_by' => $user->id,
                'status' => 'DRAFT',
                'oic_officer_id' => $request->oic_officer_id,
                'second_in_command_officer_id' => $request->second_in_command_officer_id,
            ]);

            return redirect()->route('staff-officer.roster.show', $roster->id)
                ->with('success', 'Duty roster created successfully! You can now add officer assignments.');

        } catch (\Exception $e) {
            Log::error('Failed to create duty roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create duty roster: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'assignments.officer', 'preparedBy', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);

        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only view rosters for your assigned command');
        }

        return view('dashboards.staff-officer.roster-show', compact('roster'));
    }

    public function edit($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'assignments.officer'])->findOrFail($id);

        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only edit rosters for your assigned command');
        }

        // Get all commands for Command selection
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all officers in the command (for initial load if command is pre-selected)
        $allOfficers = \App\Models\Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->get();

        // Exclude OIC and 2IC from assignments list
        $excludedIds = [];
        if ($roster->oic_officer_id) {
            $excludedIds[] = $roster->oic_officer_id;
        }
        if ($roster->second_in_command_officer_id) {
            $excludedIds[] = $roster->second_in_command_officer_id;
        }

        $officersForAssignments = $allOfficers->reject(function ($officer) use ($excludedIds) {
            return in_array($officer->id, $excludedIds);
        });

        // For OIC/2IC dropdowns, use all officers (will be loaded dynamically based on selected command)
        $officers = $allOfficers;

        // Get predefined units
        $predefinedUnits = [
            'Revenue',
            'Admin',
            'Enforcement',
            'ICT',
            'Accounts',
            'Transport and Logistics',
            'Medical',
            'Escort',
            'Guard Duty'
        ];

        // Get custom units from database (units that are not in predefined list)
        $customUnits = \App\Models\DutyRoster::whereNotNull('unit')
            ->whereNotIn('unit', $predefinedUnits)
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit')
            ->toArray();

        // Merge all units and sort alphabetically
        $allUnits = array_merge($predefinedUnits, $customUnits);
        sort($allUnits);

        return view('forms.roster.edit', compact('roster', 'officers', 'officersForAssignments', 'allOfficers', 'allUnits', 'commands', 'commandId'));
    }

    public function getOfficersByCommand(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'command_id' => 'required',
                'roster_id' => 'nullable|exists:duty_rosters,id'
            ]);

            $commandId = (int) $validated['command_id'];
            $currentRosterId = isset($validated['roster_id']) ? (int) $validated['roster_id'] : null;

            if ($commandId <= 0) {
                return response()->json([
                    'error' => 'Invalid command ID',
                    'message' => 'Command ID must be a positive integer'
                ], 422);
            }

            // Verify command exists
            $commandExists = \App\Models\Command::where('id', $commandId)->exists();
            if (!$commandExists) {
                return response()->json([
                    'error' => 'Command not found',
                    'message' => "Command with ID {$commandId} does not exist"
                ], 404);
            }

            // Query officers
            $officersQuery = \App\Models\Officer::where('present_station', $commandId)
                ->where('is_active', true)
                ->orderBy('surname')
                ->orderBy('initials');

            $officers = $officersQuery->get()->map(function ($officer) use ($currentRosterId) {
                if (!$officer) {
                    return null;
                }

                // Check if officer is assigned to an active/approved roster
                $assignedRoster = $this->getOfficerAssignedRoster($officer->id, $currentRosterId);

                return [
                    'id' => $officer->id ?? null,
                    'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')),
                    'service_number' => $officer->service_number ?? 'N/A',
                    'rank' => $officer->substantive_rank ?? 'N/A',
                    'is_assigned' => $assignedRoster !== null,
                    'assigned_roster' => $assignedRoster,
                ];
            })->filter()->values();

            return response()->json($officers);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in getOfficersByCommand', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error fetching officers by command', [
                'command_id' => $request->command_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching officers. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if an officer is assigned to an active/approved roster
     * Returns roster information if assigned, null otherwise
     */
    private function getOfficerAssignedRoster($officerId, $excludeRosterId = null)
    {
        // Check for active rosters (APPROVED or SUBMITTED status)
        $activeRoster = \App\Models\DutyRoster::whereIn('status', ['APPROVED', 'SUBMITTED'])
            ->where(function ($query) use ($officerId) {
                // Check if officer is OIC
                $query->where('oic_officer_id', $officerId)
                    // Check if officer is 2IC
                    ->orWhere('second_in_command_officer_id', $officerId)
                    // Check if officer is in assignments
                    ->orWhereHas('assignments', function ($q) use ($officerId) {
                        $q->where('officer_id', $officerId);
                    });
            })
            ->when($excludeRosterId, function ($query) use ($excludeRosterId) {
                // Exclude current roster when editing
                $query->where('id', '!=', $excludeRosterId);
            })
            ->with('command')
            ->first();

        if ($activeRoster) {
            $periodStart = $activeRoster->roster_period_start 
                ? \Carbon\Carbon::parse($activeRoster->roster_period_start)->format('d/m/Y') 
                : 'N/A';
            $periodEnd = $activeRoster->roster_period_end 
                ? \Carbon\Carbon::parse($activeRoster->roster_period_end)->format('d/m/Y') 
                : 'N/A';

            return [
                'id' => $activeRoster->id,
                'unit' => $activeRoster->unit ?? 'N/A',
                'command' => $activeRoster->command->name ?? 'N/A',
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => $activeRoster->status,
                'display_name' => ($activeRoster->unit ?? 'Roster') . ' (' . $periodStart . ' - ' . $periodEnd . ')',
            ];
        }

        return null;
    }

    public function update(Request $request, $id)
    {
        $roster = \App\Models\DutyRoster::findOrFail($id);

        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only edit rosters for your assigned command');
        }

        // Track if roster was approved (will need to change status back to DRAFT)
        $wasApproved = $roster->status === 'APPROVED';

        // Track old assignments before making changes
        $oldOfficerIds = [];
        if ($roster->oic_officer_id) {
            $oldOfficerIds[] = $roster->oic_officer_id;
        }
        if ($roster->second_in_command_officer_id) {
            $oldOfficerIds[] = $roster->second_in_command_officer_id;
        }
        $oldAssignments = $roster->assignments()->pluck('officer_id')->toArray();
        $oldOfficerIds = array_merge($oldOfficerIds, $oldAssignments);
        $oldOfficerIds = array_unique($oldOfficerIds);

        $validator = \Validator::make($request->all(), [
            'unit' => 'nullable|string|max:255',
            'unit_custom' => 'nullable|string|max:255|required_if:unit,__NEW__',
            'oic_officer_id' => 'nullable|exists:officers,id',
            'second_in_command_officer_id' => 'nullable|exists:officers,id|different:oic_officer_id',
            'assignments' => 'nullable|array',
            'assignments.*.officer_id' => 'required|exists:officers,id',
            'assignments.*.shift' => 'nullable|string|max:50',
        ], [
            'second_in_command_officer_id.different' => 'The Second In Command (2IC) cannot be the same as the Officer in Charge (OIC).',
            'unit_custom.required_if' => 'Please enter a unit name when creating a new unit.',
        ]);

        // Customize assignment validation messages to be more descriptive
        if ($validator->fails()) {
            $errors = $validator->errors();
            $messagesToRemove = [];
            $messagesToAdd = [];

            foreach ($errors->messages() as $key => $messages) {
                // Check if this is an assignment officer_id required error
                if (preg_match('/^assignments\.(\d+)\.officer_id$/', $key, $matches)) {
                    $assignmentIndex = (int) $matches[1];
                    $assignmentNumber = $assignmentIndex + 1; // Convert 0-based to 1-based

                    // Replace the default message with a more descriptive one
                    $messagesToRemove[] = $key;
                    $messagesToAdd[$key] = ["Assignment #{$assignmentNumber}: Please select an officer for this assignment."];
                }
                // Check if this is an assignment shift max length error
                elseif (preg_match('/^assignments\.(\d+)\.shift$/', $key, $matches)) {
                    $assignmentIndex = (int) $matches[1];
                    $assignmentNumber = $assignmentIndex + 1;

                    // Check if it's a max length error
                    foreach ($messages as $message) {
                        if (strpos($message, 'may not be greater than') !== false || strpos($message, 'may not exceed') !== false) {
                            $messagesToRemove[] = $key;
                            $messagesToAdd[$key] = ["Assignment #{$assignmentNumber}: The shift description cannot exceed 50 characters."];
                            break;
                        }
                    }
                }
            }

            // Remove old messages and add new ones
            foreach ($messagesToRemove as $key) {
                $errors->forget($key);
            }
            foreach ($messagesToAdd as $key => $newMessages) {
                foreach ($newMessages as $message) {
                    $errors->add($key, $message);
                }
            }

            // Throw validation exception with customized messages
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // Additional validation: OIC cannot be 2IC
        if (
            $request->oic_officer_id && $request->second_in_command_officer_id &&
            $request->oic_officer_id == $request->second_in_command_officer_id
        ) {
            return redirect()->back()
                ->with('error', 'The Officer in Charge (OIC) cannot be the same as the Second In Command (2IC).')
                ->withInput();
        }

        // Check for active APER timeline
        $activeTimeline = \App\Models\APERTimeline::where('is_active', true)->first();
        if ($activeTimeline) {
            $year = $activeTimeline->year;
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-12-31";

            // Check all officers being assigned (including OIC and 2IC)
            $officersToCheck = [];
            if ($request->has('assignments')) {
                foreach ($request->assignments as $assignment) {
                    if (!empty($assignment['officer_id'])) {
                        $officersToCheck[] = $assignment['officer_id'];
                    }
                }
            }
            if ($request->oic_officer_id) {
                $officersToCheck[] = $request->oic_officer_id;
            }
            if ($request->second_in_command_officer_id) {
                $officersToCheck[] = $request->second_in_command_officer_id;
            }

            // Remove duplicates
            $officersToCheck = array_unique($officersToCheck);

            // Check each officer if they're already in an approved roster for the active timeline
            foreach ($officersToCheck as $officerId) {
                // Skip if this is the same roster being edited
                $isInCurrentRoster = false;
                if (
                    $roster->oic_officer_id == $officerId ||
                    $roster->second_in_command_officer_id == $officerId ||
                    $roster->assignments()->where('officer_id', $officerId)->exists()
                ) {
                    $isInCurrentRoster = true;
                }

                // Check if officer is in another approved roster for the active timeline
                $existingRoster = \App\Models\DutyRoster::where('command_id', $commandId)
                    ->where('status', 'APPROVED')
                    ->where('id', '!=', $roster->id) // Exclude current roster
                    ->where(function ($query) use ($officerId, $startDate, $endDate) {
                        // Check if officer is OIC or 2IC with period overlap
                        $query->where(function ($q) use ($officerId, $startDate, $endDate) {
                            $q->where('oic_officer_id', $officerId)
                                ->where(function ($periodQuery) use ($startDate, $endDate) {
                                    $periodQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function ($overlapQuery) use ($startDate, $endDate) {
                                            $overlapQuery->where('roster_period_start', '<=', $startDate)
                                                ->where('roster_period_end', '>=', $endDate);
                                        });
                                });
                        })
                            ->orWhere(function ($q) use ($officerId, $startDate, $endDate) {
                            $q->where('second_in_command_officer_id', $officerId)
                                ->where(function ($periodQuery) use ($startDate, $endDate) {
                                    $periodQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function ($overlapQuery) use ($startDate, $endDate) {
                                            $overlapQuery->where('roster_period_start', '<=', $startDate)
                                                ->where('roster_period_end', '>=', $endDate);
                                        });
                                });
                        })
                            // Check if officer is in assignments with period overlap
                            ->orWhere(function ($q) use ($officerId, $startDate, $endDate) {
                            $q->whereHas('assignments', function ($assignmentQuery) use ($officerId) {
                                $assignmentQuery->where('officer_id', $officerId);
                            })
                                ->where(function ($periodQuery) use ($startDate, $endDate) {
                                    $periodQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function ($overlapQuery) use ($startDate, $endDate) {
                                            $overlapQuery->where('roster_period_start', '<=', $startDate)
                                                ->where('roster_period_end', '>=', $endDate);
                                        });
                                });
                        });
                    })
                    ->first();

                if ($existingRoster && !$isInCurrentRoster) {
                    $officer = \App\Models\Officer::find($officerId);
                    $officerName = $officer ? ($officer->initials . ' ' . $officer->surname . ' (' . $officer->service_number . ')') : 'Officer';
                    return redirect()->back()
                        ->with('error', "{$officerName} is already assigned to an approved roster for the active APER timeline ({$year}). Please remove them from that roster first or wait until the timeline is inactive.")
                        ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();

            // Handle custom unit (if unit is __NEW__, use unit_custom) - same logic as create
            $unit = $request->input('unit', '');

            // If unit is __NEW__, get the custom unit value
            if ($unit === '__NEW__') {
                if ($request->has('unit_custom') && !empty(trim($request->input('unit_custom')))) {
                    $unit = trim($request->input('unit_custom'));
                } else {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Please enter a unit name.')->withInput();
                }
            } elseif (!empty($unit) && $unit !== '__NEW__') {
                // Trim existing unit value
                $unit = trim($unit);
            }

            // Update unit, OIC and 2IC
            $updateData = [
                'oic_officer_id' => $request->oic_officer_id,
                'second_in_command_officer_id' => $request->second_in_command_officer_id,
            ];

            // If roster was approved, change status back to DRAFT for re-approval
            if ($wasApproved) {
                $updateData['status'] = 'DRAFT';
            }

            // Always update unit if it's provided and not empty
            // This ensures the unit is updated when user selects a different unit
            if (!empty($unit)) {
                $updateData['unit'] = $unit;
            }
            // Note: If unit is empty, we don't update it (preserve existing value)

            $roster->update($updateData);

            // Get new assigned officer IDs
            $newOfficerIds = [];
            if ($request->has('assignments')) {
                foreach ($request->assignments as $assignment) {
                    if (!in_array($assignment['officer_id'], $newOfficerIds)) {
                        $newOfficerIds[] = $assignment['officer_id'];
                    }
                }
            }

            // Include OIC and 2IC in new officer IDs
            if ($request->oic_officer_id && !in_array($request->oic_officer_id, $newOfficerIds)) {
                $newOfficerIds[] = $request->oic_officer_id;
            }
            if ($request->second_in_command_officer_id && !in_array($request->second_in_command_officer_id, $newOfficerIds)) {
                $newOfficerIds[] = $request->second_in_command_officer_id;
            }

            // Delete existing assignments if provided
            if ($request->has('assignments')) {
                $roster->assignments()->delete();

                // Create new assignments
                foreach ($request->assignments as $assignment) {
                    \App\Models\RosterAssignment::create([
                        'roster_id' => $roster->id,
                        'officer_id' => $assignment['officer_id'],
                        'shift' => $assignment['shift'] ?? null,
                    ]);
                }
            }

            // Refresh roster to get updated relationships
            $roster->refresh();
            $roster->load(['command', 'assignments', 'oicOfficer', 'secondInCommandOfficer']);

            // Determine which officers were added and removed
            $addedOfficerIds = array_diff($newOfficerIds, $oldOfficerIds);
            $removedOfficerIds = array_diff($oldOfficerIds, $newOfficerIds);

            // Notify only added and removed officers (if roster was approved)
            if ($wasApproved && (count($addedOfficerIds) > 0 || count($removedOfficerIds) > 0)) {
                $notificationService = app(NotificationService::class);

                // Notify added officers
                foreach ($addedOfficerIds as $officerId) {
                    $officer = \App\Models\Officer::find($officerId);
                    if ($officer && $officer->user) {
                        // Determine role
                        $role = 'Regular Officer';
                        if ($roster->oic_officer_id == $officerId) {
                            $role = 'Officer in Charge (OIC)';
                        } elseif ($roster->second_in_command_officer_id == $officerId) {
                            $role = 'Second In Command (2IC)';
                        }

                        // Send assignment email via job
                        if ($officer->user->email) {
                            $command = $roster->command;
                            $commandName = $command ? $command->name : 'your command';
                            $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
                            $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';
                            $oicName = $roster->oicOfficer ? "{$roster->oicOfficer->initials} {$roster->oicOfficer->surname}" : null;
                            $secondInCommandName = $roster->secondInCommandOfficer ? "{$roster->secondInCommandOfficer->initials} {$roster->secondInCommandOfficer->surname}" : null;

                            try {
                                \App\Jobs\SendDutyRosterAssignmentMailJob::dispatch(
                                    $roster,
                                    $officer,
                                    $role,
                                    $commandName,
                                    $periodStart,
                                    $periodEnd,
                                    $oicName,
                                    $secondInCommandName
                                );
                            } catch (\Exception $e) {
                                Log::error('Failed to dispatch duty roster assignment email for added officer', [
                                    'officer_id' => $officerId,
                                    'roster_id' => $roster->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }

                // Notify removed officers
                foreach ($removedOfficerIds as $officerId) {
                    $officer = \App\Models\Officer::find($officerId);
                    if ($officer && $officer->user) {
                        $command = $roster->command;
                        $commandName = $command ? $command->name : 'your command';
                        $periodStart = $roster->roster_period_start ? \Carbon\Carbon::parse($roster->roster_period_start)->format('d/m/Y') : 'N/A';
                        $periodEnd = $roster->roster_period_end ? \Carbon\Carbon::parse($roster->roster_period_end)->format('d/m/Y') : 'N/A';

                        $notificationService->notify(
                            $officer->user,
                            'duty_roster_removed',
                            'Duty Roster Assignment Removed',
                            "You have been removed from the duty roster for {$commandName}. Period: {$periodStart} to {$periodEnd}.",
                            'duty_roster',
                            $roster->id
                        );
                    }
                }
            }

            DB::commit();

            $successMessage = 'Roster updated successfully!';
            if ($wasApproved) {
                $successMessage .= ' The roster status has been reset to DRAFT and requires re-approval.';
                if (count($addedOfficerIds) > 0 || count($removedOfficerIds) > 0) {
                    $successMessage .= ' Affected officers have been notified.';
                }
            } else {
                $successMessage .= ' Emails will be sent to assigned officers after approval.';
            }

            return redirect()->route('staff-officer.roster.show', $roster->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update roster: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function submit($id)
    {
        $roster = \App\Models\DutyRoster::findOrFail($id);

        $user = auth()->user();
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $staffOfficerRole?->pivot->command_id ?? null;

        // Verify access
        if (!$commandId || $roster->command_id != $commandId) {
            abort(403, 'You can only submit rosters for your assigned command');
        }

        // Only allow submitting DRAFT rosters
        if ($roster->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT rosters can be submitted.');
        }

        // Check if roster has assignments
        if ($roster->assignments->count() === 0) {
            return redirect()->back()->with('error', 'Please add at least one officer assignment before submitting.');
        }

        try {
            $roster->update([
                'status' => 'SUBMITTED',
            ]);

            // Refresh roster to get relationships
            $roster->refresh();
            $roster->load(['command', 'preparedBy', 'assignments', 'oicOfficer', 'secondInCommandOfficer']);

            // Notify DC Admins and Area Controllers
            $notificationService = app(NotificationService::class);
            $notificationService->notifyDutyRosterSubmitted($roster);
            $notificationService->notifyDutyRosterSubmittedToAreaController($roster);

            return redirect()->route('staff-officer.roster.show', $roster->id)
                ->with('success', 'Roster submitted successfully! It is now pending DC Admin and Area Controller approval.');

        } catch (\Exception $e) {
            Log::error('Failed to submit roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit roster: ' . $e->getMessage());
        }
    }

    // Area Controller Methods
    public function areaControllerIndex(Request $request)
    {
        // Get submitted rosters (status = SUBMITTED)
        $query = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments', 'oicOfficer', 'secondInCommandOfficer'])
            ->where('status', 'SUBMITTED')
            ->orderBy('created_at', 'desc');

        $rosters = $query->paginate(20)->withQueryString();

        return view('dashboards.area-controller.roster', compact('rosters'));
    }

    public function areaControllerShow($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);

        // Only show SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            abort(403, 'This roster is not pending approval');
        }

        return view('dashboards.area-controller.roster-show', compact('roster'));
    }

    public function areaControllerApprove(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can approve rosters');
        }

        $roster = \App\Models\DutyRoster::findOrFail($id);

        // Only allow approving SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be approved.');
        }

        try {
            DB::beginTransaction();

            // Check if this is a re-approval (roster was previously approved)
            $isReapproval = !is_null($roster->approved_at);

            $roster->status = 'APPROVED';
            $roster->approved_at = now();
            $roster->save();

            // Refresh roster to load all relationships
            $roster->refresh();
            $roster->load(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer']);

            // Only notify if this is the first approval (not a re-approval)
            // If it's a re-approval, affected officers were already notified during edit
            if (!$isReapproval) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyDutyRosterApproved($roster, $user);
            }

            DB::commit();

            $successMessage = $isReapproval
                ? 'Roster re-approved successfully. Affected officers were already notified when the roster was edited.'
                : 'Roster approved successfully. All assigned officers and Staff Officer have been notified.';

            return redirect()->route('area-controller.roster')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve roster: ' . $e->getMessage());
        }
    }

    public function areaControllerReject(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is Area Controller
        if (!$user->hasRole('Area Controller')) {
            abort(403, 'Only Area Controller can reject rosters');
        }

        $roster = \App\Models\DutyRoster::findOrFail($id);

        // Only allow rejecting SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $roster->status = 'REJECTED';
            $roster->rejection_reason = $request->rejection_reason;
            $roster->save();

            // Refresh roster to load relationships
            $roster->refresh();
            $roster->load(['command', 'preparedBy']);

            // Notify Staff Officer about rejection
            $notificationService = app(NotificationService::class);
            $notificationService->notifyDutyRosterRejected($roster, $user, $request->rejection_reason);

            DB::commit();

            return redirect()->route('area-controller.roster')
                ->with('success', 'Roster rejected. Staff Officer has been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject roster: ' . $e->getMessage());
        }
    }

    // DC Admin Methods
    public function dcAdminIndex(Request $request)
    {
        $user = auth()->user();

        // Get DC Admin's command
        $dcAdminRole = $user->roles()
            ->where('name', 'DC Admin')
            ->wherePivot('is_active', true)
            ->first();

        $commandId = $dcAdminRole?->pivot->command_id ?? null;

        // Get submitted rosters for DC Admin's command
        $query = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments', 'oicOfficer', 'secondInCommandOfficer'])
            ->where('status', 'SUBMITTED')
            ->orderBy('created_at', 'desc');

        // Filter by command if DC Admin is assigned to a command
        if ($commandId) {
            $query->where('command_id', $commandId);
        }

        $rosters = $query->paginate(20)->withQueryString();

        return view('dashboards.dc-admin.roster', compact('rosters'));
    }

    public function dcAdminShow($id)
    {
        $roster = \App\Models\DutyRoster::with(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer'])->findOrFail($id);

        // Only show SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            abort(403, 'This roster is not pending approval');
        }

        return view('dashboards.dc-admin.roster-show', compact('roster'));
    }

    public function dcAdminApprove(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can approve rosters');
        }

        $roster = \App\Models\DutyRoster::findOrFail($id);

        // Only allow approving SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be approved.');
        }

        try {
            DB::beginTransaction();

            // Check if this is a re-approval (roster was previously approved)
            $isReapproval = !is_null($roster->approved_at);

            $roster->status = 'APPROVED';
            $roster->approved_at = now();
            $roster->save();

            // Refresh roster to load all relationships
            $roster->refresh();
            $roster->load(['command', 'preparedBy', 'assignments.officer', 'oicOfficer', 'secondInCommandOfficer']);

            // Only notify if this is the first approval (not a re-approval)
            // If it's a re-approval, affected officers were already notified during edit
            if (!$isReapproval) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyDutyRosterApproved($roster, $user);
            }

            DB::commit();

            $successMessage = $isReapproval
                ? 'Roster re-approved successfully. Affected officers were already notified when the roster was edited.'
                : 'Roster approved successfully. All assigned officers and Staff Officer have been notified.';

            return redirect()->route('dc-admin.roster')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve roster: ' . $e->getMessage());
        }
    }

    public function dcAdminReject(Request $request, $id)
    {
        $user = auth()->user();

        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can reject rosters');
        }

        $roster = \App\Models\DutyRoster::findOrFail($id);

        // Only allow rejecting SUBMITTED rosters
        if ($roster->status !== 'SUBMITTED') {
            return redirect()->back()
                ->with('error', 'Only SUBMITTED rosters can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $roster->status = 'REJECTED';
            $roster->rejection_reason = $request->rejection_reason;
            $roster->save();

            // Refresh roster to load relationships
            $roster->refresh();
            $roster->load(['command', 'preparedBy']);

            // Notify Staff Officer about rejection
            $notificationService = app(NotificationService::class);
            $notificationService->notifyDutyRosterRejected($roster, $user, $request->rejection_reason);

            DB::commit();

            return redirect()->route('dc-admin.roster')
                ->with('success', 'Roster rejected. Staff Officer has been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject roster: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject roster: ' . $e->getMessage());
        }
    }
}


