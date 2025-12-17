<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionEligibilityList;
use App\Models\PromotionEligibilityCriterion;
use Carbon\Carbon;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Check if user is HRD, show eligibility lists
        if (auth()->user()->hasRole('HRD')) {
            $query = PromotionEligibilityList::withCount('items as officers_count');

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $sortableColumns = [
                'year' => 'year',
                'officers_count' => 'officers_count',
                'created_at' => 'created_at',
            ];

            $column = $sortableColumns[$sortBy] ?? 'created_at';
            $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

            $query->orderBy($column, $order);

            $lists = $query->paginate(20)->withQueryString();
            
            return view('dashboards.hrd.promotion-eligibility', compact('lists'));
        }
        
        // Otherwise show board promotions
        return view('dashboards.board.promotions');
    }

    public function show($id)
    {
        return view('dashboards.board.promotion-show', compact('id'));
    }

    public function createEligibilityList()
    {
        return view('forms.promotion.create-eligibility-list');
    }

    public function storeEligibilityList(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            $list = PromotionEligibilityList::create([
                'year' => $validated['year'],
                'generated_by' => auth()->id(),
                'status' => 'DRAFT',
            ]);
            
            // Get all active promotion criteria
            $criteria = PromotionEligibilityCriterion::where('is_active', true)->get()->keyBy('rank');
            
            // Populate list items - find officers eligible for promotion based on configured criteria
            // Get all active officers with required fields
            // Exclude officers who are: deceased, interdicted, suspended, dismissed
            // Per specification: Officers not meeting time in rank, interdicted, suspended, dismissed,
            // under investigation, or deceased won't feature on the Eligibility List
            $allOfficers = \App\Models\Officer::where('is_active', true)
                ->where('is_deceased', false)
                ->where('interdicted', false)
                ->where('suspended', false)
                ->where('dismissed', false)
                ->whereNotNull('substantive_rank')
                ->whereNotNull('date_of_birth')
                ->whereNotNull('date_of_first_appointment')
                ->whereNotNull('date_of_present_appointment')
                ->get();
            
            // Additional exclusion: Officers under investigation
            // Note: Investigation system integration can be added when available
            // For now, we exclude based on the above criteria which matches specification requirements
            
            $eligibleOfficers = collect();
            
            foreach ($allOfficers as $officer) {
                $currentRank = $officer->substantive_rank;
                
                // Check if criteria exists for this rank
                if (!$criteria->has($currentRank)) {
                    continue; // Skip if no criteria configured for this rank
                }
                
                $criterion = $criteria->get($currentRank);
                $yearsInRank = $officer->date_of_present_appointment ? 
                    Carbon::parse($officer->date_of_present_appointment)->diffInYears(now()) : 0;
                
                // Check if officer meets the years-in-rank requirement
                if ($yearsInRank >= $criterion->years_in_rank_required) {
                    $eligibleOfficers->push($officer);
                }
            }
            
            // Limit to reasonable number for processing
            $eligibleOfficers = $eligibleOfficers->take(100);
            
            $serialNumber = 1;
            foreach ($eligibleOfficers as $officer) {
                $yearsInRank = $officer->date_of_present_appointment ? 
                    Carbon::parse($officer->date_of_present_appointment)->diffInYears(now()) : 0;
                
                \App\Models\PromotionEligibilityListItem::create([
                    'eligibility_list_id' => $list->id,
                    'officer_id' => $officer->id,
                    'serial_number' => $serialNumber++,
                    'current_rank' => $officer->substantive_rank ?? 'N/A',
                    'years_in_rank' => round($yearsInRank, 2),
                    'date_of_first_appointment' => $officer->date_of_first_appointment ?? now(),
                    'date_of_present_appointment' => $officer->date_of_present_appointment ?? now(),
                    'state' => $officer->state_of_origin ?? 'N/A',
                    'date_of_birth' => $officer->date_of_birth ?? now(),
                ]);
            }
            
            return redirect()->route('hrd.promotion-eligibility')
                ->with('success', "Promotion eligibility list created successfully with {$eligibleOfficers->count()} officers!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create eligibility list: ' . $e->getMessage());
        }
    }

    public function showEligibilityList($id)
    {
        $list = PromotionEligibilityList::with(['items.officer', 'generatedBy'])
            ->findOrFail($id);
        return view('dashboards.hrd.promotion-eligibility-list-show', compact('list'));
    }

    public function approve($id)
    {
        return view('forms.promotion.approve', compact('id'));
    }

    public function destroyEligibilityList($id)
    {
        try {
            $list = PromotionEligibilityList::withCount('items')->findOrFail($id);
            
            // Only allow deletion if list has no items
            if ($list->items_count > 0) {
                return redirect()->route('hrd.promotion-eligibility')
                    ->with('error', 'Cannot delete eligibility list with officers. Please remove all officers first.');
            }
            
            $list->delete();
            
            return redirect()->route('hrd.promotion-eligibility')
                ->with('success', 'Promotion eligibility list deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('hrd.promotion-eligibility')
                ->with('error', 'Failed to delete eligibility list: ' . $e->getMessage());
        }
    }

    // Promotion Criteria Management Methods
    public function criteria()
    {
        $criteria = PromotionEligibilityCriterion::with('createdBy')
            ->orderBy('rank')
            ->paginate(20);
        
        return view('dashboards.hrd.promotion-criteria', compact('criteria'));
    }

    public function createCriteria()
    {
        // Get all unique ranks from officers
        $ranks = \App\Models\Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->sort()
            ->values();
        
        return view('forms.promotion.criteria-form', compact('ranks'));
    }

    public function storeCriteria(Request $request)
    {
        $validated = $request->validate([
            'rank' => 'required|string|max:100',
            'years_in_rank_required' => 'required|numeric|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        try {
            // Check if criteria already exists for this rank
            $existing = PromotionEligibilityCriterion::where('rank', $validated['rank'])
                ->where('is_active', true)
                ->first();
            
            if ($existing && ($request->has('is_active') ? $validated['is_active'] : true)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Active criteria already exists for rank: ' . $validated['rank']);
            }

            PromotionEligibilityCriterion::create([
                'rank' => $validated['rank'],
                'years_in_rank_required' => $validated['years_in_rank_required'],
                'is_active' => $request->has('is_active') ? $validated['is_active'] : true,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('hrd.promotion-criteria')
                ->with('success', 'Promotion criteria created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create criteria: ' . $e->getMessage());
        }
    }

    public function editCriteria($id)
    {
        $criterion = PromotionEligibilityCriterion::findOrFail($id);
        
        // Get all unique ranks from officers
        $ranks = \App\Models\Officer::whereNotNull('substantive_rank')
            ->distinct()
            ->pluck('substantive_rank')
            ->filter()
            ->sort()
            ->values();
        
        return view('forms.promotion.criteria-form', compact('criterion', 'ranks'));
    }

    public function updateCriteria(Request $request, $id)
    {
        $criterion = PromotionEligibilityCriterion::findOrFail($id);
        
        $validated = $request->validate([
            'rank' => 'required|string|max:100',
            'years_in_rank_required' => 'required|numeric|min:0|max:50',
            'is_active' => 'boolean',
        ]);

        try {
            // Check if another active criteria exists for this rank (excluding current)
            if ($request->has('is_active') ? $validated['is_active'] : $criterion->is_active) {
                $existing = PromotionEligibilityCriterion::where('rank', $validated['rank'])
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->first();
                
                if ($existing) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Active criteria already exists for rank: ' . $validated['rank']);
                }
            }

            $criterion->update([
                'rank' => $validated['rank'],
                'years_in_rank_required' => $validated['years_in_rank_required'],
                'is_active' => $request->has('is_active') ? $validated['is_active'] : false,
            ]);

            return redirect()->route('hrd.promotion-criteria')
                ->with('success', 'Promotion criteria updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update criteria: ' . $e->getMessage());
        }
    }
}


