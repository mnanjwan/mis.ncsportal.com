<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Query;
use App\Models\Officer;
use App\Services\NotificationService;

class OfficerQueryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('onboarding.complete');
    }

    /**
     * Display list of queries for the logged-in officer
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $query = Query::where('officer_id', $officer->id)
            ->with(['issuedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $queries = $query->paginate(20)->withQueryString();

        return view('dashboards.officer.queries.index', compact('queries'));
    }

    /**
     * Show query details and response form
     */
    public function show($id)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $query = Query::with(['issuedBy'])
            ->where('officer_id', $officer->id)
            ->findOrFail($id);

        return view('dashboards.officer.queries.show', compact('query'));
    }

    /**
     * Store response to query
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|string|min:10',
        ]);

        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $query = Query::where('officer_id', $officer->id)
            ->findOrFail($id);

        // Verify query is pending response
        if (!$query->isPendingResponse()) {
            return redirect()->back()->with('error', 'Query is not pending response.');
        }

        // Check if query has expired
        if ($query->isOverdue()) {
            return redirect()->back()->with('error', 'The response deadline has passed. This query can no longer be responded to.');
        }

        try {
            DB::beginTransaction();

            $query->update([
                'response' => $request->response,
                'status' => 'PENDING_REVIEW',
                'responded_at' => now(),
            ]);

            // Send notification to Staff Officer who issued the query
            if ($query->issuedBy) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyQueryResponseSubmitted($query);
            }

            DB::commit();

            Log::info('Query response submitted', [
                'query_id' => $query->id,
                'officer_id' => $officer->id,
            ]);

            return redirect()->route('officer.queries.show', $query->id)
                ->with('success', 'Response submitted successfully. Staff Officer has been notified.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit query response', [
                'error' => $e->getMessage(),
                'query_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Failed to submit response. Please try again.');
        }
    }
}
