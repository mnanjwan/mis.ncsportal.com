<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Emolument;
use App\Models\Command;
use App\Models\Officer;
use App\Models\EmolumentTimeline;
use App\Models\StaffOrder;
use App\Models\LeaveApplication;
use App\Models\PassApplication;
use App\Models\ManningRequest;
use App\Models\DutyRoster;
use App\Models\OfficerPosting;
use App\Models\AccountChangeRequest;
use App\Models\NextOfKinChangeRequest;
use App\Models\DeceasedOfficer;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Load ONLY ACTIVE roles
        $user->load(['roles' => function($query) {
            $query->wherePivot('is_active', true);
        }]);
        
        // Priority order: HRD > Admin roles > Zone Coordinator > Validator > Assessor > Staff Officer > Officer
        $rolePriorities = [
            'HRD',
            'Board',
            'Accounts',
            'Welfare',
            'Establishment',
            'TRADOC',
            'ICT',
            'Building Unit',
            'Area Controller',
            'DC Admin',
            'Zone Coordinator',
            'Validator',
            'Assessor',
            'Staff Officer',
            'Officer'
        ];
        
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // Find the highest priority role the user has
        $role = 'Officer'; // Default
        foreach ($rolePriorities as $priorityRole) {
            if (in_array($priorityRole, $userRoles)) {
                $role = $priorityRole;
                break;
            }
        }

        return redirect()->route($this->getDashboardRoute($role));
    }


    // HRD Dashboard
    public function hrd()
    {
        // Get statistics
        $totalOfficers = Officer::count();
        $pendingEmoluments = Emolument::where('status', 'RAISED')->count();
        $activeTimeline = EmolumentTimeline::where('is_active', true)->first();
        $staffOrdersCount = StaffOrder::count();
        
        // Get recent officers
        $recentOfficers = Officer::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get emolument status breakdown
        $emolumentStatus = [
            'RAISED' => Emolument::where('status', 'RAISED')->count(),
            'ASSESSED' => Emolument::where('status', 'ASSESSED')->count(),
            'VALIDATED' => Emolument::where('status', 'VALIDATED')->count(),
            'PROCESSED' => Emolument::where('status', 'PROCESSED')->count(),
        ];

        return view('dashboards.hrd.dashboard', compact(
            'totalOfficers',
            'pendingEmoluments',
            'activeTimeline',
            'staffOrdersCount',
            'recentOfficers',
            'emolumentStatus'
        ));
    }

    public function officers()
    {
        return view('dashboards.hrd.officers-list');
    }

    public function emolumentTimeline()
    {
        return view('dashboards.hrd.emolument-timeline');
    }

    public function staffOrders()
    {
        return view('dashboards.hrd.staff-orders');
    }

    public function reports()
    {
        return view('dashboards.hrd.reports');
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:officers,emoluments,leave,pass,promotions,retirements',
            'format' => 'required|in:pdf,excel,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $reportType = $validated['report_type'];
        $format = $validated['format'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        try {
            // Generate report based on type
            switch ($reportType) {
                case 'officers':
                    $data = Officer::with(['presentStation', 'user'])
                        ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                        ->get();
                    $filename = 'officers_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                case 'emoluments':
                    $data = Emolument::with(['officer', 'assessment', 'validation'])
                        ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                        ->get();
                    $filename = 'emoluments_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                case 'leave':
                    $data = \App\Models\LeaveApplication::with(['officer'])
                        ->when($startDate, fn($q) => $q->where('start_date', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('end_date', '<=', $endDate))
                        ->get();
                    $filename = 'leave_applications_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                case 'pass':
                    $data = \App\Models\PassApplication::with(['officer'])
                        ->when($startDate, fn($q) => $q->where('start_date', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('end_date', '<=', $endDate))
                        ->get();
                    $filename = 'pass_applications_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                case 'promotions':
                    $data = \App\Models\PromotionEligibilityList::withCount('items')
                        ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                        ->get();
                    $filename = 'promotions_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                case 'retirements':
                    $data = \App\Models\RetirementList::withCount('items')
                        ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                        ->get();
                    $filename = 'retirements_report_' . date('Y-m-d') . '.' . ($format === 'excel' ? 'csv' : $format);
                    break;

                default:
                    return redirect()->back()->with('error', 'Invalid report type.');
            }

            // Export based on format
            if ($format === 'csv' || $format === 'excel') {
                return $this->exportToCsv($data, $filename, $reportType);
            } else {
                return redirect()->back()->with('error', 'PDF export will be implemented with a PDF library.');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }

    private function exportToCsv($data, $filename, $reportType)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Write headers based on report type
            switch ($reportType) {
                case 'officers':
                    fputcsv($file, ['Service Number', 'Name', 'Rank', 'Command', 'Email', 'Created At']);
                    foreach ($data as $item) {
                        fputcsv($file, [
                            $item->service_number ?? 'N/A',
                            ($item->initials ?? '') . ' ' . ($item->surname ?? ''),
                            $item->substantive_rank ?? 'N/A',
                            $item->presentStation->name ?? 'N/A',
                            $item->user->email ?? 'N/A',
                            $item->created_at->format('Y-m-d'),
                        ]);
                    }
                    break;
                case 'emoluments':
                    fputcsv($file, ['Officer', 'Year', 'Status', 'Assessed At', 'Validated At', 'Processed At']);
                    foreach ($data as $item) {
                        fputcsv($file, [
                            ($item->officer->initials ?? '') . ' ' . ($item->officer->surname ?? ''),
                            $item->year ?? 'N/A',
                            $item->status ?? 'N/A',
                            $item->assessed_at ? $item->assessed_at->format('Y-m-d') : 'N/A',
                            $item->validated_at ? $item->validated_at->format('Y-m-d') : 'N/A',
                            $item->processed_at ? $item->processed_at->format('Y-m-d') : 'N/A',
                        ]);
                    }
                    break;
                default:
                    fputcsv($file, ['ID', 'Created At']);
                    foreach ($data as $item) {
                        fputcsv($file, [$item->id, $item->created_at->format('Y-m-d')]);
                    }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Staff Officer Dashboard
    public function staffOfficer()
    {
        $user = auth()->user();
        
        // Get Staff Officer's command from their role
        $staffOfficerRole = $user->roles()
            ->where('name', 'Staff Officer')
            ->wherePivot('is_active', true)
            ->first();
        
        $commandId = $staffOfficerRole?->pivot->command_id ?? null;
        $command = $commandId ? Command::find($commandId) : null;
        
        // Initialize default values
        $newlyPostedOfficers = collect();
        $pendingLeaveCount = 0;
        $pendingPassCount = 0;
        $manningLevelCount = 0;
        $dutyRosterActive = false;
        $recentLeaveApplications = collect();
        $recentPassApplications = collect();
        
        if ($commandId) {
            // Get newly posted officers (not yet documented)
            // Check for officers with current posting that's not documented, OR recently posted (within 30 days) without posting record
            $newlyPostedOfficers = Officer::where('present_station', $commandId)
                ->where('is_active', true)
                ->where(function($q) {
                    // Has current posting that's not documented
                    $q->whereHas('currentPosting', function($subQ) {
                        $subQ->whereNull('documented_at');
                    })
                    // OR recently posted (within 30 days) and might not have posting record yet
                    ->orWhere(function($subQ) {
                        $subQ->whereNotNull('date_posted_to_station')
                             ->where('date_posted_to_station', '>=', now()->subDays(30))
                             ->whereDoesntHave('currentPosting');
                    });
                })
                ->with(['presentStation', 'user', 'currentPosting'])
                ->orderBy('date_posted_to_station', 'desc')
                ->take(10)
                ->get();
            
            // Get pending leave applications (PENDING status, from command officers)
            $pendingLeaveCount = \App\Models\LeaveApplication::where('status', 'PENDING')
                ->whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                })
                ->count();
            
            // Get pending pass applications (PENDING status, from command officers)
            $pendingPassCount = \App\Models\PassApplication::where('status', 'PENDING')
                ->whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                })
                ->count();
            
            // Get manning level requests count (pending approval)
            $manningLevelCount = \App\Models\ManningRequest::where('command_id', $commandId)
                ->where('status', 'PENDING')
                ->count();
            
            // Check if there's an active duty roster
            $dutyRosterActive = \App\Models\DutyRoster::where('command_id', $commandId)
                ->where('status', 'ACTIVE')
                ->exists();
            
            // Get recent leave applications (last 5, from command officers)
            $recentLeaveApplications = \App\Models\LeaveApplication::whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                })
                ->with(['officer', 'leaveType'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Get recent pass applications (last 5, from command officers)
            $recentPassApplications = \App\Models\PassApplication::whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                })
                ->with('officer')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
        
        return view('dashboards.staff-officer.dashboard', compact(
            'command',
            'newlyPostedOfficers',
            'pendingLeaveCount',
            'pendingPassCount',
            'manningLevelCount',
            'dutyRosterActive',
            'recentLeaveApplications',
            'recentPassApplications'
        ));
    }



    public function roster()
    {
        return view('dashboards.staff-officer.roster');
    }

    // Assessor Dashboard
    public function assessor()
    {
        $user = auth()->user();
        
        // Get Assessor's command from their role
        $assessorRole = $user->roles()->where('name', 'Assessor')->first();
        $commandId = $assessorRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return view('dashboards.assessor.dashboard', [
                'error' => 'No command assigned. Please contact HRD to assign you to a command.',
                'statistics' => [],
                'pendingEmoluments' => collect(),
                'commandOfficers' => collect(),
                'emolumentStatus' => [],
                'recentAssessments' => collect(),
                'command' => null,
            ]);
        }
        
        $command = Command::find($commandId);
        
        // Get statistics for command officers only
        $commandOfficers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->get();
        
        $totalCommandOfficers = $commandOfficers->count();
        
        // Get emoluments for command officers only
        $commandEmoluments = Emolument::whereHas('officer', function($q) use ($commandId) {
            $q->where('present_station', $commandId);
        });
        
        // Statistics
        $pendingEmolumentsCount = (clone $commandEmoluments)->where('status', 'RAISED')->count();
        $assessedCount = (clone $commandEmoluments)->where('status', 'ASSESSED')->count();
        $validatedCount = (clone $commandEmoluments)->where('status', 'VALIDATED')->count();
        $processedCount = (clone $commandEmoluments)->where('status', 'PROCESSED')->count();
        
        // Emolument status breakdown
        $emolumentStatus = [
            'RAISED' => $pendingEmolumentsCount,
            'ASSESSED' => $assessedCount,
            'VALIDATED' => $validatedCount,
            'PROCESSED' => $processedCount,
        ];
        
        // Pending emoluments for assessment (only from command officers)
        $pendingEmoluments = Emolument::where('status', 'RAISED')
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->with(['officer.presentStation'])
            ->orderBy('submitted_at', 'asc')
            ->take(10)
            ->get();
        
        // Recent assessments (last 5)
        $recentAssessments = Emolument::where('status', 'ASSESSED')
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->with(['officer.presentStation', 'assessment'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        // Recent command officers (last 5)
        $recentCommandOfficers = $commandOfficers
            ->sortByDesc('created_at')
            ->take(5);

        return view('dashboards.assessor.dashboard', compact(
            'totalCommandOfficers',
            'pendingEmolumentsCount',
            'assessedCount',
            'validatedCount',
            'processedCount',
            'pendingEmoluments',
            'commandOfficers',
            'emolumentStatus',
            'recentAssessments',
            'recentCommandOfficers',
            'command'
        ));
    }

    // Validator Dashboard
    public function validator()
    {
        $user = auth()->user();
        
        // Get Validator's command from their role
        $validatorRole = $user->roles()->where('name', 'Validator')->first();
        $commandId = $validatorRole?->pivot->command_id ?? null;
        
        if (!$commandId) {
            return view('dashboards.validator.dashboard', [
                'error' => 'No command assigned. Please contact HRD to assign you to a command.',
                'statistics' => [],
                'pendingEmoluments' => collect(),
                'commandOfficers' => collect(),
                'emolumentStatus' => [],
                'recentValidations' => collect(),
                'command' => null,
            ]);
        }
        
        $command = Command::find($commandId);
        
        // Get statistics for command officers only
        $commandOfficers = Officer::where('present_station', $commandId)
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->get();
        
        $totalCommandOfficers = $commandOfficers->count();
        
        // Get emoluments for command officers only
        $commandEmoluments = Emolument::whereHas('officer', function($q) use ($commandId) {
            $q->where('present_station', $commandId);
        });
        
        // Statistics
        $pendingValidationCount = (clone $commandEmoluments)->where('status', 'ASSESSED')->count();
        $validatedCount = (clone $commandEmoluments)->where('status', 'VALIDATED')->count();
        $processedCount = (clone $commandEmoluments)->where('status', 'PROCESSED')->count();
        $rejectedCount = (clone $commandEmoluments)->where('status', 'REJECTED')->count();
        
        // Emolument status breakdown
        $emolumentStatus = [
            'ASSESSED' => $pendingValidationCount,
            'VALIDATED' => $validatedCount,
            'PROCESSED' => $processedCount,
            'REJECTED' => $rejectedCount,
        ];
        
        // Pending emoluments for validation (only from command officers)
        $pendingEmoluments = Emolument::where('status', 'ASSESSED')
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->with(['officer.presentStation', 'assessment'])
            ->orderBy('assessed_at', 'asc')
            ->take(10)
            ->get();
        
        // Recent validations (last 5)
        $recentValidations = Emolument::where('status', 'VALIDATED')
            ->whereHas('officer', function($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->with(['officer.presentStation', 'validation'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        // Recent command officers (last 5)
        $recentCommandOfficers = $commandOfficers
            ->sortByDesc('created_at')
            ->take(5);

        return view('dashboards.validator.dashboard', compact(
            'totalCommandOfficers',
            'pendingValidationCount',
            'validatedCount',
            'processedCount',
            'rejectedCount',
            'pendingEmoluments',
            'commandOfficers',
            'emolumentStatus',
            'recentValidations',
            'recentCommandOfficers',
            'command'
        ));
    }

    // Area Controller Dashboard
    public function areaController()
    {
        // Get statistics for Area Controller
        $pendingManningRequests = ManningRequest::where('status', 'SUBMITTED')->count();
        $pendingRosters = DutyRoster::where('status', 'SUBMITTED')->count();
        
        // Get validated emoluments (for Area Controller validation)
        $pendingEmoluments = Emolument::where('status', 'VALIDATED')->count();
        
        // Get recent submitted manning requests
        $recentManningRequests = ManningRequest::with(['command.zone', 'requestedBy'])
            ->where('status', 'SUBMITTED')
            ->orderBy('submitted_at', 'desc')
            ->take(5)
            ->get();
        
        // Get recent submitted rosters
        $recentRosters = DutyRoster::with(['command', 'preparedBy'])
            ->where('status', 'SUBMITTED')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('dashboards.area-controller.dashboard', compact(
            'pendingManningRequests',
            'pendingRosters',
            'pendingEmoluments',
            'recentManningRequests',
            'recentRosters'
        ));
    }

    // Zone Coordinator Dashboard
    public function zoneCoordinator()
    {
        $user = auth()->user();
        
        // Get the zone coordinator's zone from their command assignment
        $zoneCoordinatorRole = $user->roles()
            ->where('name', 'Zone Coordinator')
            ->wherePivot('is_active', true)
            ->first();
        
        $coordinatorZone = null;
        $coordinatorCommand = null;
        $zoneOfficers = collect();
        $zoneCommands = collect();
        $recentOrders = collect();
        $zoneStats = [
            'total_officers' => 0,
            'eligible_officers' => 0, // GL 07 and below
            'total_commands' => 0,
            'recent_orders' => 0,
        ];
        
        if ($zoneCoordinatorRole && $zoneCoordinatorRole->pivot->command_id) {
            $coordinatorCommand = Command::find($zoneCoordinatorRole->pivot->command_id);
            $coordinatorZone = $coordinatorCommand ? $coordinatorCommand->zone : null;
            
            if ($coordinatorZone) {
                // Get all commands in the zone
                $zoneCommands = Command::where('zone_id', $coordinatorZone->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
                
                // Get all officers in the zone
                $zoneCommandIds = $zoneCommands->pluck('id')->toArray();
                $zoneOfficers = Officer::whereIn('present_station', $zoneCommandIds)
                    ->where('is_active', true)
                    ->with('presentStation')
                    ->orderBy('surname')
                    ->take(10)
                    ->get();
                
                // Get eligible officers (GL 07 and below)
                $eligibleOfficers = Officer::whereIn('present_station', $zoneCommandIds)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->where('salary_grade_level', 'GL05')
                          ->orWhere('salary_grade_level', 'GL06')
                          ->orWhere('salary_grade_level', 'GL07')
                          ->orWhere('salary_grade_level', '05')
                          ->orWhere('salary_grade_level', '06')
                          ->orWhere('salary_grade_level', '07')
                          ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7")
                          ->orWhereRaw("CAST(salary_grade_level AS UNSIGNED) <= 7");
                    })
                    ->count();
                
                // Get recent staff orders for this zone
                $recentOrders = StaffOrder::whereIn('from_command_id', $zoneCommandIds)
                    ->whereIn('to_command_id', $zoneCommandIds)
                    ->with(['officer', 'fromCommand', 'toCommand'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
                
                $zoneStats = [
                    'total_officers' => Officer::whereIn('present_station', $zoneCommandIds)->where('is_active', true)->count(),
                    'eligible_officers' => $eligibleOfficers,
                    'total_commands' => $zoneCommands->count(),
                    'recent_orders' => StaffOrder::whereIn('from_command_id', $zoneCommandIds)
                        ->whereIn('to_command_id', $zoneCommandIds)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count(),
                ];
            }
        }
        
        return view('dashboards.zone-coordinator.dashboard', compact(
            'coordinatorZone',
            'coordinatorCommand',
            'zoneOfficers',
            'zoneCommands',
            'recentOrders',
            'zoneStats'
        ));
    }

    // DC Admin Dashboard
    public function dcAdmin()
    {
        return view('dashboards.dc-admin.dashboard');
    }

    public function dcAdminLeavePass(Request $request)
    {
        $type = $request->get('type', 'leave');
        $status = $request->get('status', '');
        
        // Get minuted applications (only minuted applications should be visible to DC Admin)
        if ($type === 'leave') {
            $query = LeaveApplication::with(['officer', 'leaveType'])
                ->whereNotNull('minuted_at')
                ->where('status', 'PENDING');
            
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
            
            $leaveApplications = $query->orderBy('minuted_at', 'desc')->paginate(20)->withQueryString();
            $passApplications = collect();
        } else {
            $query = PassApplication::with('officer')
                ->whereNotNull('minuted_at')
                ->where('status', 'PENDING');
            
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
            
            $passApplications = $query->orderBy('minuted_at', 'desc')->paginate(20)->withQueryString();
            $leaveApplications = collect();
        }
        
        return view('dashboards.dc-admin.leave-pass', compact('leaveApplications', 'passApplications', 'type', 'status'));
    }

    // Accounts Dashboard
    public function accounts()
    {
        // Get statistics
        $validatedCount = Emolument::where('status', 'VALIDATED')->count();
        $pendingProcessing = Emolument::where('status', 'VALIDATED')->count();
        $processedThisMonth = Emolument::where('status', 'PROCESSED')
            ->whereMonth('processed_at', now()->month)
            ->whereYear('processed_at', now()->year)
            ->count();
        
        // Account Change Request statistics
        $pendingChangeRequests = AccountChangeRequest::where('status', 'PENDING')->count();
        $recentChangeRequests = AccountChangeRequest::with(['officer.presentStation'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.accounts.dashboard', compact(
            'validatedCount',
            'pendingProcessing',
            'processedThisMonth',
            'pendingChangeRequests',
            'recentChangeRequests'
        ));
    }

    public function validatedOfficers()
    {
        return view('dashboards.accounts.validated-officers');
    }

    public function deceasedOfficers()
    {
        return view('dashboards.accounts.deceased-officers-list');
    }

    // Board Dashboard
    public function board()
    {
        return view('dashboards.board.dashboard');
    }

    public function promotions()
    {
        return view('dashboards.board.promotions');
    }

    // Building Dashboard
    public function building()
    {
        return view('dashboards.building.dashboard');
    }

    public function quarters()
    {
        return view('dashboards.building.quarters');
    }

    // Establishment Dashboard
    public function establishment()
    {
        $totalOfficers = Officer::where('is_active', true)->where('is_deceased', false)->count();
        
        $lastServiceNumber = Officer::whereNotNull('service_number')
            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
            ->value('service_number') ?? 'N/A';

        $pendingRecruits = Officer::whereNull('service_number')
            ->whereNull('appointment_number')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->count();

        return view('dashboards.establishment.dashboard', compact(
            'totalOfficers',
            'lastServiceNumber',
            'pendingRecruits'
        ));
    }

    public function serviceNumbers()
    {
        $lastServiceNumber = Officer::whereNotNull('service_number')
            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
            ->value('service_number');

        // Calculate next available
        $nextAvailable = 'N/A';
        if ($lastServiceNumber) {
            preg_match('/(\d+)$/', $lastServiceNumber, $matches);
            if (!empty($matches[1])) {
                $nextNum = (int) $matches[1] + 1;
                $nextAvailable = 'NCS' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }
        }

        $allocatedToday = Officer::whereNotNull('service_number')
            ->whereDate('updated_at', today())
            ->count();

        // Recent allocations
        $recentAllocations = Officer::whereNotNull('service_number')
            ->with('createdBy')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboards.establishment.service-numbers', compact(
            'lastServiceNumber',
            'nextAvailable',
            'allocatedToday',
            'recentAllocations'
        ));
    }

    public function newRecruits(Request $request)
    {
        $query = Officer::whereNull('service_number')
            ->where('is_active', true)
            ->where('is_deceased', false);

        // Handle sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        // Apply sorting based on sort_by parameter
        switch ($sortBy) {
            case 'name':
                $query->orderBy('surname', $sortOrder)
                      ->orderBy('initials', $sortOrder);
                break;
            case 'email':
                $query->orderBy('email', $sortOrder);
                break;
            case 'appointment_number':
                $query->orderBy('appointment_number', $sortOrder);
                break;
            case 'substantive_rank':
                $query->orderBy('substantive_rank', $sortOrder);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $recruits = $query->paginate(100)->withQueryString();

        return view('dashboards.establishment.new-recruits', compact('recruits'));
    }

    // Welfare Dashboard
    public function welfare()
    {
        // Deceased Officer statistics
        $deceasedOfficersCount = DeceasedOfficer::count();
        $pendingValidationCount = DeceasedOfficer::whereNull('validated_at')->count();
        $validatedCount = DeceasedOfficer::whereNotNull('validated_at')->count();
        $benefitsProcessedCount = DeceasedOfficer::where('benefits_processed', true)
            ->whereYear('benefits_processed_at', now()->year)
            ->count();
        
        // Next of KIN Change Request statistics
        $pendingNextOfKinRequests = NextOfKinChangeRequest::where('status', 'PENDING')->count();
        $recentNextOfKinRequests = NextOfKinChangeRequest::with(['officer.presentStation'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent deceased officers pending validation
        $recentDeceasedOfficers = DeceasedOfficer::with(['officer.presentStation', 'reportedBy'])
            ->whereNull('validated_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.welfare.dashboard', compact(
            'deceasedOfficersCount',
            'pendingValidationCount',
            'validatedCount',
            'benefitsProcessedCount',
            'pendingNextOfKinRequests',
            'recentNextOfKinRequests',
            'recentDeceasedOfficers'
        ));
    }

    public function welfareDeceasedOfficers()
    {
        return view('dashboards.welfare.deceased-officers');
    }

    // Form Pages
    public function raiseEmolument()
    {
        return view('forms.emolument.raise');
    }

    public function applyLeave()
    {
        return view('forms.leave.apply');
    }

    public function applyPass()
    {
        return view('forms.pass.apply');
    }

    // Onboarding Steps
    public function onboardingStep1(Request $request)
    {
        // Validate token and authenticate user
        if ($request->has('token')) {
            try {
                $tokenData = base64_decode($request->token);
                list($userId, $tempPassword) = explode('|', $tokenData, 2);
                
                $user = User::find($userId);
                if ($user && Hash::check($tempPassword, $user->password)) {
                    // Auto-login the user
                    Auth::login($user);
                    
                    // Load existing officer data if available
                    // Explicitly load the officer relationship
                    $user->load('officer');
                    $officer = $user->officer;
                    $savedData = session('onboarding_step1', []);
                    
                    // Pre-fill with existing officer data if available
                    // Only fill fields that are not already in savedData or are empty
                    if ($officer) {
                        $officerData = [
                            'service_number' => $officer->service_number,
                            'initials' => $officer->initials,
                            'surname' => $officer->surname,
                            'first_name' => $officer->surname ?? '',
                            'gender' => $officer->sex == 'M' ? 'Male' : ($officer->sex == 'F' ? 'Female' : ''),
                            'date_of_birth' => $officer->date_of_birth?->format('Y-m-d'),
                            'state_of_origin' => $officer->state_of_origin,
                            'lga' => $officer->lga,
                            'geopolitical_zone' => $officer->geopolitical_zone,
                            'marital_status' => $officer->marital_status,
                            'phone' => $officer->phone_number,
                            'email' => $officer->email ?? $user->email,
                            'residential_address' => $officer->residential_address,
                            'permanent_home_address' => $officer->permanent_home_address,
                        ];
                        
                        // Merge officer data with saved data
                        // For service_number, always use officer's value if it exists
                        if (!empty($officerData['service_number'])) {
                            $savedData['service_number'] = $officerData['service_number'];
                        }
                        
                        // For other fields, don't overwrite non-empty saved values
                        foreach ($officerData as $key => $value) {
                            if ($key !== 'service_number' && (!isset($savedData[$key]) || empty($savedData[$key]))) {
                                $savedData[$key] = $value;
                            }
                        }
                    }
                    
                    return view('forms.onboarding.step1', compact('savedData'));
                }
            } catch (\Exception $e) {
                Log::error('Onboarding token validation error: ' . $e->getMessage());
            }
        }
        
        // If no valid token, require authentication
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Invalid or expired onboarding link. Please contact HRD for a new link.');
        }
        
        // Load existing officer data if available (for already authenticated users)
        $user = auth()->user();
        // Explicitly load the officer relationship
        if (!$user->relationLoaded('officer')) {
            $user->load('officer');
        }
        $officer = $user->officer;
        $savedData = session('onboarding_step1', []);
        
        // Pre-fill with existing officer data if available
        // Only fill fields that are not already in savedData or are empty
        if ($officer) {
            $officerData = [
                'service_number' => $officer->service_number,
                'initials' => $officer->initials,
                'surname' => $officer->surname,
                'first_name' => $officer->surname ?? '',
                'gender' => $officer->sex == 'M' ? 'Male' : ($officer->sex == 'F' ? 'Female' : ''),
                'date_of_birth' => $officer->date_of_birth?->format('Y-m-d'),
                'state_of_origin' => $officer->state_of_origin,
                'lga' => $officer->lga,
                'geopolitical_zone' => $officer->geopolitical_zone,
                'marital_status' => $officer->marital_status,
                'phone' => $officer->phone_number,
                'email' => $officer->email ?? $user->email,
                'residential_address' => $officer->residential_address,
                'permanent_home_address' => $officer->permanent_home_address,
            ];
            
            // Merge officer data with saved data
            // For service_number, always use officer's value if it exists
            if (!empty($officerData['service_number'])) {
                $savedData['service_number'] = $officerData['service_number'];
            }
            
            // For other fields, don't overwrite non-empty saved values
            foreach ($officerData as $key => $value) {
                if ($key !== 'service_number' && (!isset($savedData[$key]) || empty($savedData[$key]))) {
                    $savedData[$key] = $value;
                }
            }
        }
        
        return view('forms.onboarding.step1', compact('savedData'));
    }

    public function saveOnboardingStep1(Request $request)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please use the onboarding link from your email.');
        }
        
        $validated = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'initials' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'geopolitical_zone' => 'required|string|max:255',
            'marital_status' => 'required|string|max:50',
            'residential_address' => 'required|string',
            'permanent_home_address' => 'required|string',
        ]);

        session(['onboarding_step1' => $validated]);
        return redirect()->route('onboarding.step2');
    }

    public function onboardingStep2()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please use the onboarding link from your email.');
        }
        
        // Check if step1 data exists
        if (!session('onboarding_step1')) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete Step 1 first.');
        }
        
        $savedData = session('onboarding_step2', []);
        
        // Pre-fill zone_id from officer's command if available
        $user = auth()->user();
        if (!$user->relationLoaded('officer')) {
            $user->load('officer.presentStation.zone');
        }
        $officer = $user->officer;
        
        if ($officer && $officer->presentStation && $officer->presentStation->zone) {
            if (!isset($savedData['zone_id']) || empty($savedData['zone_id'])) {
                $savedData['zone_id'] = $officer->presentStation->zone->id;
            }
            if (!isset($savedData['command_id']) || empty($savedData['command_id'])) {
                $savedData['command_id'] = $officer->presentStation->id;
            }
        }
        
        return view('forms.onboarding.step2', compact('savedData'));
    }

    public function saveOnboardingStep2(Request $request)
    {
        $validated = $request->validate([
            'date_of_first_appointment' => 'required|date',
            'date_of_present_appointment' => 'required|date',
            'substantive_rank' => 'required|string|max:255',
            'salary_grade_level' => 'required|string|max:10',
            'zone_id' => 'required|exists:zones,id',
            'command_id' => 'required|exists:commands,id',
            'date_posted_to_station' => 'required|date',
            'unit' => 'nullable|string|max:255',
            'education' => 'required|array|min:1',
            'education.*.university' => 'required|string|max:255',
            'education.*.qualification' => 'required|string|max:255',
            'education.*.discipline' => 'nullable|string|max:255',
        ]);

        session(['onboarding_step2' => $validated]);
        return redirect()->route('onboarding.step3');
    }

    public function onboardingStep3()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please use the onboarding link from your email.');
        }
        
        // Check if previous steps completed
        if (!session('onboarding_step1') || !session('onboarding_step2')) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete previous steps first.');
        }
        $savedData = session('onboarding_step3', []);
        return view('forms.onboarding.step3', compact('savedData'));
    }

    public function saveOnboardingStep3(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:20',
            'sort_code' => 'nullable|string|max:20',
            'pfa_name' => 'required|string|max:255',
            'rsa_number' => 'required|string|regex:/^PEN[0-9]{12}$/',
        ]);

        session(['onboarding_step3' => $validated]);
        return redirect()->route('onboarding.step4');
    }

    public function onboardingStep4()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please use the onboarding link from your email.');
        }
        
        // Check if previous steps completed
        if (!session('onboarding_step1') || !session('onboarding_step2') || !session('onboarding_step3')) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete previous steps first.');
        }
        $savedData = session('onboarding_step4', []);
        
        // Load existing profile picture if officer has one
        $user = auth()->user();
        if ($user && $user->officer && $user->officer->profile_picture_url) {
            $savedData['profile_picture_preview'] = asset('storage/' . $user->officer->profile_picture_url);
        }
        
        return view('forms.onboarding.step4', compact('savedData'));
    }

    public function saveOnboardingStep4(Request $request)
    {
        $validated = $request->validate([
            'next_of_kin' => 'required|array|min:1|max:5',
            'next_of_kin.*.name' => 'required|string|max:255',
            'next_of_kin.*.relationship' => 'required|string|max:50',
            'next_of_kin.*.phone_number' => 'required|string|max:20',
            'next_of_kin.*.email' => 'nullable|email|max:255',
            'next_of_kin.*.address' => 'required|string',
            'next_of_kin.*.is_primary' => 'nullable|in:0,1',
            'interdicted' => 'nullable',
            'suspended' => 'nullable',
            'quartered' => 'nullable',
        ]);

        // Ensure at least one next of kin is marked as primary
        $hasPrimary = false;
        foreach ($validated['next_of_kin'] as $nok) {
            if (isset($nok['is_primary']) && $nok['is_primary'] == '1') {
                $hasPrimary = true;
                break;
            }
        }
        
        if (!$hasPrimary) {
            return redirect()->back()
                ->withErrors(['next_of_kin' => 'At least one next of kin must be marked as primary.'])
                ->withInput();
        }

        // Convert checkbox values to boolean
        $validated['interdicted'] = $request->has('interdicted');
        $validated['suspended'] = $request->has('suspended');
        $validated['quartered'] = $request->has('quartered');

        session(['onboarding_step4' => $validated]);
        return redirect()->route('onboarding.step4');
    }

    public function submitOnboarding(Request $request)
    {
        // First, save step 4 data to session
        $validated = $request->validate([
            'next_of_kin' => 'required|array|min:1|max:5',
            'next_of_kin.*.name' => 'required|string|max:255',
            'next_of_kin.*.relationship' => 'required|string|max:50',
            'next_of_kin.*.phone_number' => 'required|string|max:20',
            'next_of_kin.*.email' => 'nullable|email|max:255',
            'next_of_kin.*.address' => 'required|string',
            'next_of_kin.*.is_primary' => 'nullable|in:0,1',
            'profile_picture_data' => 'required|string',
            'documents.*' => 'nullable|file|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Validate that profile picture data is not empty
        if (empty($validated['profile_picture_data']) || !preg_match('/^data:image\//', $validated['profile_picture_data'])) {
            return redirect()->back()
                ->withErrors(['profile_picture_data' => 'Please upload your official passport photo before proceeding.'])
                ->withInput();
        }

        // Ensure at least one next of kin is marked as primary
        $hasPrimary = false;
        foreach ($validated['next_of_kin'] as $nok) {
            if (isset($nok['is_primary']) && $nok['is_primary'] == '1') {
                $hasPrimary = true;
                break;
            }
        }
        
        if (!$hasPrimary) {
            return redirect()->back()
                ->withErrors(['next_of_kin' => 'At least one next of kin must be marked as primary.'])
                ->withInput();
        }

        // Validate disclaimer acceptance
        if (!$request->has('accept_disclaimer')) {
            return redirect()->back()->with('error', 'You must accept the disclaimer before submitting.')->withInput();
        }

        // Save step 4 to session
        $step4 = $validated;
        $step4['interdicted'] = $request->has('interdicted');
        $step4['suspended'] = $request->has('suspended');
        $step4['quartered'] = $request->has('quartered');
        if ($request->has('profile_picture_data')) {
            $step4['profile_picture_data'] = $request->input('profile_picture_data');
        }
        session(['onboarding_step4' => $step4]);

        // Validate all steps are completed
        $step1 = session('onboarding_step1');
        $step2 = session('onboarding_step2');
        $step3 = session('onboarding_step3');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete all steps before submitting.');
        }

        // Redirect to preview page instead of submitting directly
        return redirect()->route('onboarding.preview');
    }

    public function onboardingPreview()
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please use the onboarding link from your email.');
        }
        
        // Check if all steps are completed
        $step1 = session('onboarding_step1');
        $step2 = session('onboarding_step2');
        $step3 = session('onboarding_step3');
        $step4 = session('onboarding_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete all steps before preview.');
        }

        return view('forms.onboarding.preview', compact('step1', 'step2', 'step3', 'step4'));
    }

    public function finalSubmitOnboarding(Request $request)
    {
        // Validate all steps are completed
        $step1 = session('onboarding_step1');
        $step2 = session('onboarding_step2');
        $step3 = session('onboarding_step3');
        $step4 = session('onboarding_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete all steps before submitting.');
        }

        try {
            // Get authenticated user
            $user = auth()->user();

            if (!$user) {
                return redirect()->route('login')->with('error', 'You must be logged in to complete onboarding.');
            }

            // Get existing officer record (should exist from HRD initiation)
            $officer = $user->officer;
            
            if (!$officer) {
                return redirect()->route('onboarding.step1')
                    ->with('error', 'Officer record not found. Please contact HRD.')
                    ->withInput();
            }

            // Combine all data - UPDATE existing officer record
            $officerData = [
                'initials' => $step1['initials'],
                'surname' => $step1['surname'],
                'sex' => $step1['gender'] == 'Male' ? 'M' : 'F',
                'date_of_birth' => $step1['date_of_birth'],
                'date_of_first_appointment' => $step2['date_of_first_appointment'],
                'date_of_present_appointment' => $step2['date_of_present_appointment'],
                'substantive_rank' => $step2['substantive_rank'],
                'salary_grade_level' => $step2['salary_grade_level'],
                'state_of_origin' => $step1['state_of_origin'],
                'lga' => $step1['lga'],
                'geopolitical_zone' => $step1['geopolitical_zone'],
                'marital_status' => $step1['marital_status'],
                'entry_qualification' => isset($step2['education']) && count($step2['education']) > 0 
                    ? $step2['education'][0]['qualification'] 
                    : null, // Use first education entry's qualification as primary
                'discipline' => isset($step2['education']) && count($step2['education']) > 0 
                    ? ($step2['education'][0]['discipline'] ?? null) 
                    : null, // Use first education entry's discipline
                'additional_qualification' => isset($step2['education']) && count($step2['education']) > 0 
                    ? json_encode($step2['education']) 
                    : null, // Store ALL education entries as JSON (including first one with university)
                'present_station' => $step2['command_id'],
                'date_posted_to_station' => $step2['date_posted_to_station'],
                'residential_address' => $step1['residential_address'],
                'permanent_home_address' => $step1['permanent_home_address'],
                'phone_number' => $step1['phone'],
                'email' => $step1['email'] ?? $user->email,
                'bank_name' => $step3['bank_name'],
                'bank_account_number' => $step3['bank_account_number'],
                'sort_code' => $step3['sort_code'] ?? null,
                'pfa_name' => $step3['pfa_name'],
                'rsa_number' => $step3['rsa_number'],
                'unit' => $step2['unit'] ?? null,
                'interdicted' => $step4['interdicted'] ?? false,
                'suspended' => $step4['suspended'] ?? false,
                'quartered' => $step4['quartered'] ?? false,
                'is_active' => true,
            ];

            // Handle profile picture upload if provided (from step4 session)
            if (isset($step4['profile_picture_data']) && !empty($step4['profile_picture_data'])) {
                try {
                    $base64Image = $step4['profile_picture_data'];
                    
                    // Extract base64 data
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $imageType = $matches[1];
                        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
                        
                        // Generate unique filename
                        $filename = 'profile_' . $officer->id . '_' . time() . '.jpg';
                        $path = 'profiles/' . $filename;
                        
                        // Store the image
                        \Storage::disk('public')->put($path, $imageData);
                        
                        // Delete old profile picture if exists
                        if ($officer->profile_picture_url) {
                            $oldPath = storage_path('app/public/' . $officer->profile_picture_url);
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                        
                        // Update officer with profile picture path
                        $officerData['profile_picture_url'] = $path;
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail onboarding
                    \Log::error('Failed to save profile picture during onboarding: ' . $e->getMessage());
                }
            }

            // UPDATE existing officer record instead of creating new one
            $officer->update($officerData);

            // Create next of kin entries
            if (isset($step4['next_of_kin']) && is_array($step4['next_of_kin'])) {
                foreach ($step4['next_of_kin'] as $nok) {
            \App\Models\NextOfKin::create([
                'officer_id' => $officer->id,
                        'name' => $nok['name'],
                        'relationship' => $nok['relationship'],
                        'phone_number' => $nok['phone_number'],
                        'email' => $nok['email'] ?? null,
                        'address' => $nok['address'],
                        'is_primary' => isset($nok['is_primary']) && $nok['is_primary'] == '1',
            ]);
                }
            }

            // Handle document uploads if any
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $path = $document->store('officer-documents', 'public');
                    \App\Models\OfficerDocument::create([
                        'officer_id' => $officer->id,
                        'document_type' => $document->getClientOriginalExtension(),
                        'file_name' => $document->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $document->getSize(),
                        'mime_type' => $document->getMimeType(),
                        'uploaded_by' => $user->id,
                    ]);
                }
            }

            // Send notification to officer about successful onboarding
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notify(
                    $user,
                    'onboarding_completed',
                    'Onboarding Completed Successfully',
                    'Your onboarding has been completed successfully. You can now access all dashboard features.',
                    null,
                    null,
                    true // Send email
                );
            } catch (\Exception $e) {
                // Log error but don't fail onboarding
                \Log::error('Failed to send onboarding completion notification: ' . $e->getMessage());
            }

            // Clear session data
            session()->forget(['onboarding_step1', 'onboarding_step2', 'onboarding_step3', 'onboarding_step4']);

            // Redirect to officer dashboard after successful onboarding
            return redirect()->route('officer.dashboard')->with('success', 'Onboarding completed successfully! You can now access all dashboard features.');
        } catch (\Exception $e) {
            Log::error('Onboarding error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to onboard officer: ' . $e->getMessage())->withInput();
        }
    }

    private function getDashboardRoute($role)
    {
        $routes = [
            'Officer' => 'officer.dashboard',
            'HRD' => 'hrd.dashboard',
            'Staff Officer' => 'staff-officer.dashboard',
            'Assessor' => 'assessor.dashboard',
            'Validator' => 'validator.dashboard',
            'Area Controller' => 'area-controller.dashboard',
            'DC Admin' => 'dc-admin.dashboard',
            'Zone Coordinator' => 'zone-coordinator.dashboard',
            'Accounts' => 'accounts.dashboard',
            'Board' => 'board.dashboard',
            'Building Unit' => 'building.dashboard',
            'Establishment' => 'establishment.dashboard',
            'TRADOC' => 'tradoc.dashboard',
            'ICT' => 'ict.dashboard',
            'Welfare' => 'welfare.dashboard',
        ];

        return $routes[$role] ?? 'officer.dashboard';
    }
}

