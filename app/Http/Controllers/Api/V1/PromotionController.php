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
        $query = Promotion::with(['officer', 'eligibilityListItem']);

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        if ($request->has('eligibility_list_id')) {
            $query->whereHas('eligibilityListItem', function($q) use ($request) {
                $q->where('eligibility_list_id', $request->eligibility_list_id);
            });
        }

        // Note: Promotion model doesn't have a status field, using approved_by_board instead
        if ($request->has('status')) {
            if ($request->status === 'APPROVED') {
                $query->where('approved_by_board', true);
            } elseif ($request->status === 'PENDING') {
                $query->where('approved_by_board', false);
            }
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
        $promotion = Promotion::with(['officer', 'eligibilityListItem.eligibilityList'])->findOrFail($id);

        return $this->successResponse($promotion);
    }

    /**
     * List promotion eligibility lists
     */
    public function eligibilityLists(Request $request): JsonResponse
    {
        $query = PromotionEligibilityList::with(['generatedBy', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        $perPage = $request->get('per_page', 20);
        $lists = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add officers_count to each list
        $lists->getCollection()->transform(function ($list) {
            $list->officers_count = $list->items()->count();
            return $list;
        });

        return $this->paginatedResponse(
            $lists->items(),
            [
                'current_page' => $lists->currentPage(),
                'per_page' => $lists->perPage(),
                'total' => $lists->total(),
                'last_page' => $lists->lastPage(),
            ]
        );
    }

    /**
     * Get dashboard statistics
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        // Count pending promotions (eligibility lists submitted to board)
        $pendingCount = PromotionEligibilityList::where('status', 'SUBMITTED_TO_BOARD')->count();

        // Count approved promotions this year
        $currentYear = date('Y');
        $approvedThisYear = Promotion::where('approved_by_board', true)
            ->where(function($query) use ($currentYear) {
                $query->whereYear('board_meeting_date', $currentYear)
                      ->orWhere(function($q) use ($currentYear) {
                          $q->whereNull('board_meeting_date')
                            ->whereYear('created_at', $currentYear);
                      });
            })
            ->count();

        return $this->successResponse([
            'pending_promotions' => $pendingCount,
            'approved_this_year' => $approvedThisYear,
        ]);
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

        $eligibilityList = PromotionEligibilityList::findOrFail($id);

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

        if ($promotion->approved_by_board) {
            return $this->errorResponse(
                'This promotion has already been approved',
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

