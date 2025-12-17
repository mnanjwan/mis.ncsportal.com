<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Officer;
use App\Models\RetirementList;
use App\Models\RetirementListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetirementController extends BaseController
{
    /**
     * List retirement lists
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can view retirement lists',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $query = RetirementList::with('items.officer');

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $lists = $query->get();

        return $this->successResponse($lists);
    }

    /**
     * Generate retirement list (HRD)
     */
    public function generateList(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can generate retirement lists',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'year' => 'required|integer|unique:retirement_lists,year',
        ]);

        // Find officers who will retire in the specified year
        // Retirement age is typically 60 years
        $retirementYear = $request->year;
        $birthYear = $retirementYear - 60;

        $officers = Officer::whereYear('date_of_birth', $birthYear)
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->get();

        $retirementList = RetirementList::create([
            'year' => $retirementYear,
            'status' => 'DRAFT',
            'created_by' => $request->user()->id,
        ]);

        foreach ($officers as $officer) {
            RetirementListItem::create([
                'retirement_list_id' => $retirementList->id,
                'officer_id' => $officer->id,
                'expected_retirement_date' => $officer->date_of_birth->copy()->addYears(60),
                'status' => 'PENDING',
            ]);
        }

        return $this->successResponse([
            'id' => $retirementList->id,
            'year' => $retirementList->year,
            'officers_count' => $retirementList->items->count(),
        ], 'Retirement list generated successfully', 201);
    }

    /**
     * Get retirement list details
     */
    public function show(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can view retirement lists',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $list = RetirementList::with('items.officer')->findOrFail($id);

        return $this->successResponse($list);
    }

    /**
     * Process retirement (HRD)
     */
    public function processRetirement(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('HRD')) {
            return $this->errorResponse(
                'Only HRD can process retirements',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $item = RetirementListItem::with('officer')->findOrFail($id);

        if ($item->status !== 'PENDING') {
            return $this->errorResponse(
                'Only pending retirements can be processed',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $request->validate([
            'actual_retirement_date' => 'required|date',
        ]);

        $item->update([
            'actual_retirement_date' => $request->actual_retirement_date,
            'status' => 'PROCESSED',
            'processed_at' => now(),
        ]);

        // Mark officer as retired
        $item->officer->update([
            'is_active' => false,
        ]);

        return $this->successResponse([
            'id' => $item->id,
            'status' => $item->status,
            'actual_retirement_date' => $item->actual_retirement_date,
        ], 'Retirement processed successfully');
    }
}

