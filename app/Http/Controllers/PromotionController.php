<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionEligibilityList;
use App\Models\PromotionEligibilityCriterion;
use App\Models\Promotion;
use App\Services\PromotionService;
use App\Services\NotificationService;
use App\Models\DutyRoster;
use App\Models\RosterAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            $query = PromotionEligibilityList::withCount('items as officers_count')
                ->with(['items.officer']);

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
            
            // Calculate eligible officers count for each list (excluding ineligible officers)
            // This shows the actual count of officers who are currently eligible
            // Excludes: Interdicted, Suspended, Under ongoing investigation, Dismissed, Deceased
            $lists->getCollection()->transform(function($list) {
                $eligibleCount = $list->items->filter(function($item) {
                    if (!$item->officer) {
                        return false;
                    }
                    
                    $officer = $item->officer;
                    
                    // Exclude officers who are indicted/interdicted, suspended, dismissed, or under investigation
                    // This matches the same exclusion criteria used in the listing display
                    if ($officer->interdicted || 
                        $officer->suspended || 
                        $officer->ongoing_investigation || 
                        $officer->dismissed ||
                        $officer->is_deceased) {
                        return false;
                    }
                    
                    return true;
                })->count();
                
                // Set eligible count (excludes ineligible officers)
                $list->eligible_officers_count = $eligibleCount;
                
                return $list;
            });
            
            return view('dashboards.hrd.promotion-eligibility', compact('lists'));
        }
        
        // Otherwise show board promotions
        return view('dashboards.board.promotions');
    }

    public function show($id)
    {
        if (!auth()->user()->hasRole('Board')) {
            abort(403, 'Unauthorized');
        }

        $list = PromotionEligibilityList::with(['generatedBy', 'items.officer'])
            ->findOrFail($id);

        // Board should primarily review lists that have been submitted.
        if (($list->status ?? 'DRAFT') !== 'SUBMITTED_TO_BOARD') {
            return redirect()->route('board.promotions')
                ->with('error', 'This promotion eligibility list has not been submitted to the Board yet.');
        }

        return view('dashboards.board.promotion-show', compact('list'));
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
            // Exclude officers who are: deceased, interdicted, suspended, dismissed, under investigation
            // Per specification: Officers not meeting time in rank, interdicted, suspended, dismissed,
            // under investigation, or deceased won't feature on the Eligibility List
            $allOfficers = \App\Models\Officer::where('is_active', true)
                ->where('is_deceased', false)
                ->eligibleForPromotionAndRetirement()
                ->whereNotNull('substantive_rank')
                ->whereNotNull('date_of_birth')
                ->whereNotNull('date_of_first_appointment')
                ->whereNotNull('date_of_present_appointment')
                ->get();
            
            $eligibleOfficers = collect();
            
            foreach ($allOfficers as $officer) {
                $currentRank = $officer->substantive_rank;
                
                // Normalize rank to abbreviation format (same as criteria)
                $normalizedRank = $this->normalizeRankToAbbreviation($currentRank);
                
                // Check if criteria exists for this normalized rank
                if (!$criteria->has($normalizedRank)) {
                    continue; // Skip if no criteria configured for this rank
                }
                
                $criterion = $criteria->get($normalizedRank);
                $yearsInRank = $officer->date_of_present_appointment ? 
                    Carbon::parse($officer->date_of_present_appointment)->diffInYears(now()) : 0;
                
                // Check if officer meets the years-in-rank requirement
                if ($yearsInRank >= $criterion->years_in_rank_required) {
                    $eligibleOfficers->push($officer);
                }
            }
            
            // Limit to reasonable number for processing
            $eligibleOfficers = $eligibleOfficers->take(100);
            
            $officersCount = $eligibleOfficers->count();
            
            if ($officersCount === 0) {
                // Delete the empty list
                $list->delete();
                
                // Calculate future years when officers will become eligible
                $futureEligibilityYears = $this->calculateFutureEligibilityYears($allOfficers, $criteria);
                
                $yearsSuggestion = '';
                if (!empty($futureEligibilityYears)) {
                    $yearsList = implode(', ', $futureEligibilityYears);
                    $yearsSuggestion = "<strong>Tip:</strong> Officers will become eligible for promotion in the following years: {$yearsList}. Consider checking those years instead.";
                } else {
                    $yearsSuggestion = "<strong>Tip:</strong> No officers found who will become eligible for promotion in the near future. Please check if promotion criteria are properly configured.";
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', "No officers found who are currently eligible for promotion based on the configured criteria. 
                    
                    {$yearsSuggestion}
                    
                    The system checks for officers who meet the years-in-rank requirement for their current rank. Officers who are interdicted, suspended, dismissed, under investigation, or deceased are excluded.");
            }
            
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
                ->with('success', "Promotion eligibility list created successfully with {$officersCount} officers!");
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
        
        // Filter out officers who are now ineligible (indicted/interdicted, suspended, dismissed, or under investigation)
        // This ensures that even if a list was created before status changes, ineligible officers won't appear
        $list->items = $list->items->filter(function($item) {
            if (!$item->officer) {
                return false; // Remove items with no officer
            }
            
            $officer = $item->officer;
            
            // Exclude officers who are indicted/interdicted, suspended, dismissed, or under investigation
            if ($officer->interdicted || 
                $officer->suspended || 
                $officer->ongoing_investigation || 
                $officer->dismissed ||
                $officer->is_deceased) {
                return false;
            }
            
            return true;
        })->values(); // Re-index the collection
        
        return view('dashboards.hrd.promotion-eligibility-list-show', compact('list'));
    }

    public function finalizeEligibilityList($id)
    {
        if (!auth()->user()->hasRole('HRD')) {
            abort(403, 'Unauthorized');
        }

        $list = PromotionEligibilityList::withCount('items')->findOrFail($id);

        if ($list->items_count <= 0) {
            return redirect()->back()->with('error', 'Cannot finalize an empty promotion eligibility list.');
        }

        if ($list->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Only DRAFT promotion eligibility lists can be finalized.');
        }

        $list->update(['status' => 'FINALIZED']);

        return redirect()->back()->with('success', 'Promotion eligibility list finalized successfully.');
    }

    public function submitEligibilityListToBoard($id)
    {
        if (!auth()->user()->hasRole('HRD')) {
            abort(403, 'Unauthorized');
        }

        $list = PromotionEligibilityList::withCount('items')->findOrFail($id);

        if ($list->items_count <= 0) {
            return redirect()->back()->with('error', 'Cannot submit an empty promotion eligibility list to Board.');
        }

        if ($list->status !== 'FINALIZED') {
            return redirect()->back()->with('error', 'Only FINALIZED promotion eligibility lists can be submitted to Board.');
        }

        $list->update(['status' => 'SUBMITTED_TO_BOARD']);

        // Notify Board users (in-app + email).
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyByRole(
                'Board',
                'promotion_list_submitted',
                'Promotion Eligibility List Submitted',
                "A promotion eligibility list for year {$list->year} has been submitted to the Board for review and approval.",
                'promotion_eligibility_list',
                $list->id,
                true
            );
        } catch (\Throwable $e) {
            // Don't block submission if notifications fail.
            \Log::warning('Failed to notify Board about promotion list submission', [
                'list_id' => $list->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('success', 'Promotion eligibility list submitted to Board successfully.');
    }

    public function approve($id)
    {
        return view('forms.promotion.approve', compact('id'));
    }

    public function bulkApprove(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Board')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'promotion_date' => 'required|date',
            'board_meeting_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer',
        ]);

        $list = PromotionEligibilityList::with(['items.officer'])
            ->findOrFail($id);

        if (($list->status ?? 'DRAFT') !== 'SUBMITTED_TO_BOARD') {
            return redirect()->back()->with('error', 'Only lists submitted to the Board can be approved.');
        }

        if (!$list->items || $list->items->isEmpty()) {
            return redirect()->back()->with('error', 'This list has no officers to approve.');
        }

        $selectedIds = collect($validated['selected_items'])
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        $selectedItems = $list->items->whereIn('id', $selectedIds)->values();
        if ($selectedItems->isEmpty()) {
            return redirect()->back()->with('error', 'No valid officers were selected for approval.');
        }

        $promotionService = app(PromotionService::class);

        $promotionDate = \Carbon\Carbon::parse($validated['promotion_date'])->startOfDay();
        $boardMeetingDate = !empty($validated['board_meeting_date'])
            ? \Carbon\Carbon::parse($validated['board_meeting_date'])->startOfDay()
            : null;

        $notes = $validated['notes'] ?? null;

        $createdOrUpdated = 0;
        $skipped = 0;
        $skippedReasons = [];

        DB::beginTransaction();
        try {
            foreach ($selectedItems as $item) {
                $officer = $item->officer;
                if (!$officer) {
                    $skipped++;
                    $skippedReasons[] = 'Missing officer record for one list item.';
                    continue;
                }

                $fromRank = $promotionService->normalizeRankToAbbreviation($officer->substantive_rank ?? $item->current_rank);
                $toRank = $promotionService->getNextRank($fromRank);

                if (empty($toRank)) {
                    $skipped++;
                    $skippedReasons[] = "Unable to determine next rank for officer {$officer->service_number} (current: {$fromRank}).";
                    continue;
                }

                // Create/Update a promotion record tied to this eligibility list item.
                $promotion = Promotion::updateOrCreate(
                    ['eligibility_list_item_id' => $item->id],
                    [
                        'officer_id' => $officer->id,
                        'from_rank' => (string) ($fromRank ?? ''),
                        'to_rank' => $toRank,
                        'promotion_date' => $promotionDate,
                        'approved_by_board' => false, // flip below to trigger approval hooks
                        'board_meeting_date' => $boardMeetingDate,
                        'notes' => $notes,
                    ]
                );

                if (!$promotion->approved_by_board) {
                    $promotion->update(['approved_by_board' => true]);
                }

                // Update officer’s rank and reset date_of_present_appointment for future eligibility calculations.
                // Get the corresponding grade level for the new rank
                $toGradeLevel = $promotionService->getGradeLevelForRank($toRank);

                // Update officer's rank, grade level, date_of_present_appointment, and profile picture requirement.
                // Clear profile_picture_updated_at to force a new upload after promotion.
                $officer->update([
                    'substantive_rank' => $toRank,
                    'salary_grade_level' => $toGradeLevel,
                    'date_of_present_appointment' => $promotionDate,
                    'profile_picture_required_after_promotion_at' => $promotionDate,
                    'profile_picture_updated_at' => null, // Force new picture upload
                ]);

                // Notify officer about profile picture requirement (in-app + email)
                if ($officer->user) {
                    app(NotificationService::class)->notify(
                        $officer->user,
                        'profile_picture_update_required',
                        'Profile Picture Update Required',
                        'Your promotion has been approved. Please update your profile picture to continue using all officer services.',
                        'officer',
                        $officer->id,
                        false
                    );
                }

                $createdOrUpdated++;
            }

            // Keep list SUBMITTED_TO_BOARD until all items have an approved promotion.
            $remaining = Promotion::whereIn('eligibility_list_item_id', $list->items->pluck('id'))
                ->where('approved_by_board', true)
                ->count();
            $total = $list->items->count();
            if ($remaining >= $total && $total > 0) {
                $list->update(['status' => 'FINALIZED']);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Bulk approval failed: ' . $e->getMessage());
        }

        $message = "Bulk approval completed. Processed: {$createdOrUpdated}. Skipped: {$skipped}.";
        if (!empty($skippedReasons)) {
            $message .= ' Skips: ' . implode(' ', array_slice(array_unique($skippedReasons), 0, 3));
        }

        return redirect()->back()->with('success', $message);
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

    /**
     * Export Promotion Eligibility List as CSV
     */
    public function exportEligibilityList($id)
    {
        $list = PromotionEligibilityList::with(['items.officer', 'generatedBy'])
            ->findOrFail($id);
        
        // Filter out officers who are now ineligible (indicted/interdicted, suspended, dismissed, or under investigation)
        // This ensures that even if a list was created before status changes, ineligible officers won't appear
        $filteredItems = $list->items->filter(function($item) {
            if (!$item->officer) {
                return false; // Remove items with no officer
            }
            
            $officer = $item->officer;
            
            // Exclude officers who are indicted/interdicted, suspended, dismissed, or under investigation
            if ($officer->interdicted || 
                $officer->suspended || 
                $officer->ongoing_investigation || 
                $officer->dismissed ||
                $officer->is_deceased) {
                return false;
            }
            
            return true;
        });
        
        // Get all items with their officers (same logic as print)
        $items = $filteredItems->map(function($item) {
            $officer = $item->officer;
            
            // Get unit from current active roster (as OIC/2IC or from assignment)
            $unit = null;
            
            // Check if officer is OIC or 2IC of an active roster
            $activeRosterAsOIC = DutyRoster::where('oic_officer_id', $officer->id)
                ->where('status', 'APPROVED')
                ->where('roster_period_start', '<=', now())
                ->where('roster_period_end', '>=', now())
                ->first();
            
            if ($activeRosterAsOIC && $activeRosterAsOIC->unit) {
                $unit = $activeRosterAsOIC->unit;
            } else {
                // Check if officer is 2IC of an active roster
                $activeRosterAs2IC = DutyRoster::where('second_in_command_officer_id', $officer->id)
                    ->where('status', 'APPROVED')
                    ->where('roster_period_start', '<=', now())
                    ->where('roster_period_end', '>=', now())
                    ->first();
                
                if ($activeRosterAs2IC && $activeRosterAs2IC->unit) {
                    $unit = $activeRosterAs2IC->unit;
                } else {
                    // Check if officer has a roster assignment with an active roster
                    $currentRosterAssignment = RosterAssignment::where('officer_id', $officer->id)
                        ->whereHas('roster', function ($query) {
                            $query->where('status', 'APPROVED')
                                  ->where('roster_period_start', '<=', now())
                                  ->where('roster_period_end', '>=', now());
                        })
                        ->with(['roster:id,unit'])
                        ->latest('duty_date')
                        ->first();
                    
                    if ($currentRosterAssignment && $currentRosterAssignment->roster && $currentRosterAssignment->roster->unit) {
                        $unit = $currentRosterAssignment->roster->unit;
                    }
                }
            }
            
            return [
                'serial_number' => $item->serial_number,
                'service_number' => $officer->service_number ?? 'N/A',
                'rank' => $item->current_rank ?? ($officer->substantive_rank ?? 'N/A'),
                'initials' => $officer->initials ?? '',
                'name' => $officer->surname ?? '',
                'unit' => $unit,
                'state' => $item->state ?? ($officer->state_of_origin ?? 'N/A'),
                'date_of_birth' => $item->date_of_birth ?? ($officer->date_of_birth ?? null),
                'date_of_first_appointment' => $item->date_of_first_appointment ?? ($officer->date_of_first_appointment ?? null),
            ];
        })->toArray();
        
        // Sort by rank in descending order (same as print)
        $rankOrder = [
            'CGC' => 1, 'DCG' => 2, 'ACG' => 3, 'CC' => 4, 'DC' => 5, 'AC' => 6,
            'CSC' => 7, 'SC' => 8, 'DSC' => 9, 'ASC I' => 10, 'ASC II' => 11,
            'IC' => 12, 'AIC' => 13, 'CA I' => 14, 'CA II' => 15, 'CA III' => 16,
        ];
        
        usort($items, function($a, $b) use ($rankOrder) {
            $rankA = $this->normalizeRankForSorting($a['rank'], $rankOrder);
            $rankB = $this->normalizeRankForSorting($b['rank'], $rankOrder);
            return $rankA <=> $rankB;
        });
        
        // Reassign serial numbers after sorting
        foreach ($items as $index => &$item) {
            $item['serial_number'] = $index + 1;
        }
        unset($item);
        
        // Generate filename
        $filename = 'promotion_eligibility_list_' . $list->year . '_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // Write CSV headers
            fputcsv($file, [
                'S/N',
                'Service Number',
                'Rank',
                'Initial',
                'Name',
                'Unit',
                'State',
                'Date of Birth (DOB)',
                'Date of First Appointment (DOFA)'
            ]);
            
            // Write data rows
            foreach ($items as $item) {
                fputcsv($file, [
                    $item['serial_number'],
                    $item['service_number'] ?? 'N/A',
                    $item['rank'],
                    $item['initials'],
                    $item['name'],
                    $item['unit'] ?? '',
                    $item['state'],
                    $item['date_of_birth'] ? Carbon::parse($item['date_of_birth'])->format('d/m/Y') : '',
                    $item['date_of_first_appointment'] ? Carbon::parse($item['date_of_first_appointment'])->format('d/m/Y') : '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Normalize rank for sorting (extract abbreviation from full rank names)
     */
    private function normalizeRankForSorting($rank, $rankOrder)
    {
        if (empty($rank)) {
            return 999; // Put empty ranks at the end
        }
        
        // If already an abbreviation, return its order
        if (isset($rankOrder[$rank])) {
            return $rankOrder[$rank];
        }
        
        // Try to extract abbreviation from parentheses
        if (preg_match('/\(([A-Z\s]+)\)/', $rank, $matches)) {
            $abbr = trim($matches[1]);
            if (isset($rankOrder[$abbr])) {
                return $rankOrder[$abbr];
            }
        }
        
        // Try partial matching
        foreach ($rankOrder as $abbr => $order) {
            if (stripos($rank, $abbr) !== false) {
                return $order;
            }
        }
        
        // If no match found, put at end
        return 999;
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
        // Use standard rank abbreviations (excluding top ranks that don't need promotion criteria)
        // CGC, DCG, ACG, and CC are the highest ranks and don't get promoted further
        $ranks = [
            'DC',
            'AC',
            'CSC',
            'SC',
            'DSC',
            'ASC I',
            'ASC II',
            'IC',
            'AIC',
            'CA I',
            'CA II',
            'CA III',
        ];
        
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
        
        // Use standard rank abbreviations (excluding top ranks that don't need promotion criteria)
        // CGC, DCG, ACG, and CC are the highest ranks and don't get promoted further
        $ranks = [
            'DC',
            'AC',
            'CSC',
            'SC',
            'DSC',
            'ASC I',
            'ASC II',
            'IC',
            'AIC',
            'CA I',
            'CA II',
            'CA III',
        ];
        
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

    /**
     * Calculate future years when officers will become eligible for promotion
     * 
     * @param \Illuminate\Support\Collection $officers
     * @param \Illuminate\Support\Collection $criteria
     * @return array Array of years (sorted, unique)
     */
    private function calculateFutureEligibilityYears($officers, $criteria)
    {
        $futureYears = collect();
        $currentYear = now()->year;
        $maxFutureYear = $currentYear + 20; // Look up to 20 years ahead
        
        foreach ($officers as $officer) {
            $currentRank = $officer->substantive_rank;
            $normalizedRank = $this->normalizeRankToAbbreviation($currentRank);
            
            // Check if criteria exists for this rank
            if (!$criteria->has($normalizedRank)) {
                continue;
            }
            
            $criterion = $criteria->get($normalizedRank);
            
            // Skip if officer doesn't have date_of_present_appointment
            if (!$officer->date_of_present_appointment) {
                continue;
            }
            
            // Calculate current years in rank
            $dateOfAppointment = Carbon::parse($officer->date_of_present_appointment);
            $yearsInRank = $dateOfAppointment->diffInYears(now());
            
            // If already eligible, skip (we're looking for future eligibility)
            if ($yearsInRank >= $criterion->years_in_rank_required) {
                continue;
            }
            
            // Calculate when officer will become eligible
            // Add the required years to appointment date to get eligibility date
            $eligibilityDate = $dateOfAppointment->copy()->addYears($criterion->years_in_rank_required);
            $eligibilityYear = $eligibilityDate->year;
            
            // Only include years within reasonable range
            if ($eligibilityYear >= $currentYear && $eligibilityYear <= $maxFutureYear) {
                $futureYears->push($eligibilityYear);
            }
        }
        
        // Return sorted, unique years
        return $futureYears->unique()->sort()->values()->toArray();
    }

    /**
     * Normalize rank from full name to abbreviation format
     * This ensures matching between officer ranks (which may be full names) 
     * and criteria ranks (which are abbreviations)
     */
    private function normalizeRankToAbbreviation($rank)
    {
        if (empty($rank)) {
            return $rank;
        }

        // Standard rank abbreviations
        $standardRanks = [
            'CGC', 'DCG', 'ACG', 'CC', 'DC', 'AC',
            'CSC', 'SC', 'DSC', 'ASC I', 'ASC II',
            'IC', 'AIC', 'CA I', 'CA II', 'CA III',
        ];

        // If already an abbreviation, return as is
        if (in_array($rank, $standardRanks)) {
            return $rank;
        }

        // Mapping from full names to abbreviations
        $rankMapping = [
            'Comptroller General of Customs (CGC) GL18' => 'CGC',
            'Comptroller General' => 'CGC',
            'Deputy Comptroller General of Customs (DCG) GL17' => 'DCG',
            'Deputy Comptroller General' => 'DCG',
            'Assistant Comptroller General (ACG) of Customs GL 16' => 'ACG',
            'Assistant Comptroller General' => 'ACG',
            'Comptroller of Customs (CC) GL15' => 'CC',
            'Comptroller' => 'CC',
            'Deputy Comptroller of Customs (DC) GL14' => 'DC',
            'Deputy Comptroller' => 'DC',
            'Assistant Comptroller of Customs (AC) GL13' => 'AC',
            'Assistant Comptroller' => 'AC',
            'Chief Superintendent of Customs (CSC) GL12' => 'CSC',
            'Chief Superintendent' => 'CSC',
            'Superintendent of Customs (SC) GL11' => 'SC',
            'Superintendent' => 'SC',
            'Deputy Superintendent of Customs (DSC) GL10' => 'DSC',
            'Deputy Superintendent' => 'DSC',
            'Assistant Superintendent of Customs Grade I (ASC I) GL 09' => 'ASC I',
            'Assistant Superintendent Grade I' => 'ASC I',
            'Assistant Superintendent of Customs Grade II (ASC II) GL 08' => 'ASC II',
            'Assistant Superintendent Grade II' => 'ASC II',
            'Assistant Superintendent' => 'ASC I', // Default to ASC I if ambiguous
            'Inspector of Customs (IC) GL07' => 'IC',
            'Inspector' => 'IC',
            'Assistant Inspector of Customs (AIC) GL06' => 'AIC',
            'Assistant Inspector' => 'AIC',
            'Customs Assistant I (CA I) GL05' => 'CA I',
            'Customs Assistant I' => 'CA I',
            'Customs Assistant II (CA II) GL04' => 'CA II',
            'Customs Assistant II' => 'CA II',
            'Customs Assistant III (CA III) GL03' => 'CA III',
            'Customs Assistant III' => 'CA III',
            'Customs Assistant' => 'CA I', // Default to CA I if ambiguous
        ];

        // Check exact match first
        if (isset($rankMapping[$rank])) {
            return $rankMapping[$rank];
        }

        // Try partial matching (case-insensitive)
        foreach ($rankMapping as $fullName => $abbr) {
            if (stripos($rank, $fullName) !== false || stripos($fullName, $rank) !== false) {
                return $abbr;
            }
        }

        // If no match found, try to extract abbreviation from parentheses
        if (preg_match('/\(([A-Z\s]+)\)/', $rank, $matches)) {
            $abbr = trim($matches[1]);
            if (in_array($abbr, $standardRanks)) {
                return $abbr;
            }
        }

        // Return original rank if no normalization possible
        return $rank;
    }
}


