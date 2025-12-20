<?php

namespace App\Http\Controllers;

use App\Models\Command;
use App\Models\Emolument;
use App\Models\EmolumentAssessment;
use App\Models\EmolumentTimeline;
use App\Models\EmolumentValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmolumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display officer's emoluments list
     */
    /**
     * Display emoluments list based on role
     */
    public function index()
    {
        $user = auth()->user();

        // If Assessor, show only emoluments from their command
        if ($user->hasRole('Assessor')) {
            // Get Assessor's command from their role
            $assessorRole = $user->roles()->where('name', 'Assessor')->first();
            $commandId = $assessorRole?->pivot->command_id ?? null;
            
            $query = Emolument::with(['officer.presentStation', 'timeline']);
            
            // Filter by command - only show officers from Assessor's command
            if ($commandId) {
                $query->whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                });
            } else {
                // If no command assigned, return empty results
                $query->whereRaw('1 = 0');
            }

            // Filter by Status
            if ($status = request('status')) {
                $query->where('status', $status);
            }

            // Filter by Year
            if ($year = request('year')) {
                $query->where('year', $year);
            }

            // Search by Name or Service Number
            if ($search = request('search')) {
                $query->whereHas('officer', function ($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('other_names', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = request('sort_by', 'submitted_at');
            $sortOrder = request('sort_order', 'desc');
            $allowedSorts = ['year', 'status', 'submitted_at'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('submitted_at', 'desc');
            }

            $emoluments = $query->paginate(20)->withQueryString();

            // Get unique years for filter dropdown
            $years = Emolument::distinct()->orderBy('year', 'desc')->pluck('year');

            return view('dashboards.assessor.emoluments-list', compact('emoluments', 'years'));
        }

        // If Area Controller, show assessed emoluments pending validation
        if ($user->hasRole('Area Controller')) {
            // Calculate statistics
            $pendingValidation = Emolument::where('status', 'ASSESSED')->count();
            $validatedToday = Emolument::where('status', 'VALIDATED')
                ->whereDate('validated_at', today())
                ->count();
            $totalProcessed = Emolument::where('status', 'VALIDATED')->count();
            
            // Base query for pending validations (ASSESSED status)
            $query = Emolument::with(['officer.presentStation', 'timeline', 'assessment.assessor'])
                ->where('status', 'ASSESSED');

            // Filter by Year
            if ($year = request('year')) {
                $query->where('year', $year);
            }

            // Search by Name or Service Number
            if ($search = request('search')) {
                $query->whereHas('officer', function ($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('other_names', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = request('sort_by', 'assessed_at');
            $sortOrder = request('sort_order', 'desc');
            $allowedSorts = ['year', 'assessed_at', 'submitted_at'];

            if ($sortBy === 'officer') {
                $query->join('officers', 'emoluments.officer_id', '=', 'officers.id')
                    ->orderBy('officers.surname', $sortOrder)
                    ->orderBy('officers.initials', $sortOrder)
                    ->select('emoluments.*');
            } elseif ($sortBy === 'service_number') {
                $query->join('officers', 'emoluments.officer_id', '=', 'officers.id')
                    ->orderBy('officers.service_number', $sortOrder)
                    ->select('emoluments.*');
            } elseif ($sortBy === 'rank') {
                $query->join('officers', 'emoluments.officer_id', '=', 'officers.id')
                    ->orderBy('officers.substantive_rank', $sortOrder)
                    ->select('emoluments.*');
            } elseif ($sortBy === 'assessor') {
                $query->join('emolument_assessments', 'emoluments.id', '=', 'emolument_assessments.emolument_id')
                    ->join('users', 'emolument_assessments.assessor_id', '=', 'users.id')
                    ->orderBy('users.email', $sortOrder)
                    ->select('emoluments.*');
            } elseif (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('assessed_at', 'desc');
            }

            $emoluments = $query->paginate(20)->withQueryString();

            // Get unique years for filter dropdown
            $years = Emolument::distinct()->orderBy('year', 'desc')->pluck('year');

            return view('dashboards.area-controller.emoluments', compact(
                'emoluments', 
                'years',
                'pendingValidation',
                'validatedToday',
                'totalProcessed'
            ));
        }

        // If Validator, show assessed emoluments for validation
        if ($user->hasRole('Validator')) {
            // Get Validator's command from their role
            $validatorRole = $user->roles()->where('name', 'Validator')->first();
            $commandId = $validatorRole?->pivot->command_id ?? null;
            
            $query = Emolument::with(['officer.presentStation', 'timeline', 'assessment'])
                ->whereIn('status', ['ASSESSED', 'VALIDATED']);
            
            // Filter by command - only show officers from Validator's command
            if ($commandId) {
                $query->whereHas('officer', function($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                });
            } else {
                // If no command assigned, return empty results
                $query->whereRaw('1 = 0');
            }

            // Filter by Status
            if ($status = request('status')) {
                $query->where('status', $status);
            }

            // Filter by Year
            if ($year = request('year')) {
                $query->where('year', $year);
            }

            // Search by Name or Service Number
            if ($search = request('search')) {
                $query->whereHas('officer', function ($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('other_names', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = request('sort_by', 'assessed_at');
            $sortOrder = request('sort_order', 'desc');
            $allowedSorts = ['year', 'status', 'assessed_at', 'submitted_at'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('assessed_at', 'desc');
            }

            $emoluments = $query->paginate(20)->withQueryString();

            // Get unique years for filter dropdown
            $years = Emolument::distinct()->orderBy('year', 'desc')->pluck('year');

            return view('dashboards.validator.emoluments-list', compact('emoluments', 'years'));
        }

        // Default: Officer view (My Emoluments)
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found');
        }

        $emoluments = Emolument::where('officer_id', $officer->id)
            ->with('timeline')
            ->orderBy('year', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'raised' => $emoluments->where('status', 'RAISED')->count(),
            'assessed' => $emoluments->where('status', 'ASSESSED')->count(),
            'validated' => $emoluments->where('status', 'VALIDATED')->count(),
            'processed' => $emoluments->where('status', 'PROCESSED')->count(),
        ];

        return view('dashboards.officer.emoluments', compact('emoluments', 'stats'));
    }

    /**
     * Show form to raise emolument
     */
    public function create()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found');
        }

        // Get active timelines
        $timelines = EmolumentTimeline::where('is_active', true)
            ->orderBy('year', 'desc')
            ->get();

        // Get IDs of timelines where officer already has an emolument
        $existingTimelineIds = Emolument::where('officer_id', $officer->id)
            ->whereIn('timeline_id', $timelines->pluck('id'))
            ->pluck('timeline_id')
            ->toArray();

        // Filter out timelines that have already been submitted
        $availableTimelines = $timelines->reject(function ($timeline) use ($existingTimelineIds) {
            return in_array($timeline->id, $existingTimelineIds);
        });

        if ($availableTimelines->isEmpty()) {
            return redirect()->route('officer.emoluments')
                ->with('info', 'You have already submitted emoluments for all active timelines.');
        }

        return view('forms.emolument.raise', [
            'timelines' => $availableTimelines,
            'officer' => $officer
        ]);
    }

    /**
     * Store new emolument
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->back()->with('error', 'Officer record not found');
        }

        // Validate request
        $validated = $request->validate([
            'timeline_id' => 'required|exists:emolument_timelines,id',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'pfa_name' => 'required|string|max:255',
            'rsa_pin' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Check if timeline is still active
        $timeline = EmolumentTimeline::find($validated['timeline_id']);
        if (!$timeline || !$timeline->is_active) {
            return redirect()->back()->with('error', 'Selected timeline is not active');
        }

        // Check for duplicate
        $existing = Emolument::where('officer_id', $officer->id)
            ->where('timeline_id', $validated['timeline_id'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'You have already raised an emolument for this timeline');
        }

        // Create emolument
        DB::beginTransaction();
        try {
            $emolument = Emolument::create([
                'officer_id' => $officer->id,
                'timeline_id' => $validated['timeline_id'],
                'year' => $timeline->year,
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'pfa_name' => $validated['pfa_name'],
                'rsa_pin' => $validated['rsa_pin'],
                'notes' => $validated['notes'],
                'status' => 'RAISED',
                'submitted_at' => now(),
            ]);

            DB::commit();

            // Notify Assessors about the new emolument
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyEmolumentRaised($emolument);

            return redirect()->route('officer.emoluments')
                ->with('success', 'Emolument raised successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to raise emolument: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show emolument details
     */
    public function show($id)
    {
        $emolument = Emolument::with([
            'officer.presentStation',
            'timeline',
            'assessment.assessor',
            'validation.validator'
        ])->findOrFail($id);

        // Check authorization
        $user = auth()->user();
        if ($user->officer && $user->officer->id !== $emolument->officer_id) {
            if (!$user->hasAnyRole(['HRD', 'Assessor', 'Validator', 'Accounts', 'Area Controller'])) {
                abort(403, 'Unauthorized access');
            }
        }

        // Determine back route and breadcrumbs based on user role
        $backRoute = 'officer.emoluments';
        $breadcrumbRole = 'Officer';
        $breadcrumbRoute = 'officer.dashboard';

        if ($user->hasRole('Assessor')) {
            $backRoute = 'assessor.emoluments';
            $breadcrumbRole = 'Assessor';
            $breadcrumbRoute = 'assessor.dashboard';
        } elseif ($user->hasRole('Validator')) {
            $backRoute = 'validator.emoluments';
            $breadcrumbRole = 'Validator';
            $breadcrumbRoute = 'validator.dashboard';
        } elseif ($user->hasRole('HRD')) {
            $backRoute = 'hrd.emoluments';
            $breadcrumbRole = 'HRD';
            $breadcrumbRoute = 'hrd.dashboard';
        } elseif ($user->hasRole('Accounts')) {
            $backRoute = 'accounts.validated-officers';
            $breadcrumbRole = 'Accounts';
            $breadcrumbRoute = 'accounts.dashboard';
        } elseif ($user->hasRole('Area Controller')) {
            $backRoute = 'area-controller.emoluments';
            $breadcrumbRole = 'Area Controller';
            $breadcrumbRoute = 'area-controller.dashboard';
        }

        return view('dashboards.emolument.show', compact('emolument', 'backRoute', 'breadcrumbRole', 'breadcrumbRoute'));
    }

    /**
     * Show assessment form (Assessor)
     */
    public function assess($id)
    {
        $user = auth()->user();
        $emolument = Emolument::with('officer.presentStation')->findOrFail($id);

        // If Assessor, verify emolument is from their command
        if ($user->hasRole('Assessor')) {
            $assessorRole = $user->roles()->where('name', 'Assessor')->first();
            $commandId = $assessorRole?->pivot->command_id ?? null;
            
            if ($commandId && $emolument->officer->present_station != $commandId) {
                return redirect()->route('assessor.dashboard')
                    ->with('error', 'You can only assess emoluments from officers in your command.');
            }
        }

        if ($emolument->status !== 'RAISED') {
            return redirect()->back()->with('error', 'Emolument must be in RAISED status to be assessed');
        }

        return view('forms.emolument.assess', compact('emolument'));
    }

    /**
     * Process assessment (Assessor)
     */
    public function processAssessment(Request $request, $id)
    {
        $user = auth()->user();
        $emolument = Emolument::with('officer')->findOrFail($id);

        // If Assessor, verify emolument is from their command
        if ($user->hasRole('Assessor')) {
            $assessorRole = $user->roles()->where('name', 'Assessor')->first();
            $commandId = $assessorRole?->pivot->command_id ?? null;
            
            if ($commandId && $emolument->officer->present_station != $commandId) {
                return redirect()->route('assessor.dashboard')
                    ->with('error', 'You can only assess emoluments from officers in your command.');
            }
        }

        if ($emolument->status !== 'RAISED') {
            return redirect()->back()->with('error', 'Emolument must be in RAISED status');
        }

        $validated = $request->validate([
            'assessment_status' => 'required|in:APPROVED,REJECTED',
            'comments' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create assessment record
            EmolumentAssessment::create([
                'emolument_id' => $emolument->id,
                'assessor_id' => auth()->id(),
                'assessment_status' => $validated['assessment_status'],
                'comments' => $validated['comments'],
            ]);

            // Update emolument status
            $emolument->update([
                'status' => $validated['assessment_status'] === 'APPROVED' ? 'ASSESSED' : 'REJECTED',
                'assessed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('assessor.emoluments')
                ->with('success', 'Emolument assessed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Assessment failed: ' . $e->getMessage());
        }
    }

    /**
     * Show validation form (Validator)
     */
    public function validateForm($id)
    {
        $user = auth()->user();
        $emolument = Emolument::with(['officer.presentStation', 'assessment.assessor'])->findOrFail($id);

        // If Validator, verify emolument is from their command
        if ($user->hasRole('Validator')) {
            $validatorRole = $user->roles()->where('name', 'Validator')->first();
            $commandId = $validatorRole?->pivot->command_id ?? null;
            
            if ($commandId && $emolument->officer->present_station != $commandId) {
                return redirect()->route('validator.dashboard')
                    ->with('error', 'You can only validate emoluments from officers in your command.');
            }
        }
        
        // Area Controller can validate any assessed emolument (no command restriction)

        if ($emolument->status !== 'ASSESSED') {
            return redirect()->back()->with('error', 'Emolument must be assessed before validation');
        }

        // Check if assessment exists, if not try to find it
        if (!$emolument->assessment) {
            $assessment = EmolumentAssessment::where('emolument_id', $emolument->id)->first();
            if ($assessment) {
                $emolument->load('assessment');
            } else {
                // If status is ASSESSED but no assessment exists, create a minimal one
                \Log::warning('Emolument ASSESSED but no assessment record found', [
                    'emolument_id' => $emolument->id,
                    'status' => $emolument->status
                ]);
                
                $assessment = EmolumentAssessment::create([
                    'emolument_id' => $emolument->id,
                    'assessor_id' => auth()->id(), // Use current validator as fallback
                    'assessment_status' => 'APPROVED',
                    'comments' => 'Assessment record created automatically due to data inconsistency',
                ]);
                
                // Update assessed_at if missing
                if (!$emolument->assessed_at) {
                    $emolument->update(['assessed_at' => $assessment->created_at]);
                }
                
                $emolument->load('assessment');
            }
        } else {
            // Ensure assessed_at is set if missing
            if (!$emolument->assessed_at && $emolument->assessment) {
                $emolument->update(['assessed_at' => $emolument->assessment->created_at]);
                $emolument->refresh();
            }
        }

        // Check if already validated
        if ($emolument->validation) {
            return redirect()->back()->with('error', 'This emolument has already been validated.');
        }

        return view('forms.emolument.validate', compact('emolument'));
    }

    /**
     * Process validation (Validator)
     */
    public function processValidation(Request $request, $id)
    {
        try {
            $user = auth()->user();
            // Load emolument with all necessary relationships
            $emolument = Emolument::with(['officer.presentStation', 'assessment', 'validation'])->findOrFail($id);

            // If Validator, verify emolument is from their command
            if ($user->hasRole('Validator')) {
                $validatorRole = $user->roles()->where('name', 'Validator')->first();
                $commandId = $validatorRole?->pivot->command_id ?? null;
                
                if ($commandId && $emolument->officer->present_station != $commandId) {
                    return redirect()->route('validator.dashboard')
                        ->with('error', 'You can only validate emoluments from officers in your command.');
                }
            }

            if ($emolument->status !== 'ASSESSED') {
                return redirect()->back()->with('error', 'Emolument must be assessed before validation. Current status: ' . $emolument->status);
            }

            // Reload assessment if not loaded
            if (!$emolument->assessment) {
                // Try to find assessment directly
                $assessment = EmolumentAssessment::where('emolument_id', $emolument->id)->first();
                if (!$assessment) {
                    // If status is ASSESSED but no assessment exists, this is a data inconsistency
                    // We'll create a minimal assessment record to fix this
                    \Log::warning('Emolument ASSESSED but no assessment record found', [
                        'emolument_id' => $emolument->id,
                        'status' => $emolument->status
                    ]);
                    
                    // Create a minimal assessment record to fix data inconsistency
                    $assessment = EmolumentAssessment::create([
                        'emolument_id' => $emolument->id,
                        'assessor_id' => auth()->id(), // Use current validator as fallback
                        'assessment_status' => 'APPROVED',
                        'comments' => 'Assessment record created automatically due to data inconsistency',
                    ]);
                    
                    // Reload the relationship
                    $emolument->load('assessment');
                } else {
                    // Reload the relationship
                    $emolument->load('assessment');
                }
            }
            
            // Ensure assessed_at is set if missing
            if (!$emolument->assessed_at && $emolument->assessment) {
                $emolument->update(['assessed_at' => $emolument->assessment->created_at]);
                $emolument->refresh();
            }

            // Check if already validated
            if ($emolument->validation) {
                return redirect()->back()->with('error', 'This emolument has already been validated.');
            }

            $validated = $request->validate([
                'validation_status' => 'required|in:APPROVED,REJECTED',
                'comments' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();
            
            // Create validation record
            $validation = EmolumentValidation::create([
                'emolument_id' => $emolument->id,
                'assessment_id' => $emolument->assessment->id,
                'validator_id' => auth()->id(),
                'validation_status' => $validated['validation_status'],
                'comments' => $validated['comments'] ?? null,
            ]);

            // Update emolument status
            $emolument->update([
                'status' => $validated['validation_status'] === 'APPROVED' ? 'VALIDATED' : 'REJECTED',
                'validated_at' => now(),
            ]);

            DB::commit();

            // Redirect based on user role
            $user = auth()->user();
            if ($user->hasRole('Area Controller')) {
                return redirect()->route('area-controller.emoluments')
                    ->with('success', 'Emolument validated successfully');
            }

            return redirect()->route('validator.emoluments')
                ->with('success', 'Emolument validated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Validation failed: ' . $e->getMessage(), [
                'emolument_id' => $id ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show validated emoluments for processing (Accounts)
     */
    public function validated(Request $request)
    {
        $query = Emolument::where('status', 'VALIDATED')
            ->with('officer.presentStation');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('command_id')) {
            $commandId = (int) $request->command_id;
            $query->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            });
        }

        $emoluments = $query->orderBy('validated_at', 'asc')->get();

        // Get unique years for filter
        $years = Emolument::where('status', 'VALIDATED')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        // Get commands for filter
        $commands = Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboards.accounts.validated-emoluments', compact('emoluments', 'years', 'commands'));
    }

    /**
     * Process emolument payment (Accounts)
     */
    public function processPayment(Request $request, $id)
    {
        $emolument = Emolument::findOrFail($id);

        if ($emolument->status !== 'VALIDATED') {
            return redirect()->back()->with('error', 'Only validated emoluments can be processed');
        }

        DB::beginTransaction();
        try {
            $emolument->update([
                'status' => 'PROCESSED',
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Emolument processed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk process emoluments (Accounts)
     */
    public function bulkProcess(Request $request)
    {
        $request->validate([
            'emolument_ids' => 'required|array',
            'emolument_ids.*' => 'exists:emoluments,id',
        ]);

        $emoluments = Emolument::whereIn('id', $request->emolument_ids)
            ->where('status', 'VALIDATED')
            ->get();

        if ($emoluments->isEmpty()) {
            return redirect()->back()->with('error', 'No valid emoluments selected for processing');
        }

        DB::beginTransaction();
        try {
            $processed = 0;
            foreach ($emoluments as $emolument) {
                $emolument->update([
                    'status' => 'PROCESSED',
                    'processed_at' => now(),
                ]);
                $processed++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "Successfully processed {$processed} emolument(s)");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Bulk processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Show processed emoluments history (Accounts)
     */
    public function processedHistory(Request $request)
    {
        $query = Emolument::where('status', 'PROCESSED')
            ->with(['officer.presentStation', 'assessment', 'validation']);

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by command
        if ($request->filled('command_id')) {
            $commandId = (int) $request->command_id;
            $query->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('processed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('processed_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'processed_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['processed_at', 'year'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Default sort by processed_at desc
            $query->orderBy('processed_at', 'desc');
        }
        
        // For officer name sorting, we'll sort after getting results
        if ($sortBy === 'officer_id') {
            $query->orderBy('processed_at', 'desc'); // Temporary order
        }

        // Get unique years for filter
        $years = Emolument::where('status', 'PROCESSED')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        // Get commands for filter
        $commands = Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get results
        $emoluments = $query->get();
        
        // Sort by officer name if requested (after getting results to avoid join issues)
        if ($sortBy === 'officer_id') {
            $emoluments = $emoluments->sortBy(function($emolument) {
                return ($emolument->officer->surname ?? '') . ($emolument->officer->initials ?? '');
            }, SORT_REGULAR, $sortOrder === 'desc');
        }
        
        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $items = $emoluments->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $emoluments->count();
        $emoluments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('dashboards.accounts.processed-history', compact('emoluments', 'years', 'commands'));
    }

    /**
     * Export processed emoluments report (Accounts)
     */
    public function exportProcessedReport(Request $request)
    {
        $query = Emolument::where('status', 'PROCESSED')
            ->with(['officer.presentStation', 'assessment', 'validation']);

        // Apply same filters as history page
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('command_id')) {
            $query->whereHas('officer', function ($q) use ($request) {
                $q->where('present_station', $request->command_id);
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('processed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('processed_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%");
            });
        }

        $emoluments = $query->orderBy('processed_at', 'desc')->get();

        $format = $request->get('format', 'csv');

        if ($format === 'csv') {
            $filename = 'processed_emoluments_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($emoluments) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Officer Name',
                    'Service Number',
                    'Year',
                    'Command',
                    'Processed Date',
                    'Validated Date',
                    'Assessed Date',
                    'Bank Name',
                    'Account Number'
                ]);

                // Data
                foreach ($emoluments as $emolument) {
                    fputcsv($file, [
                        ($emolument->officer->initials ?? '') . ' ' . ($emolument->officer->surname ?? ''),
                        $emolument->officer->service_number ?? 'N/A',
                        $emolument->year,
                        $emolument->officer->presentStation->name ?? 'N/A',
                        $emolument->processed_at ? $emolument->processed_at->format('Y-m-d') : 'N/A',
                        $emolument->validated_at ? $emolument->validated_at->format('Y-m-d') : 'N/A',
                        $emolument->assessed_at ? $emolument->assessed_at->format('Y-m-d') : 'N/A',
                        $emolument->bank_name ?? 'N/A',
                        $emolument->bank_account_number ?? 'N/A',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return redirect()->back()->with('error', 'Invalid export format');
    }
}


