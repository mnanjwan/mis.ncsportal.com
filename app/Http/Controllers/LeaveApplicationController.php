<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\WorkingDayService;
use App\Services\PassService;

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
        ], [
            'leave_type_id.required' => 'Please select a leave type.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.after' => 'The end date must be after the start date.',
            'expected_date_of_delivery.required_if' => 'Expected date of delivery is required for Maternity Leave.',
        ]);

        // Check for overlapping leave applications
        $overlappingLeave = LeaveApplication::where('officer_id', $officer->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })->first();

        if ($overlappingLeave) {
            $start = \Carbon\Carbon::parse($overlappingLeave->start_date)->format('d/m/Y');
            $end = \Carbon\Carbon::parse($overlappingLeave->end_date)->format('d/m/Y');
            return redirect()->back()->with('error', "You already have a {$overlappingLeave->status} leave application overlapping with this period ({$start} to {$end}).")->withInput();
        }

        // Check for overlapping pass applications
        $overlappingPass = \App\Models\PassApplication::where('officer_id', $officer->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })->first();

        if ($overlappingPass) {
            $start = \Carbon\Carbon::parse($overlappingPass->start_date)->format('d/m/Y');
            $end = \Carbon\Carbon::parse($overlappingPass->end_date)->format('d/m/Y');
            return redirect()->back()->with('error', "You already have a {$overlappingPass->status} pass application overlapping with this period ({$start} to {$end}).")->withInput();
        }

        try {
            DB::beginTransaction();

            $leaveType = LeaveType::findOrFail($request->leave_type_id);

            // Restrict officers from applying preretirement leave
            // Preretirement leave is automatic and CGC-managed only
            if ($leaveType->code === 'PRE_RETIREMENT_LEAVE') {
                return redirect()->back()->with('error', 'Preretirement leave cannot be applied by officers. It is automatically placed 3 months before retirement and managed by CGC only.')->withInput();
            }

            // Calculate working days within the selected range (for validation if needed)
            $workingDayService = app(WorkingDayService::class);
            $workingDaysInRange = $workingDayService->workingDaysBetween($request->start_date, $request->end_date);

            // Calculate Expiry Date (Resume Duty Date)
            // The user's selection (calendar days) is treated as their working day entitlement.
            // This ensures non-working days in the range are added back to the end.
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $calendarDaysChosen = $startDate->diffInDays($endDate) + 1;

            // The officer gets $calendarDaysChosen of actual working days starting from $start_date
            $calculatedEndDate = $workingDayService->calculateEndDate($request->start_date, $calendarDaysChosen);
            $resumeDate = $workingDayService->calculateResumeDate($calculatedEndDate);

            // Enforce Grade Level limits for Annual Leave
            if ($leaveType->code === 'ANNUAL_LEAVE') {
                $passService = app(PassService::class);
                $maxAllowed = $passService->getPassMaxWorkingDaysForGradeLevel($officer->salary_grade_level);
                
                // Note: We use $calendarDaysChosen here because that's what the user "intended" to take as a block
                // OR we could use $numberOfDays. But the user said "days will not be short".
                // Let's use $calendarDaysChosen as the "intended working days".
                if ($calendarDaysChosen > $maxAllowed) {
                    return redirect()->back()->with('error', "Your Annual Leave entitlement is {$maxAllowed} working days based on your Grade Level. Your current selection effectively requests {$calendarDaysChosen} working days.")->withInput();
                }

                // Implement 6-Month Cooling Period
                $lastLeave = LeaveApplication::where('officer_id', $officer->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'APPROVED')
                    ->whereYear('start_date', now()->year)
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($lastLeave) {
                    $lastEndDate = \Carbon\Carbon::parse($lastLeave->end_date);
                    $newStartDate = \Carbon\Carbon::parse($request->start_date);
                    $monthsSinceLastLeave = $lastEndDate->diffInMonths($newStartDate);
                    if ($monthsSinceLastLeave < 6) {
                        return redirect()->back()->with('error', "The 6-Month Cooling Period rule applies. It has only been {$monthsSinceLastLeave} month(s) since your last Annual Leave. You must wait at least 6 months.")->withInput();
                    }
                }
            }

            // Validate other leave type specific rules
            if ($leaveType->max_duration_days && $calendarDaysChosen > $leaveType->max_duration_days && $leaveType->code !== 'ANNUAL_LEAVE') {
                return redirect()->back()->with('error', "Maximum duration for this leave type is {$leaveType->max_duration_days} days")->withInput();
            }

            $application = LeaveApplication::create([
                'officer_id' => $officer->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,  // Original user-selected end date (stays fixed)
                'expiry_date' => $resumeDate,       // Actual resumption date (accounts for non-working days)
                'number_of_days' => $calendarDaysChosen, // The full count of working days the officer gets
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
        
        // Check if user is 2iC Unit Head - they can view all minuted applications
        if ($user->hasRole('2iC Unit Head')) {
            $application->load(['officer.presentStation', 'leaveType']);
            $application->refresh();
            return view('dashboards.dc-admin.leave-show', compact('application'));
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
            
            // Notify officer and 2iC Unit Heads about minuting
            $notificationService = app(NotificationService::class);
            $notificationService->notifyLeaveApplicationMinuted($application);
            $notificationService->notifyLeaveApplicationMinutedToDcAdmin($application);
            
            return redirect()->route('staff-officer.leave-applications.show', $id)
                ->with('success', 'Application has been minuted to 2iC Unit Head for approval.');
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
        
        // Check if user is 2iC Unit Head
        if (!$user->hasRole('2iC Unit Head')) {
            abort(403, 'Only 2iC Unit Head can approve applications');
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
        
        $isStaffOfficer = $user->hasRole('Staff Officer');
        $is2iC = $user->hasRole('2iC Unit Head');

        if (!$isStaffOfficer && !$is2iC) {
            abort(403, 'Only Staff Officers or 2iC Unit Head can reject applications');
        }
        
        if ($isStaffOfficer) {
            // Verify application is from Staff Officer's command
            $staffOfficerRole = $user->roles()
                ->where('name', 'Staff Officer')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $staffOfficerRole?->pivot->command_id ?? null;
            if ($commandId && $application->officer->present_station != $commandId) {
                return redirect()->back()->with('error', 'You can only reject applications from your command.');
            }
        } else {
            // 2iC Unit Head - Check if application has been minuted
            if (!$application->minuted_at) {
                return redirect()->back()
                    ->with('error', 'This application has not been minuted yet.');
            }
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
            
            $redirectRoute = $is2iC ? 'dc-admin.leave-pass' : 'staff-officer.leave-pass';
            return redirect()->route($redirectRoute, ['type' => 'leave'])
                ->with('success', 'Leave application rejected.');
        } catch (\Exception $e) {
            \Log::error('Failed to reject leave application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject application: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        // Redirect to the official print format
        return redirect()->route('print.leave-document', $id);
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


