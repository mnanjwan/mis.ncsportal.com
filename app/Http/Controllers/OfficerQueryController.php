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

        // First, automatically expire any queries that have passed their deadline
        $expiredQueries = Query::where('officer_id', $officer->id)
            ->where('status', 'PENDING_RESPONSE')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '<=', now())
            ->get();

        if ($expiredQueries->isNotEmpty()) {
            $notificationService = app(NotificationService::class);
            
            foreach ($expiredQueries as $expiredQuery) {
                try {
                    DB::beginTransaction();

                    // Update query status to ACCEPTED
                    $expiredQuery->update([
                        'status' => 'ACCEPTED',
                        'reviewed_at' => now(),
                    ]);

                    // Send notification to officer about automatic expiration
                    if ($expiredQuery->officer && $expiredQuery->officer->user) {
                        $notificationService->notifyQueryExpired($expiredQuery);
                    }

                    DB::commit();

                    Log::info('Query expired automatically on index view', [
                        'query_id' => $expiredQuery->id,
                        'officer_id' => $expiredQuery->officer_id,
                        'deadline' => $expiredQuery->response_deadline,
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to expire query on index view', [
                        'query_id' => $expiredQuery->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
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

        // Automatically expire query if deadline has passed (real-time check)
        if ($query->status === 'PENDING_RESPONSE' 
            && $query->response_deadline 
            && now()->greaterThanOrEqualTo($query->response_deadline)) {
            
            try {
                DB::beginTransaction();

                // Update query status to ACCEPTED (automatically added to disciplinary record)
                $query->update([
                    'status' => 'ACCEPTED',
                    'reviewed_at' => now(),
                ]);

                // Send notification to officer about automatic expiration
                if ($query->officer && $query->officer->user) {
                    $notificationService = app(NotificationService::class);
                    $notificationService->notifyQueryExpired($query);
                }

                DB::commit();

                Log::info('Query expired automatically on view', [
                    'query_id' => $query->id,
                    'officer_id' => $query->officer_id,
                    'deadline' => $query->response_deadline,
                ]);

                // Reload the query to get updated status
                $query->refresh();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to expire query on view', [
                    'query_id' => $query->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

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

        // Verify query can accept response (checks status and deadline)
        if (!$query->canAcceptResponse()) {
            if (!$query->isPendingResponse()) {
                return redirect()->back()->with('error', 'Query is not pending response.');
            }
            if ($query->response_deadline && now()->greaterThanOrEqualTo($query->response_deadline)) {
                return redirect()->back()->with('error', 'The response deadline has been reached or passed. This query can no longer be responded to.');
            }
            return redirect()->back()->with('error', 'You cannot respond to this query at this time.');
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
