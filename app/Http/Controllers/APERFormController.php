<?php

namespace App\Http\Controllers;

use App\Models\APERForm;
use App\Models\APERTimeline;
use App\Models\Officer;
use App\Models\User;
use App\Services\DutyRosterService;
use App\Services\RankComparisonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class APERFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Officer: View their APER forms
    public function index()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $forms = APERForm::where('officer_id', $officer->id)
            ->with(['timeline', 'reportingOfficer', 'countersigningOfficer'])
            ->orderBy('year', 'desc')
            ->paginate(20);

        return view('dashboards.officer.aper-forms', compact('forms'));
    }

    // Officer: Create new APER form - DISABLED
    // Officers can no longer create their own forms. Forms are created by Reporting Officers.
    public function create()
    {
        return redirect()->route('officer.aper-forms')
            ->with('error', 'APER forms are created by Reporting Officers. Please contact your Reporting Officer (OIC or 2IC) to have your APER form created.');
    }

    // Officer: Edit existing APER form (only if DRAFT) - DISABLED
    // Officers can no longer edit forms. Forms are filled by Reporting Officers.
    public function edit($id)
    {
        return redirect()->route('officer.aper-forms')
            ->with('error', 'APER forms are filled by Reporting Officers. Please contact your Reporting Officer (OIC or 2IC).');
    }

    // Reporting Officer: Search for officers to access their APER forms
    public function store(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $activeTimeline = APERTimeline::where('is_active', true)->first();

        if (!$activeTimeline || !$activeTimeline->can_submit) {
            return redirect()->back()->with('error', 'APER form submission period is not active.');
        }

        // Check if form already exists
        $existingForm = APERForm::where('officer_id', $officer->id)
            ->where('year', $activeTimeline->year)
            ->first();

        if ($existingForm && $existingForm->status !== 'DRAFT') {
            return redirect()->route('officer.aper-forms.show', $existingForm->id)
                ->with('error', 'APER form for this year already exists.');
        }

        $validated = $this->validateFormData($request);

        // Handle qualifications array
        if ($request->has('qualifications')) {
            $qualifications = [];
            foreach ($request->qualifications as $qual) {
                if (!empty($qual['qualification']) || !empty($qual['year'])) {
                    $qualifications[] = $qual;
                }
            }
            $validated['qualifications'] = $qualifications;
        }

        // Handle leave records arrays
        if ($request->has('sick_leave_records')) {
            $validated['sick_leave_records'] = array_filter($request->sick_leave_records ?? [], function ($record) {
                return !empty($record['type']) || !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('maternity_leave_records')) {
            $validated['maternity_leave_records'] = array_filter($request->maternity_leave_records ?? [], function ($record) {
                return !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('annual_casual_leave_records')) {
            $validated['annual_casual_leave_records'] = array_filter($request->annual_casual_leave_records ?? [], function ($record) {
                return !empty($record['from']) || !empty($record['to']);
            });
        }

        // Handle targets arrays
        if ($request->has('division_targets')) {
            $validated['division_targets'] = array_filter($request->division_targets ?? []);
        }
        if ($request->has('individual_targets')) {
            $validated['individual_targets'] = array_filter($request->individual_targets ?? []);
        }

        // Handle training courses array
        if ($request->has('training_courses')) {
            $validated['training_courses'] = array_filter($request->training_courses ?? [], function ($course) {
                return !empty($course['type']) || !empty($course['where']);
            });
        }

        $action = $request->input('action', 'save');
        $status = $action === 'submit' ? 'SUBMITTED' : 'DRAFT';

        DB::beginTransaction();
        try {
            if ($existingForm) {
                $form = $existingForm;
                $validated['status'] = $status;
                if ($action === 'submit') {
                    $validated['submitted_at'] = now();
                }
                $form->update($validated);
            } else {
                $validated['officer_id'] = $officer->id;
                $validated['timeline_id'] = $activeTimeline->id;
                $validated['year'] = $activeTimeline->year;
                $validated['status'] = $status;
                if ($action === 'submit') {
                    $validated['submitted_at'] = now();
                }
                $form = APERForm::create($validated);
            }

            DB::commit();

            // Send notification if form was submitted
            if ($action === 'submit' && $form->officer->user && $form->officer->user->email) {
                \App\Jobs\SendAPERFormSubmittedMailJob::dispatch($form);
            }

            $message = $action === 'submit'
                ? 'APER form submitted successfully. Waiting for Reporting Officer assignment.'
                : 'APER form saved successfully.';
            return redirect()->route('officer.aper-forms.show', $form->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save APER form: ' . $e->getMessage());
        }
    }

    // Officer: Update existing APER form (only if DRAFT)
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $form = APERForm::findOrFail($id);

        // Check ownership
        if ($form->officer_id !== $officer->id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Only allow updating if status is DRAFT
        if ($form->status !== 'DRAFT') {
            return redirect()->route('officer.aper-forms.show', $form->id)
                ->with('error', 'You can only edit draft forms.');
        }

        $activeTimeline = $form->timeline;

        if (!$activeTimeline) {
            return redirect()->back()->with('error', 'Timeline not found for this form.');
        }

        // Do not allow editing or updating if timeline period has ended
        if (!$activeTimeline->can_submit) {
            return redirect()->back()->with('error', 'APER form submission period has ended. You cannot edit or update this form.');
        }

        $action = $request->input('action', 'save');

        $validated = $this->validateFormData($request);

        // Handle qualifications array
        if ($request->has('qualifications')) {
            $qualifications = [];
            foreach ($request->qualifications as $qual) {
                if (!empty($qual['qualification']) || !empty($qual['year'])) {
                    $qualifications[] = $qual;
                }
            }
            $validated['qualifications'] = $qualifications;
        }

        // Handle leave records arrays
        if ($request->has('sick_leave_records')) {
            $validated['sick_leave_records'] = array_filter($request->sick_leave_records ?? [], function ($record) {
                return !empty($record['type']) || !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('maternity_leave_records')) {
            $validated['maternity_leave_records'] = array_filter($request->maternity_leave_records ?? [], function ($record) {
                return !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('annual_casual_leave_records')) {
            $validated['annual_casual_leave_records'] = array_filter($request->annual_casual_leave_records ?? [], function ($record) {
                return !empty($record['from']) || !empty($record['to']);
            });
        }

        // Handle targets arrays
        if ($request->has('division_targets')) {
            $validated['division_targets'] = array_filter($request->division_targets ?? []);
        }
        if ($request->has('individual_targets')) {
            $validated['individual_targets'] = array_filter($request->individual_targets ?? []);
        }

        // Handle training courses array
        if ($request->has('training_courses')) {
            $validated['training_courses'] = array_filter($request->training_courses ?? [], function ($course) {
                return !empty($course['type']) || !empty($course['where']);
            });
        }

        $status = $action === 'submit' ? 'SUBMITTED' : 'DRAFT';

        DB::beginTransaction();
        try {
            $validated['status'] = $status;
            if ($action === 'submit') {
                $validated['submitted_at'] = now();
            }
            $form->update($validated);

            DB::commit();
            $message = $action === 'submit'
                ? 'APER form submitted successfully. Waiting for Reporting Officer assignment.'
                : 'APER form updated successfully.';
            return redirect()->route('officer.aper-forms.show', $form->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update APER form: ' . $e->getMessage());
        }
    }

    // Officer: Submit APER form (separate route)
    public function submit($id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->officer->user_id !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if ($form->status !== 'DRAFT') {
            return redirect()->back()->with('error', 'Form has already been submitted.');
        }

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'APER form submitted successfully. Waiting for Reporting Officer assignment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit form: ' . $e->getMessage());
        }
    }

    // Reporting Officer: Search for officers to access their APER forms
    public function searchOfficers(Request $request)
    {
        $user = auth()->user();

        // Allow access if user has Reporting Officer role, HRD role, Staff Officer role, OR is an officer
        $this->authorizeAnyRoleOrOfficer(['Reporting Officer', 'HRD', 'Staff Officer']);

        // Get Reporting Officer's officer record
        $reportingOfficer = $user->officer;
        if (!$reportingOfficer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        // Get Reporting Officer's command
        $commandId = $reportingOfficer->present_station;
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to a command to access APER forms.');
        }

        // Get active timeline
        $activeTimeline = APERTimeline::where('is_active', true)->first();
        $formYear = $activeTimeline ? $activeTimeline->year : date('Y'); // Year for APER forms
        $rosterCheckYear = $formYear; // Year to use for roster checks (may fallback to current year)

        $dutyRosterService = app(DutyRosterService::class);
        $rankComparisonService = app(RankComparisonService::class);

        // 1. Determine all subordinates the user is OIC or 2IC for in the given year
        $subordinateIds = $dutyRosterService->getSubordinateIds($reportingOfficer->id, $commandId, $rosterCheckYear);
        
        // Fallback: If no subordinates found for APER timeline year, check current year
        // This handles cases where roster exists for current year but APER timeline is for different year
        if (empty($subordinateIds) && $activeTimeline && $activeTimeline->year != date('Y')) {
            $currentYearSubordinates = $dutyRosterService->getSubordinateIds($reportingOfficer->id, $commandId, date('Y'));
            if (!empty($currentYearSubordinates)) {
                $subordinateIds = $currentYearSubordinates;
                $rosterCheckYear = date('Y'); // Use current year for roster checks
            }
        }

        // 2. Query officers in the command
        $query = Officer::where('present_station', $commandId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting: Subordinates (those under OIC/2IC roster) first, then by surname
        if (!empty($subordinateIds)) {
            $idsString = implode(',', $subordinateIds);
            $query->orderByRaw("CASE WHEN id IN ({$idsString}) THEN 0 ELSE 1 END");
        }
        $query->orderBy('surname');

        $officers = $query->paginate(20);

        foreach ($officers as $officer) {
            // Can user START an APER for this officer? (Must be OIC/2IC and not self)
            $officer->is_subordinate = in_array($officer->id, $subordinateIds) && $officer->id !== $reportingOfficer->id;

            // Check for pending countersignature (use APER timeline year, not roster check year)
            $pendingForm = APERForm::where('officer_id', $officer->id)
                ->where('year', $formYear)
                ->where('status', 'COUNTERSIGNING_OFFICER')
                ->where('reporting_officer_id', '!=', $user->id) // Cannot countersign self-reported form
                ->first();

            $officer->pending_form = $pendingForm;
            $officer->can_countersign = false;

            if ($pendingForm) {
                $ro = $pendingForm->reportingOfficer;
                if ($ro && $ro->officer) {
                    if ($rankComparisonService->isRankHigherOrEqual($reportingOfficer->id, $ro->officer->id)) {
                        $officer->can_countersign = true;
                    }
                }
            }

            $officer->roster_role = $dutyRosterService->getOfficerRoleInRoster($officer->id, $commandId, $rosterCheckYear);
        }

        $isOICOr2IC = $dutyRosterService->isOfficerOICOr2IC($reportingOfficer->id, $commandId, $rosterCheckYear);
        $rosterRole = $dutyRosterService->getOfficerRoleInRoster($reportingOfficer->id, $commandId, $rosterCheckYear);
        
        // Get roster unit name for display (like dashboard)
        $rosterUnitName = null;
        if ($rosterRole) {
            $startDate = "{$rosterCheckYear}-01-01";
            $endDate = "{$rosterCheckYear}-12-31";
            $rosterAsOIC = \App\Models\DutyRoster::where('command_id', $commandId)
                ->whereIn('status', ['APPROVED', 'SUBMITTED'])
                ->where(function ($query) use ($reportingOfficer) {
                    $query->where('oic_officer_id', $reportingOfficer->id)
                        ->orWhere('second_in_command_officer_id', $reportingOfficer->id);
                })
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($nullQuery) {
                        $nullQuery->whereNull('roster_period_start')
                            ->whereNull('roster_period_end');
                    })
                        ->orWhere(function ($dateQuery) use ($startDate, $endDate) {
                            $dateQuery->whereNotNull('roster_period_start')
                                ->whereNotNull('roster_period_end')
                                ->where(function ($overlapQuery) use ($startDate, $endDate) {
                                    $overlapQuery->whereBetween('roster_period_start', [$startDate, $endDate])
                                        ->orWhereBetween('roster_period_end', [$startDate, $endDate])
                                        ->orWhere(function ($spanQuery) use ($startDate, $endDate) {
                                            $spanQuery->where('roster_period_start', '<=', $startDate)
                                                ->where('roster_period_end', '>=', $endDate);
                                        });
                                });
                        });
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($rosterAsOIC) {
                $rosterUnitName = $rosterAsOIC->unit;
            }
        }
        
        // Format roster role with unit name (like dashboard: "OIC - Stone age")
        if ($rosterRole && $rosterUnitName) {
            $rosterRole = "{$rosterRole} - {$rosterUnitName}";
        }

        return view('dashboards.reporting-officer.aper-search', compact('officers', 'rosterRole', 'isOICOr2IC'));
    }

    // Countersigning Officer: Search for forms in the command awaiting countersigning
    public function searchCountersigningForms(Request $request)
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $commandId = $officer->present_station;
        if (!$commandId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to a command.');
        }

        // Find forms in command, awaiting CSO, where reporter != current user
        $query = APERForm::where('status', 'COUNTERSIGNING_OFFICER')
            ->where('reporting_officer_id', '!=', $user->id) // Constraint: Not the one who filled it
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            })
            ->with(['officer', 'reportingOfficer.officer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%");
            });
        }

        // Get all matching basic criteria
        $potentialForms = $query->orderBy('updated_at', 'desc')->get();

        // Filter by Rank (CSO >= Reporting Officer)
        $rankComparisonService = app(RankComparisonService::class);
        $forms = $potentialForms->filter(function ($form) use ($officer, $rankComparisonService) {
            // Reporting Officer must exist (it should for this status)
            if (!$form->reportingOfficer || !$form->reportingOfficer->officer)
                return false;

            // Check if user's rank is higher or equal to Reporting Officer's rank
            return $rankComparisonService->isRankHigherOrEqual($officer->id, $form->reportingOfficer->officer->id);
        });

        // Paginate manually
        $page = $request->input('page', 1);
        $perPage = 20;
        $forms = new \Illuminate\Pagination\LengthAwarePaginator(
            $forms->forPage($page, $perPage),
            $forms->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashboards.countersigning-officer.aper-search', compact('forms'));
    }

    // Reporting Officer: Access APER form for an officer (creates form if doesn't exist)
    public function accessForm($officerId)
    {
        $user = auth()->user();
        $reportingOfficer = $user->officer;
        $officer = Officer::findOrFail($officerId);

        if (!$reportingOfficer) {
            return redirect()->back()->with('error', 'Officer record not found.');
        }

        // Get active timeline
        $activeTimeline = APERTimeline::where('is_active', true)->first();

        if (!$activeTimeline) {
            return redirect()->back()->with('error', 'No active APER timeline found.');
        }

        // Validate same command
        if ($reportingOfficer->present_station !== $officer->present_station) {
            return redirect()->back()->with('error', 'You can only create APER forms for officers in your command.');
        }

        $commandId = $reportingOfficer->present_station;
        $formYear = $activeTimeline->year; // Always use APER timeline year for form creation
        $rosterCheckYear = $formYear; // Year to use for roster checks (may fallback to current year)

        // Validate Reporting Officer is OIC or 2IC (unless HRD or Staff Officer)
        if (!$user->hasRole('HRD') && !$user->hasRole('Staff Officer')) {
            $dutyRosterService = app(DutyRosterService::class);
            $isOICOr2IC = $dutyRosterService->isOfficerOICOr2IC($reportingOfficer->id, $commandId, $rosterCheckYear);
            
            // Fallback: Check current year if not OIC/2IC for APER timeline year
            if (!$isOICOr2IC && $activeTimeline->year != date('Y')) {
                $isOICOr2IC = $dutyRosterService->isOfficerOICOr2IC($reportingOfficer->id, $commandId, date('Y'));
                if ($isOICOr2IC) {
                    $rosterCheckYear = date('Y'); // Use current year for roster checks
                }
            }

            if (!$isOICOr2IC) {
                return redirect()->back()->with('error', 'You must be an Officer in Charge (OIC) or Second In Command (2IC) in an approved duty roster to create APER forms.');
            }

            // Validate that the officer is a subordinate in the rosters where reporting officer is OIC/2IC for the given year
            $subordinateIds = $dutyRosterService->getSubordinateIds($reportingOfficer->id, $commandId, $rosterCheckYear);
            
            // Fallback: If no subordinates found for APER timeline year, check current year
            if (empty($subordinateIds) && $activeTimeline->year != date('Y')) {
                $currentYearSubordinates = $dutyRosterService->getSubordinateIds($reportingOfficer->id, $commandId, date('Y'));
                if (!empty($currentYearSubordinates)) {
                    $subordinateIds = $currentYearSubordinates;
                    $rosterCheckYear = date('Y'); // Use current year for roster checks
                }
            }

            if (!in_array($officer->id, $subordinateIds) || $officer->id === $reportingOfficer->id) {
                return redirect()->back()->with('error', 'You can only create APER forms for officers assigned to your duty roster.');
            }

            // Validate rank - Reporting Officer must be same or higher rank
            $rankComparisonService = app(RankComparisonService::class);
            if (!$rankComparisonService->isRankHigherOrEqual($reportingOfficer->id, $officer->id)) {
                return redirect()->back()->with('error', 'You must be of the same rank or higher than the officer you are assessing.');
            }
        }

        // Find or create form (always use APER timeline year for form)
        $form = APERForm::where('officer_id', $officer->id)
            ->where('year', $formYear)
            ->first();

        // Create form if it doesn't exist
        if (!$form) {
            DB::beginTransaction();
            try {
                // Check if officer already has an accepted form for this year
                $acceptedForm = APERForm::where('officer_id', $officer->id)
                    ->where('year', $formYear)
                    ->where('status', 'ACCEPTED')
                    ->first();

                if ($acceptedForm) {
                    return redirect()->back()->with('error', 'This officer already has an accepted APER form for this year.');
                }

                // Create new form
                $form = APERForm::create([
                    'officer_id' => $officer->id,
                    'timeline_id' => $activeTimeline->id,
                    'year' => $formYear,
                    'status' => 'REPORTING_OFFICER',
                    'reporting_officer_id' => $user->id,
                ]);

                DB::commit();

                // Send notification to Reporting Officer
                if ($user->email) {
                    \App\Jobs\SendAPERReportingOfficerAssignedMailJob::dispatch($form, $user);
                }

                Log::info('APER form created by Reporting Officer', [
                    'form_id' => $form->id,
                    'officer_id' => $officer->id,
                    'reporting_officer_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create APER form', [
                    'officer_id' => $officer->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('error', 'Failed to create APER form: ' . $e->getMessage());
            }
        } else {
            // Form exists - check access
            if (!$form->canBeAccessedBy($user)) {
                // If form is submitted and no reporting officer assigned, assign this user
                if ($form->status === 'SUBMITTED' && !$form->reporting_officer_id) {
                    DB::beginTransaction();
                    try {
                        $form->update([
                            'reporting_officer_id' => $user->id,
                            'status' => 'REPORTING_OFFICER',
                        ]);
                        DB::commit();

                        if ($user->email) {
                            \App\Jobs\SendAPERReportingOfficerAssignedMailJob::dispatch($form, $user);
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Failed to assign reporting officer.');
                    }
                } else {
                    return redirect()->back()->with('error', 'You do not have access to this APER form.');
                }
            }

            // If form was rejected and needs reassignment
            if ($form->is_rejected && $form->status === 'REPORTING_OFFICER' && $form->reporting_officer_id !== $user->id) {
                return redirect()->back()->with('error', 'This form has been rejected and needs to be reassigned by HRD or Staff Officer.');
            }
        }

        return view('forms.aper.reporting-officer', compact('form', 'officer'));
    }

    // Reporting Officer: Update APER form
    public function updateReportingOfficer(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->reporting_officer_id !== $user->id || $form->status !== 'REPORTING_OFFICER') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $this->validateReportingOfficerData($request);

        DB::beginTransaction();
        try {
            $form->update($validated);
            DB::commit();
            return redirect()->back()->with('success', 'APER form updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update form: ' . $e->getMessage());
        }
    }

    // Reporting Officer: Complete and forward to Countersigning Officer
    public function completeReportingOfficer($id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->reporting_officer_id !== $user->id || $form->status !== 'REPORTING_OFFICER') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Validate that essential fields are filled
        $requiredFields = [
            'job_understanding_grade' => 'Job Understanding',
            'knowledge_application_grade' => 'Knowledge Application',
            'accomplishment_grade' => 'Accomplishment',
            'judgement_grade' => 'Judgement',
            'work_speed_accuracy_grade' => 'Work Speed & Accuracy',
            'written_expression_grade' => 'Written Expression',
            'oral_expression_grade' => 'Oral Expression',
            'staff_relations_grade' => 'Staff Relations',
            'public_relations_grade' => 'Public Relations',
            'overall_assessment' => 'Overall Assessment',
            'promotability' => 'Promotability'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($form->$field)) {
                $missingFields[] = $label;
            }
        }

        if (!empty($missingFields)) {
            $fieldsList = implode(', ', $missingFields);
            $errorMessage = count($missingFields) === 1 
                ? "Please complete the following required field before forwarding: {$fieldsList}"
                : "Please complete the following required fields before forwarding: {$fieldsList}";
            
            return redirect()->back()->with('error', $errorMessage);
        }

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'COUNTERSIGNING_OFFICER',
                'reporting_officer_completed_at' => now(),
                'reporting_officer_user_id' => $user->id,
            ]);

            DB::commit();

            // Notify potential Countersigning Officers in the command pool
            \App\Jobs\SendAPERCountersigningPoolMailJob::dispatch($form);

            // Redirect to appropriate page based on user role
            if ($user->hasRole('Staff Officer') || $user->hasRole('HRD')) {
                return redirect()->route('staff-officer.aper-forms.reporting-officer.search')
                    ->with('success', 'APER form forwarded to Countersigning Officer.');
            } else {
                return redirect()->route('officer.aper-forms.search-officers')
                    ->with('success', 'APER form forwarded to Countersigning Officer.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete form: ' . $e->getMessage());
        }
    }

    // Countersigning Officer: Access APER form
    public function accessCountersigningForm($id)
    {
        $user = auth()->user();
        $countersigningOfficer = $user->officer;
        $form = APERForm::with(['officer', 'reportingOfficer'])->findOrFail($id);

        if (!$countersigningOfficer) {
            return redirect()->back()->with('error', 'Officer record not found.');
        }

        // Validate same command
        if ($countersigningOfficer->present_station !== $form->officer->present_station) {
            return redirect()->back()->with('error', 'You can only countersign APER forms for officers in your command.');
        }

        // Validate rank - Counter Signing Officer must be same or higher rank than Reporting Officer
        if ($form->reportingOfficer && $form->reportingOfficer->officer) {
            $rankComparisonService = app(RankComparisonService::class);
            if (!$rankComparisonService->isRankHigherOrEqual($countersigningOfficer->id, $form->reportingOfficer->officer->id)) {
                return redirect()->back()->with('error', 'You must be of the same rank or higher than the Reporting Officer to countersign this form.');
            }
        }

        if (!$form->canBeAccessedBy($user)) {
            // If form is ready for countersigning and no countersigning officer assigned, assign this user
            if ($form->status === 'COUNTERSIGNING_OFFICER' && !$form->countersigning_officer_id) {
                DB::beginTransaction();
                try {
                    $form->update([
                        'countersigning_officer_id' => $user->id,
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Failed to assign countersigning officer.');
                }
            } else {
                return redirect()->back()->with('error', 'You do not have access to this APER form.');
            }
        }

        // If form was rejected and needs reassignment
        if ($form->is_rejected && $form->status === 'COUNTERSIGNING_OFFICER' && $form->countersigning_officer_id !== $user->id) {
            return redirect()->back()->with('error', 'This form has been rejected and needs to be reassigned by HRD or Staff Officer.');
        }

        return view('forms.aper.countersigning-officer', compact('form'));
    }

    // Countersigning Officer: Update APER form
    public function updateCountersigningOfficer(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->countersigning_officer_id !== $user->id || $form->status !== 'COUNTERSIGNING_OFFICER') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $this->validateCountersigningOfficerData($request);

        DB::beginTransaction();
        try {
            $form->update($validated);
            DB::commit();
            return redirect()->back()->with('success', 'APER form updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update form: ' . $e->getMessage());
        }
    }

    // Countersigning Officer: Complete and forward to Officer
    public function completeCountersigningOfficer($id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->countersigning_officer_id !== $user->id || $form->status !== 'COUNTERSIGNING_OFFICER') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if (empty($form->countersigning_officer_declaration) || strlen($form->countersigning_officer_declaration) < 50) {
            return redirect()->back()->with('error', 'You must provide a valid countersigning declaration (min 50 chars) before completing.');
        }

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'OFFICER_REVIEW',
                'countersigning_officer_completed_at' => now(),
                'countersigning_officer_user_id' => $user->id,
            ]);

            DB::commit();

            // Send notification to officer (email and app notification)
            if ($form->officer->user) {
                // Email notification
                if ($form->officer->user->email) {
                    \App\Jobs\SendAPERFormReadyForReviewMailJob::dispatch($form);
                }
                
                // App notification
                \App\Models\Notification::create([
                    'user_id' => $form->officer->user->id,
                    'notification_type' => 'APER_FORM_READY_FOR_REVIEW',
                    'title' => 'APER Form Ready for Review',
                    'message' => "Your APER form for {$form->year} has been countersigned and is ready for your review.",
                    'entity_type' => 'APERForm',
                    'entity_id' => $form->id,
                    'is_read' => false,
                ]);
            }

            // Redirect to appropriate page based on user role - avoid redirect loop
            if ($user->hasRole('Staff Officer') || $user->hasRole('HRD')) {
                return redirect()->route('staff-officer.aper-forms.reporting-officer.search')
                    ->with('success', 'APER form forwarded to Officer for review.');
            } else {
                return redirect()->route('officer.aper-forms.countersigning.search')
                    ->with('success', 'APER form forwarded to Officer for review.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete form: ' . $e->getMessage());
        }
    }

    // Officer: View their APER form
    public function show($id)
    {
        $user = auth()->user();
        $form = APERForm::with(['officer', 'timeline', 'reportingOfficer', 'countersigningOfficer'])->findOrFail($id);

        if ($form->officer->user_id !== $user->id && !$form->canBeAccessedBy($user)) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        return view('forms.aper.show', compact('form'));
    }

    // Officer: Update Comments
    public function updateComments(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->officer->user_id !== $user->id || $form->status !== 'OFFICER_REVIEW') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'officer_comments' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $form->update([
                'officer_comments' => $validated['officer_comments'] ?? null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Comments saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save comments: ' . $e->getMessage());
        }
    }

    // Officer: Accept APER form
    public function accept($id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->officer->user_id !== $user->id || $form->status !== 'OFFICER_REVIEW') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'ACCEPTED',
                'accepted_at' => now(),
                'officer_reviewed_at' => now(),
                'officer_signed_at' => now(),
                'is_rejected' => false,
                'rejection_reason' => null,
            ]);

            DB::commit();

            // Send notification to officer
            if ($form->officer->user && $form->officer->user->email) {
                \App\Jobs\SendAPERFormAcceptedMailJob::dispatch($form);
            }

            return redirect()->back()->with('success', 'APER form accepted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to accept form: ' . $e->getMessage());
        }
    }

    // Officer: Reject APER form
    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if ($form->officer->user_id !== $user->id || $form->status !== 'OFFICER_REVIEW') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Get Staff Officer for the command
            $commandId = $form->officer->present_station;
            $staffOfficer = User::whereHas('roles', function ($q) {
                $q->where('name', 'Staff Officer')
                    ->where('user_roles.is_active', true);
            })
                ->whereHas('officer', function ($q) use ($commandId) {
                    $q->where('present_station', $commandId);
                })
                ->first();

            if (!$staffOfficer) {
                // FALLBACK: If no Staff Officer defined for command, assign to an HRD admin
                $staffOfficer = User::whereHas('roles', function ($q) {
                    $q->where('name', 'HRD')
                        ->where('user_roles.is_active', true);
                })->first();
            }

            if (!$staffOfficer) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No Staff Officer or HRD found to review this rejection. Please contact support.');
            }

            $form->update([
                'status' => 'STAFF_OFFICER_REVIEW', // New status for Staff Officer review
                'is_rejected' => true,
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_by_role' => 'OFFICER',
                'rejected_at' => now(),
                'officer_reviewed_at' => now(),
                'staff_officer_id' => $staffOfficer->id,
            ]);

            DB::commit();

            // Send notification to officer
            if ($form->officer->user && $form->officer->user->email) {
                \App\Jobs\SendAPERFormRejectedMailJob::dispatch($form);
            }

            // Send notification to Staff Officer
            if ($staffOfficer->email) {
                \App\Jobs\SendAPERFormRejectedToStaffOfficerMailJob::dispatch($form, $staffOfficer);
            }

            return redirect()->back()->with('success', 'APER form rejected. It has been sent to Staff Officer for review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject APER form', [
                'form_id' => $form->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to reject form: ' . $e->getMessage());
        }
    }

    // Staff Officer: View rejected APER forms pending review
    public function staffOfficerReviewIndex(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Staff Officer')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $staffOfficer = $user->officer;
        if (!$staffOfficer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        $commandId = $staffOfficer->present_station;

        $query = APERForm::with(['officer', 'timeline', 'reportingOfficer', 'countersigningOfficer'])
            ->where('status', 'STAFF_OFFICER_REVIEW')
            ->whereHas('officer', function ($q) use ($commandId) {
                $q->where('present_station', $commandId);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%");
            });
        }

        $forms = $query->orderBy('rejected_at', 'desc')->paginate(20);

        return view('dashboards.staff-officer.aper-review', compact('forms'));
    }

    // Staff Officer: View rejected APER form details
    public function staffOfficerReviewShow($id)
    {
        $user = auth()->user();
        $form = APERForm::with(['officer', 'timeline', 'reportingOfficer', 'countersigningOfficer'])->findOrFail($id);

        if (!$user->hasRole('Staff Officer')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if ($form->status !== 'STAFF_OFFICER_REVIEW') {
            return redirect()->route('staff-officer.aper-forms.review')
                ->with('error', 'This form is not pending Staff Officer review.');
        }

        $staffOfficer = $user->officer;
        if (!$staffOfficer || $staffOfficer->present_station !== $form->officer->present_station) {
            return redirect()->route('staff-officer.aper-forms.review')
                ->with('error', 'You can only review APER forms for officers in your command.');
        }

        return view('dashboards.staff-officer.aper-review-show', compact('form'));
    }

    // Staff Officer: Reject APER form (finalize - HRD can access, marks awarded)
    public function staffOfficerReject(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if (!$user->hasRole('Staff Officer')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Only allow if form is in STAFF_OFFICER_REVIEW status
        if ($form->status !== 'STAFF_OFFICER_REVIEW') {
            return redirect()->back()->with('error', 'This form is not pending Staff Officer review.');
        }

        // Validate same command
        $staffOfficer = $user->officer;
        if (!$staffOfficer || $staffOfficer->present_station !== $form->officer->present_station) {
            return redirect()->back()->with('error', 'You can only review APER forms for officers in your command.');
        }

        $validated = $request->validate([
            'staff_officer_rejection_reason' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'FINALIZED', // Final status - HRD can access
                'is_rejected' => true,
                'staff_officer_rejection_reason' => $validated['staff_officer_rejection_reason'] ?? null,
                'staff_officer_id' => $user->id,
                'finalized_at' => now(),
            ]);

            DB::commit();

            // Send notification to officer
            if ($form->officer->user && $form->officer->user->email) {
                \App\Jobs\SendAPERFormFinalizedMailJob::dispatch($form);
            }

            return redirect()->back()->with('success', 'APER form finalized. HRD can now access this form and marks will be awarded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to finalize form: ' . $e->getMessage());
        }
    }

    // HRD/Staff Officer: Reassign Reporting Officer
    public function reassignReportingOfficer(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if (!$user->hasRole('HRD') && !$user->hasRole('Staff Officer')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if (!$form->canBeReassigned()) {
            return redirect()->back()->with('error', 'Form cannot be reassigned at this stage.');
        }

        $validated = $request->validate([
            'reporting_officer_id' => 'required|exists:users,id',
        ]);

        // Validate new Reporting Officer is OIC/2IC (unless HRD)
        if (!$user->hasRole('HRD')) {
            $newReportingOfficer = User::find($validated['reporting_officer_id']);
            if ($newReportingOfficer && $newReportingOfficer->officer) {
                $commandId = $form->officer->present_station;
                $year = $form->year;

                $dutyRosterService = app(DutyRosterService::class);
                $isOICOr2IC = $dutyRosterService->isOfficerOICOr2IC($newReportingOfficer->officer->id, $commandId, $year);

                if (!$isOICOr2IC) {
                    return redirect()->back()->with('error', 'The selected Reporting Officer must be an OIC or 2IC in an approved duty roster.');
                }

                // Validate rank
                $rankComparisonService = app(RankComparisonService::class);
                if (!$rankComparisonService->isRankHigherOrEqual($newReportingOfficer->officer->id, $form->officer->id)) {
                    return redirect()->back()->with('error', 'The selected Reporting Officer must be of the same rank or higher than the officer being assessed.');
                }
            }
        }

        DB::beginTransaction();
        try {
            $form->update([
                'reporting_officer_id' => $validated['reporting_officer_id'],
                'status' => 'REPORTING_OFFICER',
                'is_rejected' => false,
                'rejection_reason' => null,
                'staff_officer_id' => $user->id,
            ]);

            DB::commit();

            // Send notification to newly assigned reporting officer
            $reportingOfficer = User::find($validated['reporting_officer_id']);
            if ($reportingOfficer && $reportingOfficer->email) {
                \App\Jobs\SendAPERReportingOfficerAssignedMailJob::dispatch($form, $reportingOfficer);
            }

            return redirect()->back()->with('success', 'Reporting Officer reassigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reassign reporting officer: ' . $e->getMessage());
        }
    }

    // HRD/Staff Officer: Reassign Countersigning Officer
    public function reassignCountersigningOfficer(Request $request, $id)
    {
        $user = auth()->user();
        $form = APERForm::findOrFail($id);

        if (!$user->hasRole('HRD') && !$user->hasRole('Staff Officer')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if (!$form->canBeReassigned()) {
            return redirect()->back()->with('error', 'Form cannot be reassigned at this stage.');
        }

        $validated = $request->validate([
            'countersigning_officer_id' => 'required|exists:users,id',
        ]);

        // Validate new Counter Signing Officer rank (must be same or higher than Reporting Officer)
        $newCountersigningOfficer = User::find($validated['countersigning_officer_id']);
        if ($newCountersigningOfficer && $newCountersigningOfficer->officer && $form->reportingOfficer && $form->reportingOfficer->officer) {
            $rankComparisonService = app(RankComparisonService::class);
            if (!$rankComparisonService->isRankHigherOrEqual($newCountersigningOfficer->officer->id, $form->reportingOfficer->officer->id)) {
                return redirect()->back()->with('error', 'The selected Counter Signing Officer must be of the same rank or higher than the Reporting Officer.');
            }

            // Validate same command
            if ($newCountersigningOfficer->officer->present_station !== $form->officer->present_station) {
                return redirect()->back()->with('error', 'The selected Counter Signing Officer must be in the same command.');
            }
        }

        DB::beginTransaction();
        try {
            $form->update([
                'countersigning_officer_id' => $validated['countersigning_officer_id'],
                'status' => 'COUNTERSIGNING_OFFICER',
                'is_rejected' => false,
                'rejection_reason' => null,
                'staff_officer_id' => $user->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Countersigning Officer reassigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reassign countersigning officer: ' . $e->getMessage());
        }
    }

    // HRD: View all APER forms
    public function hrdIndex(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('HRD')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = APERForm::with(['officer', 'timeline', 'reportingOfficer', 'countersigningOfficer']);

        // By default, show submitted forms and above (not drafts)
        if (!$request->filled('status')) {
            $query->whereIn('status', ['SUBMITTED', 'REPORTING_OFFICER', 'COUNTERSIGNING_OFFICER', 'OFFICER_REVIEW', 'ACCEPTED', 'REJECTED']);
        } elseif ($request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('officer', function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%");
            });
        }

        $forms = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('dashboards.hrd.aper-forms', compact('forms'));
    }

    // Private helper methods
    private function validateFormData(Request $request)
    {
        return $request->validate([
            // Part 1
            'service_number' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:255',
            'forenames' => 'nullable|string|max:255',
            'department_area' => 'nullable|string|max:255',
            'cadre' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'date_of_first_appointment' => 'nullable|date',
            'date_of_present_appointment' => 'nullable|date',
            'rank' => 'nullable|string|max:255',
            'hapass' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'state_of_origin' => 'nullable|string|max:255',
            'qualifications' => 'nullable|array',
            // Part 2 - Leave Records
            'sick_leave_records' => 'nullable|array',
            'maternity_leave_records' => 'nullable|array',
            'annual_casual_leave_records' => 'nullable|array',
            // Part 2 - Targets
            'division_targets' => 'nullable|array',
            'individual_targets' => 'nullable|array',
            'project_cost' => 'nullable|string|max:255',
            'completion_time' => 'nullable|string|max:255',
            'quantity_conformity' => 'nullable|string',
            'quality_conformity' => 'nullable|string',
            // Part 2 - Job Description
            'main_duties' => 'nullable|string',
            'joint_discussion' => 'nullable|string|max:10',
            'properly_equipped' => 'nullable|string|max:10',
            'equipment_difficulties' => 'nullable|string',
            'difficulties_encountered' => 'nullable|string',
            'supervisor_assistance_methods' => 'nullable|string',
            'periodic_review' => 'nullable|string|max:255',
            'performance_measure_up' => 'nullable|string|max:10',
            'solution_admonition' => 'nullable|string',
            'final_evaluation' => 'nullable|string',
            'adhoc_duties' => 'nullable|string',
            'adhoc_affected_duties' => 'nullable|string|max:10',
            'schedule_duty_from' => 'nullable|date',
            'schedule_duty_to' => 'nullable|date',
            // Training
            'training_courses' => 'nullable|array',
            'training_enhanced_performance' => 'nullable|string',
            // Job Performance
            'satisfactory_jobs' => 'nullable|string',
            'success_failure_causes' => 'nullable|string',
            'training_needs' => 'nullable|string',
            'effective_use_capabilities' => 'nullable|string|max:10',
            'better_use_abilities' => 'nullable|string',
            'job_satisfaction' => 'nullable|string|max:10',
            'job_satisfaction_causes' => 'nullable|string',
        ]);
    }

    private function validateReportingOfficerData(Request $request)
    {
        $rules = [
            'targets_agreed' => 'nullable|string|max:10|in:YES,NO',
            'targets_agreement_details' => 'nullable|string|max:2000',
            'duties_agreed' => 'nullable|string|max:10|in:YES,NO',
            'duties_agreement_details' => 'nullable|string|max:2000',
            // Job Assessment grades and comments
            'job_understanding_grade' => 'nullable|string|max:1|in:A,B,C,D,E,F',
            'job_understanding_comment' => 'nullable|string|max:2000',
            'knowledge_application_grade' => 'nullable|string|max:1',
            'knowledge_application_comment' => 'nullable|string',
            'accomplishment_grade' => 'nullable|string|max:1',
            'accomplishment_comment' => 'nullable|string',
            'judgement_grade' => 'nullable|string|max:1',
            'judgement_comment' => 'nullable|string',
            'written_expression_grade' => 'nullable|string|max:1',
            'written_expression_comment' => 'nullable|string',
            'oral_expression_grade' => 'nullable|string|max:1',
            'oral_expression_comment' => 'nullable|string',
            'staff_relations_grade' => 'nullable|string|max:1',
            'staff_relations_comment' => 'nullable|string',
            'public_relations_grade' => 'nullable|string|max:1',
            'public_relations_comment' => 'nullable|string',
            'staff_management_grade' => 'nullable|string|max:1',
            'staff_management_comment' => 'nullable|string',
            // Quality of Work
            'quality_of_work_grade' => 'nullable|string|max:1',
            'quality_of_work_comment' => 'nullable|string',
            'productivity_grade' => 'nullable|string|max:1',
            'productivity_comment' => 'nullable|string',
            'effective_use_of_data_grade' => 'nullable|string|max:1',
            'effective_use_of_data_comment' => 'nullable|string',
            'initiative_grade' => 'nullable|string|max:1',
            'initiative_comment' => 'nullable|string',
            // Character Traits
            'dependability_grade' => 'nullable|string|max:1',
            'dependability_comment' => 'nullable|string',
            'loyalty_grade' => 'nullable|string|max:1',
            'loyalty_comment' => 'nullable|string',
            'honesty_grade' => 'nullable|string|max:1',
            'honesty_comment' => 'nullable|string',
            'reliability_under_pressure_grade' => 'nullable|string|max:1',
            'reliability_under_pressure_comment' => 'nullable|string',
            'sense_of_responsibility_grade' => 'nullable|string|max:1',
            'sense_of_responsibility_comment' => 'nullable|string',
            'appearance_grade' => 'nullable|string|max:1',
            'appearance_comment' => 'nullable|string',
            // Work Habits
            'punctuality_grade' => 'nullable|string|max:1',
            'punctuality_comment' => 'nullable|string',
            'attendance_grade' => 'nullable|string|max:1',
            'attendance_comment' => 'nullable|string',
            'drive_determination_grade' => 'nullable|string|max:1',
            'drive_determination_comment' => 'nullable|string',
            'resource_utilization_grade' => 'nullable|string|max:1',
            'resource_utilization_comment' => 'nullable|string',
            // Sanctions and Rewards
            'disciplinary_action' => 'nullable|string|max:10',
            'disciplinary_action_details' => 'nullable|string',
            'special_commendation' => 'nullable|string|max:10',
            'special_commendation_details' => 'nullable|string',
            // Leadership
            'encourage_standards_grade' => 'nullable|string|max:1',
            'encourage_standards_comment' => 'nullable|string',
            'train_subordinates_grade' => 'nullable|string|max:1',
            'train_subordinates_comment' => 'nullable|string',
            'good_example_grade' => 'nullable|string|max:1',
            'good_example_comment' => 'nullable|string',
            'suggestions_improvements_grade' => 'nullable|string|max:1',
            'suggestions_improvements_comment' => 'nullable|string',
            // Overall Assessment
            'overall_assessment' => 'nullable|string|max:1|in:A,B,C,D,E,F',
            'training_needs_assessment' => 'nullable|string|max:2000',
            'general_remarks' => 'nullable|string|max:2000',
            'suggest_different_job' => 'nullable|string|max:10|in:YES,NO',
            'different_job_details' => 'nullable|string|max:2000',
            'suggest_transfer' => 'nullable|string|max:10|in:YES,NO',
            'transfer_details' => 'nullable|string|max:2000',
            'promotability' => 'nullable|string|max:1|in:A,B,C,D,E,F',
            'reporting_officer_declaration' => 'nullable|string|max:2000',
        ];

        // Add validation for all grade fields
        $gradeFields = [
            'job_understanding',
            'knowledge_application',
            'accomplishment',
            'judgement',
            'work_speed_accuracy',
            'written_expression',
            'oral_expression',
            'staff_relations',
            'public_relations',
            'staff_management',
            'quality_of_work',
            'productivity',
            'effective_use_of_data',
            'initiative',
            'dependability',
            'loyalty',
            'honesty',
            'reliability_under_pressure',
            'sense_of_responsibility',
            'appearance',
            'punctuality',
            'attendance',
            'drive_determination',
            'resource_utilization',
            'encourage_standards',
            'train_subordinates',
            'good_example',
            'suggestions_improvements'
        ];

        foreach ($gradeFields as $field) {
            $rules[$field . '_grade'] = 'nullable|string|max:1|in:A,B,C,D,E,F';
            $rules[$field . '_comment'] = 'nullable|string|max:2000';
        }

        return $request->validate($rules);
    }

    private function validateCountersigningOfficerData(Request $request)
    {
        return $request->validate([
            'countersigning_officer_declaration' => 'required|string|min:50|max:2000',
        ], [
            'countersigning_officer_declaration.required' => 'Declaration is required.',
            'countersigning_officer_declaration.min' => 'Declaration must be at least 50 characters.',
        ]);
    }

    // PDF Export
    public function exportPDF($id)
    {
        $form = APERForm::with(['officer', 'timeline', 'reportingOfficer', 'countersigningOfficer'])->findOrFail($id);

        // Check access
        $user = auth()->user();
        if (!$form->canBeAccessedBy($user) && $form->officer->user_id !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('forms.aper.pdf', compact('form'));
        return $pdf->download("aper-form-{$form->year}-{$form->officer->service_number}.pdf");
    }
}

