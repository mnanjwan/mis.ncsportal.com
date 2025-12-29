<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Query;
use App\Models\Officer;

class AreaControllerQueryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Area Controller');
    }

    /**
     * Display list of accepted queries for officers in Area Controller's command
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get Area Controller's command from their role
        $areaControllerRole = $user->roles()
            ->where('name', 'Area Controller')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $areaControllerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'Command not found for Area Controller role.');
        }

        // Get officers in this command
        $officerIds = Officer::where('present_station', $commandId)
            ->pluck('id')
            ->toArray();

        // Get accepted queries for officers in this command
        $query = Query::whereIn('officer_id', $officerIds)
            ->where('status', 'ACCEPTED')
            ->with(['officer', 'issuedBy'])
            ->orderBy('reviewed_at', 'desc');

        // Filter by officer if provided
        if ($request->has('officer_id') && $request->officer_id) {
            $query->where('officer_id', $request->officer_id);
        }

        $queries = $query->paginate(20);

        // Get officers for filter dropdown
        $officers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->orderBy('surname')
            ->orderBy('initials')
            ->get();

        return view('dashboards.area-controller.queries.index', compact('queries', 'officers'));
    }

    /**
     * Show query details
     */
    public function show($id)
    {
        $user = auth()->user();
        
        // Get Area Controller's command
        $areaControllerRole = $user->roles()
            ->where('name', 'Area Controller')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $areaControllerRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'Command not found.');
        }

        $query = Query::with(['officer', 'issuedBy'])
            ->where('status', 'ACCEPTED')
            ->findOrFail($id);

        // Verify query belongs to officer in Area Controller's command
        if ($query->officer->present_station != $commandId) {
            return redirect()->route('area-controller.queries.index')
                ->with('error', 'Query not found in your command.');
        }

        return view('dashboards.area-controller.queries.show', compact('query'));
    }
}

