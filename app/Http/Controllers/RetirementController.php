<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetirementList;
use App\Services\RetirementService;
use Carbon\Carbon;
use App\Models\Officer;

class RetirementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD')->except(['index', 'show', 'myRetirement']);
    }

    /**
     * Show officer's retirement information
     */
    public function myRetirement()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $retirementDate = $officer->calculateRetirementDate();
        $retirementType = $officer->getRetirementType();
        $alertDate = $officer->getAlertDate();
        $daysUntilRetirement = $officer->getDaysUntilRetirement();
        $isApproachingRetirement = $officer->isApproachingRetirement();
        $retirementAlert = $officer->retirementAlert;

        return view('dashboards.officer.retirement', compact(
            'officer',
            'retirementDate',
            'retirementType',
            'alertDate',
            'daysUntilRetirement',
            'isApproachingRetirement',
            'retirementAlert'
        ));
    }

    public function index(Request $request)
    {
        $query = RetirementList::withCount('items as officers_count');

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
        
        return view('dashboards.hrd.retirement-list', compact('lists'));
    }

    public function show($id)
    {
        $list = RetirementList::with(['items.officer', 'generatedBy'])
            ->findOrFail($id);
        return view('dashboards.hrd.retirement-list-show', compact('list'));
    }

    public function destroy($id)
    {
        try {
            $list = RetirementList::withCount('items')->findOrFail($id);
            
            // Only allow deletion if list has no items
            if ($list->items_count > 0) {
                return redirect()->route('hrd.retirement-list')
                    ->with('error', 'Cannot delete retirement list with officers. Please remove all officers first.');
            }
            
            $list->delete();
            
            return redirect()->route('hrd.retirement-list')
                ->with('success', 'Retirement list deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('hrd.retirement-list')
                ->with('error', 'Failed to delete retirement list: ' . $e->getMessage());
        }
    }

    public function generateList()
    {
        return view('forms.retirement.generate-list');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            // Generate retirement list based on criteria
            $list = RetirementList::create([
                'year' => $validated['year'],
                'generated_by' => auth()->id(),
                'status' => 'DRAFT',
            ]);

            // Populate list items - find officers who will reach retirement criteria in the specified year
            // According to spec: 60 years of age OR 35 years of service, whichever comes first
            $targetYear = $validated['year'];
            $retirementAge = 60;
            $yearsOfService = 35;
            
            // Calculate dates for retirement criteria
            $retirementDateEnd = Carbon::create($targetYear, 12, 31)->endOfDay();
            $birthDateThreshold = $retirementDateEnd->copy()->subYears($retirementAge)->startOfDay(); // Born on or before this date = 60 by end of year
            $serviceDateThreshold = $retirementDateEnd->copy()->subYears($yearsOfService)->startOfDay(); // Started service on or before this date = 35 years by end of year
            
            // Find officers who meet retirement criteria:
            // 1. Will be 60 years old by end of target year, OR
            // 2. Will complete 35 years of service by end of target year
            $eligibleOfficers = \App\Models\Officer::where('is_active', true)
                ->where('is_deceased', false)
                ->whereNotNull('date_of_birth')
                ->where(function($query) use ($birthDateThreshold, $serviceDateThreshold) {
                    // Age-based retirement: date_of_birth <= (target year - 60)
                    $query->where('date_of_birth', '<=', $birthDateThreshold)
                        // OR service-based retirement: date_of_first_appointment <= (target year - 35)
                        ->orWhere(function($q) use ($serviceDateThreshold) {
                            $q->whereNotNull('date_of_first_appointment')
                              ->where('date_of_first_appointment', '<=', $serviceDateThreshold);
                        });
                })
                ->get();
            
            $serialNumber = 1;
            foreach ($eligibleOfficers as $officer) {
                // Calculate when officer will reach each threshold
                $ageRetirementDate = $officer->date_of_birth ? 
                    $officer->date_of_birth->copy()->addYears($retirementAge) : null;
                $serviceRetirementDate = $officer->date_of_first_appointment ? 
                    $officer->date_of_first_appointment->copy()->addYears($yearsOfService) : null;
                
                // Determine which condition applies (whichever comes first)
                // Note: Database uses 'SVC' not 'SERVICE' (per migration enum)
                $retirementCondition = 'AGE';
                $retirementDate = $ageRetirementDate;
                
                if ($serviceRetirementDate && $ageRetirementDate) {
                    // Use whichever comes first
                    if ($serviceRetirementDate->lte($ageRetirementDate)) {
                        $retirementCondition = 'SVC'; // Database enum uses 'SVC'
                        $retirementDate = $serviceRetirementDate;
                    }
                } elseif ($serviceRetirementDate && !$ageRetirementDate) {
                    $retirementCondition = 'SVC'; // Database enum uses 'SVC'
                    $retirementDate = $serviceRetirementDate;
                }
                
                // Ensure retirement date is within the target year (or set to end of year if later)
                if ($retirementDate && $retirementDate->year > $targetYear) {
                    $retirementDate = Carbon::create($targetYear, 12, 31);
                } elseif (!$retirementDate) {
                    $retirementDate = Carbon::create($targetYear, 12, 31);
                }
                
                // Ensure retirement date is not in the past (shouldn't happen for future years, but safety check)
                if ($retirementDate->lt(now())) {
                    $retirementDate = Carbon::create($targetYear, 12, 31);
                }
                
                $preRetirementLeave = $retirementDate->copy()->subMonths(3); // 3 months before retirement
                
                \App\Models\RetirementListItem::create([
                    'retirement_list_id' => $list->id,
                    'officer_id' => $officer->id,
                    'serial_number' => $serialNumber++,
                    'rank' => $officer->substantive_rank ?? 'N/A',
                    'initials' => $officer->initials ?? '',
                    'name' => $officer->surname ?? '',
                    'retirement_condition' => $retirementCondition,
                    'date_of_birth' => $officer->date_of_birth,
                    'date_of_first_appointment' => $officer->date_of_first_appointment ?? now(),
                    'date_of_pre_retirement_leave' => $preRetirementLeave,
                    'retirement_date' => $retirementDate,
                    'notified' => false,
                ]);
            }
            
            $officersCount = $eligibleOfficers->count();
            
            if ($officersCount === 0) {
                // Delete the empty list
                $list->delete();
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', "No officers found who will reach retirement criteria (60 years of age OR 35 years of service) by the end of {$targetYear}. 
                    
                    <strong>Tip:</strong> Try using a future year. Based on current officer data, retirement years range from 2036-2057. 
                    
                    The system checks for officers who will:
                    - Reach age 60 by end of {$targetYear}, OR
                    - Complete 35 years of service by end of {$targetYear}");
            }
            
            // Activate pre-retirement status for officers whose pre-retirement date has arrived
            try {
                $retirementService = new RetirementService();
                $retirementService->activatePreRetirementStatus($list);
            } catch (\Exception $e) {
                // Log error but don't fail the list generation
                \Log::error("Failed to activate pre-retirement status: " . $e->getMessage());
            }
            
            return redirect()->route('hrd.retirement-list')
                ->with('success', "Retirement list generated successfully with {$officersCount} officer(s)!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to generate retirement list: ' . $e->getMessage());
        }
    }
}


