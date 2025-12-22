<?php

namespace App\Http\Controllers;

use App\Models\RetirementListItem;
use App\Models\Officer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CGCPreretirementLeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of officers on preretirement leave
     */
    public function index(Request $request)
    {
        // Check if user has CGC role
        $user = auth()->user();
        if (!$user || !$user->hasRole('CGC')) {
            abort(403, 'Unauthorized access. CGC role required.');
        }

        $query = RetirementListItem::with(['officer', 'retirementList', 'cgcApprovedBy'])
            ->whereNotNull('preretirement_leave_status');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('preretirement_leave_status', $request->status);
        }

        // Search by service number or name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('initials', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('date_of_pre_retirement_leave', 'desc')
            ->paginate(20);

        return view('dashboards.cgc.preretirement-leave.index', compact('items'));
    }

    /**
     * Show officers approaching preretirement leave (within next 3 months)
     */
    public function approaching(Request $request)
    {
        // Check if user has CGC role
        if (!auth()->user()->hasRole('CGC')) {
            abort(403, 'Unauthorized access. CGC role required.');
        }

        $threeMonthsFromNow = now()->addMonths(3);

        $query = RetirementListItem::with(['officer', 'retirementList'])
            ->where('date_of_pre_retirement_leave', '>=', now())
            ->where('date_of_pre_retirement_leave', '<=', $threeMonthsFromNow)
            ->whereNull('preretirement_leave_status'); // Not yet placed

        // Search by service number or name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('initials', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('date_of_pre_retirement_leave', 'asc')
            ->paginate(20);

        return view('dashboards.cgc.preretirement-leave.approaching', compact('items'));
    }

    /**
     * Show details of a specific preretirement leave item
     */
    public function show($id)
    {
        // Check if user has CGC role
        if (!auth()->user()->hasRole('CGC')) {
            abort(403, 'Unauthorized access. CGC role required.');
        }

        $item = RetirementListItem::with(['officer', 'retirementList', 'cgcApprovedBy'])
            ->findOrFail($id);

        return view('dashboards.cgc.preretirement-leave.show', compact('item'));
    }

    /**
     * Approve officer to work during preretirement period (preretirement leave "in office")
     */
    public function approveInOffice(Request $request, $id)
    {
        // Check if user has CGC role
        if (!auth()->user()->hasRole('CGC')) {
            abort(403, 'Unauthorized access. CGC role required.');
        }

        $request->validate([
            'approval_reason' => 'nullable|string|max:1000',
        ]);

        $item = RetirementListItem::with('officer')->findOrFail($id);

        if (!$item->officer) {
            return redirect()->back()->with('error', 'Officer record not found.');
        }

        DB::beginTransaction();
        try {
            // Update retirement list item
            $item->update([
                'preretirement_leave_status' => 'CGC_APPROVED_IN_OFFICE',
                'cgc_approved_by' => auth()->id(),
                'cgc_approved_at' => now(),
                'cgc_approval_reason' => $request->approval_reason,
            ]);

            // Update officer status
            $item->officer->update([
                'preretirement_leave_status' => 'PRERETIREMENT_LEAVE_IN_OFFICE',
                'preretirement_leave_started_at' => now(),
            ]);

            // Notify officer
            if ($item->officer->user) {
                Notification::create([
                    'user_id' => $item->officer->user->id,
                    'notification_type' => 'PRERETIREMENT_LEAVE_CGC_APPROVED',
                    'title' => 'Preretirement Leave In Office Approved',
                    'message' => "CGC has approved you to continue working during your preretirement period. You will work for the last 3 months before your retirement date ({$item->retirement_date->format('Y-m-d')}).",
                    'data' => [
                        'officer_id' => $item->officer->id,
                        'retirement_list_item_id' => $item->id,
                        'retirement_date' => $item->retirement_date->format('Y-m-d'),
                        'status' => 'CGC_APPROVED_IN_OFFICE',
                    ],
                ]);
            }

            DB::commit();

            Log::info("CGC approved officer for preretirement leave in office", [
                'officer_id' => $item->officer->id,
                'retirement_list_item_id' => $item->id,
                'cgc_user_id' => auth()->id(),
            ]);

            return redirect()->route('cgc.preretirement-leave.index')
                ->with('success', "Officer {$item->officer->service_number} ({$item->officer->full_name}) has been approved to work during preretirement period.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to approve preretirement leave in office: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve officer. Please try again.');
        }
    }

    /**
     * Cancel CGC approval (revert to automatic placement)
     */
    public function cancelApproval($id)
    {
        // Check if user has CGC role
        if (!auth()->user()->hasRole('CGC')) {
            abort(403, 'Unauthorized access. CGC role required.');
        }

        $item = RetirementListItem::with('officer')->findOrFail($id);

        if ($item->preretirement_leave_status !== 'CGC_APPROVED_IN_OFFICE') {
            return redirect()->back()->with('error', 'This item is not approved for preretirement leave in office.');
        }

        DB::beginTransaction();
        try {
            // Revert to automatic placement
            $item->update([
                'preretirement_leave_status' => 'AUTO_PLACED',
                'cgc_approved_by' => null,
                'cgc_approved_at' => null,
                'cgc_approval_reason' => null,
            ]);

            // Update officer status
            if ($item->officer) {
                $item->officer->update([
                    'preretirement_leave_status' => 'ON_PRERETIREMENT_LEAVE',
                ]);
            }

            DB::commit();

            Log::info("CGC cancelled preretirement leave in office approval", [
                'officer_id' => $item->officer->id ?? null,
                'retirement_list_item_id' => $item->id,
                'cgc_user_id' => auth()->id(),
            ]);

            return redirect()->route('cgc.preretirement-leave.index')
                ->with('success', 'CGC approval has been cancelled. Officer reverted to automatic preretirement leave.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to cancel CGC approval: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to cancel approval. Please try again.');
        }
    }
}
