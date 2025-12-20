<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PassApplication;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class PassApplicationController extends Controller
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

        $passes = PassApplication::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboards.officer.pass-applications-list', compact('passes'));
    }

    public function create()
    {
        return view('forms.pass.apply');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->back()->with('error', 'Officer record not found. Please contact HR.');
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $numberOfDays = $startDate->diffInDays($endDate) + 1;

            // Validate maximum 5 days
            if ($numberOfDays > 5) {
                return redirect()->back()->with('error', 'Pass cannot exceed 5 days')->withInput();
            }

            // Check if annual leave is exhausted
            /*
            $annualLeaveType = LeaveType::where('code', 'ANNUAL_LEAVE')->first();
            if ($annualLeaveType) {
                $annualLeaveCount = LeaveApplication::where('officer_id', $officer->id)
                    ->where('leave_type_id', $annualLeaveType->id)
                    ->whereYear('start_date', now()->year)
                    ->where('status', 'APPROVED')
                    ->count();

                if ($annualLeaveCount < 2) {
                    return redirect()->back()->with('error', 'Annual leave must be exhausted before applying for pass')->withInput();
                }
            }
            */

            // Check maximum 2 passes per year
            $passCount = PassApplication::where('officer_id', $officer->id)
                ->whereYear('start_date', now()->year)
                ->where('status', 'APPROVED')
                ->count();

            if ($passCount >= 2) {
                return redirect()->back()->with('error', 'Maximum 2 passes per year allowed')->withInput();
            }

            $application = PassApplication::create([
                'officer_id' => $officer->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'number_of_days' => $numberOfDays,
                'reason' => $request->reason,
                'status' => 'PENDING',
                'submitted_at' => now(),
            ]);

            DB::commit();

            // Notify Staff Officers about the new pass application
            $notificationService = app(NotificationService::class);
            $notificationService->notifyPassApplicationSubmitted($application);

            return redirect()->route('officer.pass-applications')->with('success', 'Pass application submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit pass application: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $officer = $user->officer;
        // Use fresh() to ensure we get the latest data from database
        $application = PassApplication::with('officer')->findOrFail($id);
        
        // Check if user is Area Controller - they can view all applications
        if ($user->hasRole('Area Controller')) {
            // Load officer with presentStation relationship
            $application->load(['officer.presentStation']);
            // Refresh the application to ensure we have the latest data
            $application->refresh();
            
            return view('dashboards.area-controller.pass-show', compact('application'));
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
            
            return view('dashboards.staff-officer.pass-show', compact('application'));
        }
        
        // For officers, check if it's their own application
        if ($officer) {
            if ($application->officer_id !== $officer->id) {
                abort(403, 'Unauthorized access');
            }
            
            return view('dashboards.officer.pass-show', compact('application'));
        }
        
        abort(403, 'Unauthorized access');
    }

    public function minute($id)
    {
        $user = auth()->user();
        $application = PassApplication::with('officer')->findOrFail($id);
        
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
            return redirect()->route('staff-officer.pass-applications.show', $id)
                ->with('error', 'You can only minute applications from your command.');
        }
        
        // Check if already minuted
        if ($application->minuted_at) {
            return redirect()->route('staff-officer.pass-applications.show', $id)
                ->with('error', 'This application has already been minuted.');
        }
        
        // Check if application is in PENDING status
        if ($application->status !== 'PENDING') {
            return redirect()->route('staff-officer.pass-applications.show', $id)
                ->with('error', 'Only PENDING applications can be minuted.');
        }
        
        try {
            // Update minuted_at
            $application->minuted_at = now();
            $application->save();
            
            // Refresh the model to ensure we have the latest data
            $application->refresh();
            
            return redirect()->route('staff-officer.pass-applications.show', $id)
                ->with('success', 'Application has been minuted to DC Admin for approval.');
        } catch (\Exception $e) {
            \Log::error('Failed to minute pass application: ' . $e->getMessage());
            return redirect()->route('staff-officer.pass-applications.show', $id)
                ->with('error', 'Failed to minute application: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $user = auth()->user();
        $application = PassApplication::with('officer')->findOrFail($id);
        
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
            $notificationService->notifyPassApplicationApproved($application);
            
            return redirect()->route('dc-admin.leave-pass', ['type' => 'pass'])
                ->with('success', 'Pass application approved successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to approve pass application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }
    
    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $application = PassApplication::with('officer')->findOrFail($id);
        
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
            $notificationService->notifyPassApplicationRejected($application, $request->rejection_reason);
            
            return redirect()->route('dc-admin.leave-pass', ['type' => 'pass'])
                ->with('success', 'Pass application rejected.');
        } catch (\Exception $e) {
            \Log::error('Failed to reject pass application: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reject application: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        return view('forms.pass.print', compact('id'));
    }

    // Staff Officer - List pass applications for their command
    public function staffOfficerIndex(Request $request)
    {
        $user = auth()->user();
        
        // Get Staff Officer's command
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        
        // Get pass applications for command officers
        $query = PassApplication::whereHas('officer', function($q) use ($commandId) {
                if ($commandId) {
                    $q->where('present_station', $commandId);
                }
            })
            ->with('officer')
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $passApplications = $query->paginate(20)->withQueryString();
        
        return $passApplications;
    }

    // Area Controller - List all pass applications
    public function areaControllerIndex(Request $request)
    {
        $query = PassApplication::with('officer.presentStation')
            ->orderBy('created_at', 'desc');
        
        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $passApplications = $query->paginate(20)->withQueryString();
        
        return $passApplications;
    }
}


