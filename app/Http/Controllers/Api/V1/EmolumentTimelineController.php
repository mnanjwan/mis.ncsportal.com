<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\EmolumentTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmolumentTimelineController extends BaseController
{
    /**
     * Get active emolument timeline
     */
    public function index(Request $request): JsonResponse
    {
        $timeline = EmolumentTimeline::where('is_active', true)
            ->orderBy('year', 'desc')
            ->first();

        if (!$timeline) {
            return $this->successResponse(null, 'No active timeline found');
        }

        return $this->successResponse([
            'id' => $timeline->id,
            'year' => $timeline->year,
            'start_date' => $timeline->start_date->format('Y-m-d'),
            'end_date' => $timeline->end_date->format('Y-m-d'),
            'is_extended' => $timeline->is_extended,
            'extension_end_date' => $timeline->extension_end_date?->format('Y-m-d'),
            'is_active' => $timeline->is_active,
            'can_submit' => $timeline->can_submit,
            'days_remaining' => $timeline->days_remaining,
        ]);
    }

    /**
     * Get all active emolument timelines
     */
    public function active(Request $request): JsonResponse
    {
        $timelines = EmolumentTimeline::where('is_active', true)
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($timeline) {
                return [
                    'id' => $timeline->id,
                    'year' => $timeline->year,
                    'start_date' => $timeline->start_date->format('d M Y'),
                    'end_date' => $timeline->end_date->format('d M Y'),
                    'is_extended' => $timeline->is_extended,
                    'extension_end_date' => $timeline->extension_end_date?->format('d M Y'),
                    'can_submit' => $timeline->can_submit,
                    'days_remaining' => $timeline->days_remaining,
                ];
            });

        return $this->successResponse($timelines, 'Active timelines retrieved successfully');
    }

    /**
     * Create emolument timeline (HRD only)
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user has HRD role
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can create emolument timelines',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'year' => 'required|integer|unique:emolument_timelines,year',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date',
        ]);

        $timeline = EmolumentTimeline::create([
            'year' => $request->year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return $this->successResponse($timeline, 'Emolument timeline created successfully', 201);
    }

    /**
     * Extend emolument timeline (HRD only)
     */
    public function extend(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can extend emolument timelines',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $timeline = EmolumentTimeline::findOrFail($id);

        $request->validate([
            'extension_end_date' => 'required|date|after:end_date',
        ]);

        $timeline->update([
            'is_extended' => true,
            'extension_end_date' => $request->extension_end_date,
        ]);

        return $this->successResponse($timeline, 'Timeline extended successfully');
    }
}

