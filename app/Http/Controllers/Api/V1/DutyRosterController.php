<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Command;
use App\Models\DutyRoster;
use App\Models\RosterAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DutyRosterController extends BaseController
{
    /**
     * List duty rosters
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = DutyRoster::with(['command', 'assignments.officer']);

        // Role-based filtering
        if ($user->hasRole('Staff Officer')) {
            if ($user->officer?->present_station) {
                $query->where('command_id', $user->officer->present_station);
            }
        }

        if ($request->has('command_id')) {
            $query->where('command_id', $request->command_id);
        }

        if ($request->has('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $perPage = $request->get('per_page', 20);
        $rosters = $query->paginate($perPage);

        return $this->paginatedResponse(
            $rosters->items(),
            [
                'current_page' => $rosters->currentPage(),
                'per_page' => $rosters->perPage(),
                'total' => $rosters->total(),
                'last_page' => $rosters->lastPage(),
            ]
        );
    }

    /**
     * Create duty roster (Staff Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;

        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $request->validate([
            'command_id' => 'required|exists:commands,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'assignments' => 'required|array|min:1',
            'assignments.*.officer_id' => 'required|exists:officers,id',
            'assignments.*.duty_date' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
            'assignments.*.duty_type' => 'required|string',
        ]);

        // Verify command access
        if ($user->hasRole('Staff Officer') && $officer->present_station != $request->command_id) {
            return $this->errorResponse(
                'You can only create rosters for your command',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $roster = DutyRoster::create([
            'command_id' => $request->command_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => $user->id,
        ]);

        foreach ($request->assignments as $assignment) {
            RosterAssignment::create([
                'roster_id' => $roster->id,
                'officer_id' => $assignment['officer_id'],
                'duty_date' => $assignment['duty_date'],
                'duty_type' => $assignment['duty_type'],
            ]);
        }

        return $this->successResponse([
            'id' => $roster->id,
            'start_date' => $roster->start_date,
            'end_date' => $roster->end_date,
        ], 'Duty roster created successfully', 201);
    }

    /**
     * Get roster details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $roster = DutyRoster::with(['command', 'assignments.officer'])->findOrFail($id);

        return $this->successResponse($roster);
    }

    /**
     * Get officer's duty schedule
     */
    public function officerSchedule(Request $request, $officerId): JsonResponse
    {
        $user = $request->user();

        // Officers can only see their own schedule
        if ($user->hasRole('Officer') && $user->officer?->id != $officerId) {
            return $this->errorResponse(
                'You can only view your own schedule',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $query = RosterAssignment::where('officer_id', $officerId)
            ->with(['roster.command']);

        if ($request->has('start_date')) {
            $query->where('duty_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('duty_date', '<=', $request->end_date);
        }

        $assignments = $query->orderBy('duty_date')->get();

        return $this->successResponse($assignments);
    }
}

