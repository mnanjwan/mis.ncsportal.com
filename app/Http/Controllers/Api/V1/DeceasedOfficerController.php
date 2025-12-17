<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DeceasedOfficer;
use App\Models\Officer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeceasedOfficerController extends BaseController
{
    /**
     * List deceased officers
     */
    public function index(Request $request): JsonResponse
    {
        $query = DeceasedOfficer::with('officer');

        if ($request->has('command_id')) {
            $query->whereHas('officer', function ($q) use ($request) {
                $q->where('present_station', $request->command_id);
            });
        }

        if ($request->has('year')) {
            $query->whereYear('date_of_death', $request->year);
        }

        $perPage = $request->get('per_page', 20);
        $deceased = $query->paginate($perPage);

        return $this->paginatedResponse(
            $deceased->items(),
            [
                'current_page' => $deceased->currentPage(),
                'per_page' => $deceased->perPage(),
                'total' => $deceased->total(),
                'last_page' => $deceased->lastPage(),
            ]
        );
    }

    /**
     * Record deceased officer (HRD/Staff Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['HRD', 'Staff Officer'])) {
            return $this->errorResponse(
                'Only HRD or Staff Officer can record deceased officers',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'date_of_death' => 'required|date|before_or_equal:today',
            'cause_of_death' => 'nullable|string',
            'place_of_death' => 'nullable|string',
            'death_certificate_url' => 'nullable|string',
        ]);

        $officer = Officer::findOrFail($request->officer_id);

        // Check if already recorded
        $existing = DeceasedOfficer::where('officer_id', $request->officer_id)->first();
        if ($existing) {
            return $this->errorResponse(
                'Officer already recorded as deceased',
                null,
                400,
                'DUPLICATE_ENTRY'
            );
        }

        $deceased = DeceasedOfficer::create([
            'officer_id' => $request->officer_id,
            'date_of_death' => $request->date_of_death,
            'cause_of_death' => $request->cause_of_death,
            'place_of_death' => $request->place_of_death,
            'death_certificate_url' => $request->death_certificate_url,
            'recorded_by' => $user->id,
        ]);

        // Mark officer as deceased
        $officer->update([
            'is_deceased' => true,
            'is_active' => false,
        ]);

        return $this->successResponse([
            'id' => $deceased->id,
            'officer_id' => $deceased->officer_id,
            'date_of_death' => $deceased->date_of_death,
        ], 'Deceased officer recorded successfully', 201);
    }

    /**
     * Get deceased officer details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $deceased = DeceasedOfficer::with('officer')->findOrFail($id);

        return $this->successResponse($deceased);
    }
}

