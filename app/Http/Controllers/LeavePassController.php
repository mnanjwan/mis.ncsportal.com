<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Command;
use App\Models\LeaveApplication;
use App\Models\PassApplication;

class LeavePassController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Staff Officer - Leave & Pass Management
     */
    public function staffOfficerIndex(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? Command::find($commandId) : null;
        
        $leaveController = app(LeaveApplicationController::class);
        $passController = app(PassApplicationController::class);
        
        // Filter by type (leave or pass)
        $type = $request->get('type', 'leave');
        
        if ($type === 'pass') {
            $leaveApplications = collect();
            $passApplications = $passController->staffOfficerIndex($request);
        } else {
            $leaveApplications = $leaveController->staffOfficerIndex($request);
            $passApplications = collect();
        }
        
        return view('dashboards.staff-officer.leave-pass', compact(
            'command',
            'leaveApplications',
            'passApplications',
            'type'
        ));
    }

    /**
     * Area Controller - Leave & Pass Management
     */
    public function areaControllerIndex(Request $request)
    {
        $leaveController = app(LeaveApplicationController::class);
        $passController = app(PassApplicationController::class);
        
        $type = $request->get('type', 'leave');
        $status = $request->get('status', '');
        
        if ($type === 'leave') {
            $leaveApplications = $leaveController->areaControllerIndex($request);
            $passApplications = collect();
        } else {
            $passApplications = $passController->areaControllerIndex($request);
            $leaveApplications = collect();
        }
        
        return view('dashboards.area-controller.leave-pass', compact(
            'leaveApplications',
            'passApplications',
            'type',
            'status'
        ));
    }
}

