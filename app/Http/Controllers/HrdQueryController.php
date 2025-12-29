<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Query;
use App\Models\Officer;

class HrdQueryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    /**
     * Display list of all accepted queries (HRD sees all)
     */
    public function index(Request $request)
    {
        // Get accepted queries for all officers (HRD sees all)
        $query = Query::where('status', 'ACCEPTED')
            ->with(['officer.presentStation', 'issuedBy'])
            ->orderBy('reviewed_at', 'desc');

        // Filter by command if provided
        if ($request->has('command_id') && $request->command_id) {
            $officerIds = Officer::where('present_station', $request->command_id)
                ->pluck('id')
                ->toArray();
            $query->whereIn('officer_id', $officerIds);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('officer', function($officerQuery) use ($search) {
                    $officerQuery->where('initials', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('service_number', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('response', 'like', "%{$search}%")
                ->orWhereHas('issuedBy', function($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('issuedBy.officer', function($officerQuery) use ($search) {
                    $officerQuery->where('initials', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%");
                });
            });
        }

        $queries = $query->paginate(20)->withQueryString();

        // Get commands for filter dropdown
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboards.hrd.queries.index', compact('queries', 'commands'));
    }

    /**
     * Show query details
     */
    public function show($id)
    {
        $query = Query::with(['officer.presentStation', 'issuedBy'])
            ->where('status', 'ACCEPTED')
            ->findOrFail($id);

        return view('dashboards.hrd.queries.show', compact('query'));
    }
}

