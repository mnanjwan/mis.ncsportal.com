<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Promotion;
use App\Models\PromotionEligibilityList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends BaseController
{
    /**
     * List promotions
     */
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::with(['officer', 'eligibilityList']);

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        if ($request->has('eligibility_list_id')) {
            $query->where('eligibility_list_id', $request->eligibility_list_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $promotions = $query->paginate($perPage);

        return $this->paginatedResponse(
            $promotions->items(),
            [
                'current_page' => $promotions->currentPage(),
                'per_page' => $promotions->perPage(),
                'total' => $promotions->total(),
                'last_page' => $promotions->lastPage(),
            ]
        );
    }

    /**
     * Get promotion details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $promotion = Promotion::with(['officer', 'eligibilityList'])->findOrFail($id);

        return $this->successResponse($promotion);
    }

    /**
     * Create promotion eligibility list (Board)
     */
    public function createEligibilityList(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('Board')) {
            return $this->errorResponse(
                'Only Board can create eligibility lists',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'year' => 'required|integer|unique:promotion_eligibility_lists,year',
            'rank' => 'required|string',
            'criteria' => 'required|array|min:1',
            'criteria.*.criterion_type' => 'required|string',
            'criteria.*.value' => 'required|string',
        ]);

        $eligibilityList = PromotionEligibilityList::create([
            'year' => $request->year,
            'rank' => $request->rank,
            'created_by' => $request->user()->id,
        ]);

        foreach ($request->criteria as $criterion) {
            $eligibilityList->criteria()->create([
                'criterion_type' => $criterion['criterion_type'],
                'value' => $criterion['value'],
            ]);
        }

        return $this->successResponse([
            'id' => $eligibilityList->id,
            'year' => $eligibilityList->year,
            'rank' => $eligibilityList->rank,
        ], 'Eligibility list created successfully', 201);
    }

    /**
     * Generate eligibility list (Board)
     */
    public function generateEligibilityList(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('Board')) {
            return $this->errorResponse(
                'Only Board can generate eligibility lists',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $eligibilityList = PromotionEligibilityList::with('criteria')->findOrFail($id);

        // This would typically involve complex logic to evaluate officers
        // For now, we'll just mark it as generated
        $eligibilityList->update([
            'status' => 'GENERATED',
            'generated_at' => now(),
        ]);

        return $this->successResponse([
            'id' => $eligibilityList->id,
            'status' => $eligibilityList->status,
        ], 'Eligibility list generated successfully');
    }

    /**
     * Approve promotion (Board)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasRole('Board')) {
            return $this->errorResponse(
                'Only Board can approve promotions',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $promotion = Promotion::findOrFail($id);

        if ($promotion->status !== 'PENDING') {
            return $this->errorResponse(
                'Only pending promotions can be approved',
                null,
                400,
                'WORKFLOW_ERROR'
            );
        }

        $request->validate([
            'new_rank' => 'required|string',
            'effective_date' => 'required|date|after_or_equal:today',
        ]);

        $promotion->update([
            'status' => 'APPROVED',
            'new_rank' => $request->new_rank,
            'effective_date' => $request->effective_date,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        // Update officer's rank
        $promotion->officer->update([
            'substantive_rank' => $request->new_rank,
        ]);

        return $this->successResponse([
            'id' => $promotion->id,
            'status' => $promotion->status,
            'new_rank' => $promotion->new_rank,
        ], 'Promotion approved successfully');
    }
}

