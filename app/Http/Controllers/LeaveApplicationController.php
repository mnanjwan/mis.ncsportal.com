<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class LeaveApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')->with('error', 'Officer record not found.');
        }

        $applications = LeaveApplication::where('officer_id', $officer->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboards.officer.leave-applications-list', compact('applications'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::all();
        return view('forms.leave.apply', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->back()->with('error', 'Officer record not found. Please contact HR.');
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
            'expected_date_of_delivery' => 'required_if:leave_type_id,6|nullable|date', // Maternity leave
            'medical_certificate' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::findOrFail($request->leave_type_id);

            // Restrict officers from applying preretirement leave
            // Preretirement leave is automatic and CGC-managed only
            if ($leaveType->code === 'PRE_RETIREMENT_LEAVE') {
                return redirect()->back()->with('error', 'Preretirement leave cannot be applied by officers. It is automatically placed 3 months before retirement and managed by CGC only.')->withInput();
            }

            // Calculate number of days
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $numberOfDays = $startDate->diffInDays($endDate) + 1;

            // Validate leave type specific rules
            if ($leaveType->max_duration_days && $numberOfDays > $leaveType->max_duration_days) {
                return redirect()->back()->with('error', "Maximum duration for this leave type is {$leaveType->max_duration_days} days")->withInput();
            }

            // Check annual leave limits
            if ($leaveType->code === 'ANNUAL_LEAVE') {
                $annualLeaveCount = LeaveApplication::where('officer_id', $officer->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->whereYear('start_date', now()->year)
                    ->count();

                if ($annualLeaveCount >= ($leaveType->max_occurrences_per_year ?? 2)) {
                    return redirect()->back()->with('error', 'Maximum annual leave applications reached for this year')->withInput();
                }
            }

            $application = LeaveApplication::create([
                'officer_id' => $officer->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'number_of_days' => $numberOfDays,
                'reason' => $request->reason,
                'expected_date_of_delivery' => $request->expected_date_of_delivery,
                'status' => 'PENDING',
                'submitted_at' => now(),
            ]);

            // Handle medical certificate upload if provided
            if ($request->hasFile('medical_certificate')) {
                $path = $request->file('medical_certificate')->store('certificates', 'public');
                $application->medical_certificate_url = $path;
                $application->save();
            }

            DB::commit();

            // Notify Staff Officers about the new leave application
            $notificationService = app(NotificationService::class);
            $notificationService->notifyLeaveApplicationSubmitted($application);

            return redirect()->route('officer.leave-applications')->with('success', 'Leave application submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit leave application: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $officer = $user->officer;
        // Use fresh() to ensure we get the latest data from database
        $application = LeaveApplication::with(['leaveType', 'officer'])->findOrFail($id);
        
        // Check if user is Area Controller - they can view all applications
        if ($user->hasRole('Area Controller')) {
            // Load officer with presentStation relationship
            $application->load(['officer.presentStation', 'leaveType']);
            // Refresh the application to ensure we have the latest data
            $application->refresh();
            
            return view('dashboards.area-controller.leave-show', compact('application'));
        }
        
        // Check if user is staff officer (has access to all applications in their command)
        if ($user->hasRole('Staff Officer')) {
            // Verify application is from Staff Officer's command
            $staffOfficerRole = $user->roles()
                ->where('name', 'Staff Officer')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $staffOfficerRole?->pivot->command_id ?? null;
            if ($commandId && $application->officer->present_station != $commandId) {
                abort(403, 'You can only view applications from your command');
            }
            
            // Refresh the application to ensure we have the latest data
            $application->refresh();
            
            return view('dashboards.staff-officer.leave-show', compact('application'));
        }
        
        // For officers, check if it's their own application
        if ($officer) {
            if ($application->officer_id !== $officer->id) {
                abort(403, 'Unauthorized access');
            }
            
            return view('dashboards.officer.leave-show', compact('application'));
        }
        
        abort(403, 'Unauthorized access');
    }

    public function minute($id)
    {
        $user = auth()->user();
        $application = LeaveApplication::with(['officer', 'leaveType'])->findOrFail($id);
        
        // Check if user is Staff Officer
        if (!$user->hasRole('Staff Officer')) {
            abort(403, 'Only Staff Officers can minute applications');
        }
        
        // Verify application is from Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        if ($commandId && $application->officer->present_station != $commandId) {
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('error', 'You can only minute applications from your command.');
        }
        
        // Check if already minuted
        if ($application->minuted_at) {
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('error', 'This application has already been minuted.');
        }
        
        // Check if application is in PENDING status
        if ($application->status !== 'PENDING') {
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('error', 'Only PENDING applications can be minuted.');
        }
        
        try {
            // Update minuted_at
            $application->minuted_at = now();
            $application->save();
            
            // Refresh the model to ensure we have the latest data
            $application->refresh();
            
            // Notify officer and DC Admins about minuting
            $notificationService = app(NotificationService::class);
            $notificationService->notifyLeaveApplicationMinuted($application);
            $notificationService->notifyLeaveApplicationMinutedToDcAdmin($application);
            
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('success', 'Application has been minuted to DC Admin for approval.');
        } catch (\Exception $e) {
            Log::error('Failed to minute leave application: ' . $e->getMessage());
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('error', 'Failed to minute application: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $user = auth()->user();
        $application = LeaveApplication::with(['officer', 'leaveType'])->findOrFail($id);
        
        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can approve applications');
        }
        
        // Check if application has been minuted
        if (!$application->minuted_at) {
            return redirect()->back()
                ->with('error', 'This application has not been minuted yet.');
        }
        
        // Check if application is in PENDING status
        if ($application->status !== 'PENDING') {
            return redirect()->back()
                ->with('error', 'Only PENDING applications can be approved.');
        }
        
        try {
            $application->status = 'APPROVED';
            $application->approved_at = now();
            $application->save();
            
            // Notify officer about approval
            $notificationService = app(NotificationService::class);
            $notificationService->notifyLeaveApplicationApproved($application);
            
            return redirect()->route('dc-admin.leave-pass', ['type' => 'leave'])
                ->with('success', 'Leave application approved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to approve leave application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $application = LeaveApplication::with(['officer', 'leaveType'])->findOrFail($id);
        
        // Check if user is DC Admin
        if (!$user->hasRole('DC Admin')) {
            abort(403, 'Only DC Admin can reject applications');
        }
        
        // Check if application has been minuted
        if (!$application->minuted_at) {
            return redirect()->back()
                ->with('error', 'This application has not been minuted yet.');
        }
        
        // Check if application is in PENDING status
        if ($application->status !== 'PENDING') {
            return redirect()->back()
                ->with('error', 'Only PENDING applications can be rejected.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        try {
            $application->status = 'REJECTED';
            $application->rejected_at = now();
            $application->rejection_reason = $request->rejection_reason;
            $application->save();
            
            // Notify officer about rejection
            $notificationService = app(NotificationService::class);
            $notificationService->notifyLeaveApplicationRejected($application, $request->rejection_reason);
            
            return redirect()->route('dc-admin.leave-pass', ['type' => 'leave'])
                ->with('success', 'Leave application rejected.');
        } catch (\Exception $e) {
            Log::error('Failed to reject leave application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject application: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        return view('forms.leave.print', compact('id'));
    }

    // Staff Officer - List leave applications for their command
    public function staffOfficerIndex(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? \App\Models\Command::find($commandId) : null;
        
        // Get leave applications for command officers
        $query = LeaveApplication::whereHas('officer', function($q) use ($commandId) {
                if ($commandId) {
                    $q->where('present_station', $commandId);
                }
            })
            ->with(['officer', 'leaveType'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $leaveApplications = $query->paginate(20)->withQueryString();
        
        return $leaveApplications;
    }

    // Area Controller - List all leave applications
    public function areaControllerIndex(Request $request)
    {
        $query = LeaveApplication::with(['officer.presentStation', 'leaveType'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $leaveApplications = $query->paginate(20)->withQueryString();
        
        return $leaveApplications;
    }
}


