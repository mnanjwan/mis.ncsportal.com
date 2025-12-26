<?php

namespace App\Http\Controllers;

use App\Models\APERForm;
use App\Models\APERTimeline;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // Officer: Create new APER form
    public function create()
    {
        $user = auth()->user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('dashboard')->with('error', 'Officer record not found.');
        }

        // Get active timeline
        $activeTimeline = APERTimeline::where('is_active', true)->first();

        if (!$activeTimeline) {
            return redirect()->route('officer.aper-forms')
                ->with('error', 'No active APER timeline found. Please contact HRD.');
        }

        // Check if form already exists for this year
        $existingForm = APERForm::where('officer_id', $officer->id)
            ->where('year', $activeTimeline->year)
            ->first();

        if ($existingForm) {
            return redirect()->route('officer.aper-forms.show', $existingForm->id)
                ->with('info', 'APER form for this year already exists.');
        }

        // Check if officer already has an accepted form for this year
        $acceptedForm = APERForm::where('officer_id', $officer->id)
            ->where('year', $activeTimeline->year)
            ->where('status', 'ACCEPTED')
            ->first();

        if ($acceptedForm) {
            return redirect()->route('officer.aper-forms.show', $acceptedForm->id)
                ->with('info', 'You already have an accepted APER form for this year.');
        }

        // Check if timeline is active
        if (!$activeTimeline->can_submit) {
            return redirect()->route('officer.aper-forms')
                ->with('error', 'APER form submission period has ended.');
        }

        // Load officer with relationships
        $officer->load(['presentStation.zone', 'courses', 'leaveApplications.leaveType']);

        // Get zone from present station
        $zone = $officer->presentStation && $officer->presentStation->zone 
            ? $officer->presentStation->zone->name 
            : null;

        // Determine cadre from discipline or other field (you may need to adjust based on your data structure)
        // For now, we'll check if there's a discipline field that indicates GD/SS
        $cadre = null;
        if ($officer->discipline) {
            // You may need to map discipline to GD/SS based on your business logic
            // This is a placeholder - adjust based on your actual data
            $cadre = str_contains(strtoupper($officer->discipline), 'GENERAL') ? 'GD' : 'SS';
        }

        // Parse qualifications from officer profile
        $qualifications = [];
        if ($officer->entry_qualification) {
            $qualifications[] = [
                'qualification' => $officer->entry_qualification,
                'year' => $officer->date_of_first_appointment ? $officer->date_of_first_appointment->format('Y') : null
            ];
        }
        if ($officer->additional_qualification) {
            // Try to parse additional qualifications if stored as JSON or comma-separated
            $additionalQuals = json_decode($officer->additional_qualification, true);
            if (is_array($additionalQuals)) {
                $qualifications = array_merge($qualifications, $additionalQuals);
            } else {
                $qualifications[] = [
                    'qualification' => $officer->additional_qualification,
                    'year' => null
                ];
            }
        }

        // Fetch leave records for the year
        $yearStart = \Carbon\Carbon::create($activeTimeline->year, 1, 1);
        $yearEnd = \Carbon\Carbon::create($activeTimeline->year, 12, 31);
        
        $leaveApplications = $officer->leaveApplications()
            ->whereBetween('start_date', [$yearStart, $yearEnd])
            ->orWhereBetween('end_date', [$yearStart, $yearEnd])
            ->orWhere(function($query) use ($yearStart, $yearEnd) {
                $query->where('start_date', '<=', $yearStart)
                      ->where('end_date', '>=', $yearEnd);
            })
            ->with('leaveType')
            ->get();

        // Organize leave records by type
        $sickLeaveRecords = [];
        $maternityLeaveRecords = [];
        $annualCasualLeaveRecords = [];

        foreach ($leaveApplications as $leave) {
            $leaveTypeName = strtolower($leave->leaveType->name ?? '');
            $record = [
                'from' => $leave->start_date->format('Y-m-d'),
                'to' => $leave->end_date->format('Y-m-d'),
                'days' => $leave->number_of_days ?? $leave->start_date->diffInDays($leave->end_date) + 1,
            ];

            if (str_contains($leaveTypeName, 'sick') || str_contains($leaveTypeName, 'hospital')) {
                $record['type'] = str_contains($leaveTypeName, 'hospital') ? 'Hospitalisation' : 'Sick Leave';
                $sickLeaveRecords[] = $record;
            } elseif (str_contains($leaveTypeName, 'maternity')) {
                $maternityLeaveRecords[] = $record;
            } elseif (str_contains($leaveTypeName, 'annual') || str_contains($leaveTypeName, 'casual')) {
                $annualCasualLeaveRecords[] = $record;
            }
        }

        // Fetch training courses since appointment
        $trainingCourses = $officer->courses()
            ->where('start_date', '>=', $officer->date_of_first_appointment)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($course) {
                return [
                    'type' => $course->course_name . ($course->course_type ? ' (' . $course->course_type . ')' : ''),
                    'where' => 'NCS', // You may want to add a location field to OfficerCourse
                    'from' => $course->start_date ? $course->start_date->format('Y-m-d') : null,
                    'to' => $course->end_date ? $course->end_date->format('Y-m-d') : null,
                ];
            })
            ->toArray();

        // Pre-fill form with officer data
        $formData = [
            'service_number' => $officer->service_number,
            'surname' => $officer->surname,
            'forenames' => $officer->initials,
            'department_area' => $officer->presentStation ? $officer->presentStation->name : null,
            'cadre' => $cadre,
            'unit' => $officer->unit,
            'zone' => $zone,
            'date_of_first_appointment' => $officer->date_of_first_appointment,
            'date_of_present_appointment' => $officer->date_of_present_appointment,
            'rank' => $officer->substantive_rank,
            'date_of_birth' => $officer->date_of_birth,
            'state_of_origin' => $officer->state_of_origin,
            'qualifications' => $qualifications,
            'sick_leave_records' => $sickLeaveRecords,
            'maternity_leave_records' => $maternityLeaveRecords,
            'annual_casual_leave_records' => $annualCasualLeaveRecords,
            'training_courses' => $trainingCourses,
            'period_from' => $yearStart->format('Y-m-d'),
            'period_to' => $yearEnd->format('Y-m-d'),
        ];

        return view('forms.aper.create', compact('activeTimeline', 'officer', 'formData'));
    }

    // Officer: Store new APER form
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
            $validated['sick_leave_records'] = array_filter($request->sick_leave_records ?? [], function($record) {
                return !empty($record['type']) || !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('maternity_leave_records')) {
            $validated['maternity_leave_records'] = array_filter($request->maternity_leave_records ?? [], function($record) {
                return !empty($record['from']) || !empty($record['to']);
            });
        }
        if ($request->has('annual_casual_leave_records')) {
            $validated['annual_casual_leave_records'] = array_filter($request->annual_casual_leave_records ?? [], function($record) {
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
            $validated['training_courses'] = array_filter($request->training_courses ?? [], function($course) {
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
        
        // Check if user has Reporting Officer role
        if (!$user->hasRole('Reporting Officer') && !$user->hasRole('HRD') && !$user->hasRole('Staff Officer')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = Officer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                  ->orWhere('initials', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $officers = $query->orderBy('surname')->paginate(20);

        return view('dashboards.reporting-officer.aper-search', compact('officers'));
    }

    // Reporting Officer: Access APER form for an officer
    public function accessForm($officerId)
    {
        $user = auth()->user();
        $officer = Officer::findOrFail($officerId);

        // Get active timeline
        $activeTimeline = APERTimeline::where('is_active', true)->first();

        if (!$activeTimeline) {
            return redirect()->back()->with('error', 'No active APER timeline found.');
        }

        // Find or create form
        $form = APERForm::where('officer_id', $officer->id)
            ->where('year', $activeTimeline->year)
            ->first();

        if (!$form) {
            return redirect()->back()->with('error', 'APER form not found for this officer.');
        }

        // Check if form can be accessed by this user
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

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'COUNTERSIGNING_OFFICER',
                'reporting_officer_completed_at' => now(),
                'reporting_officer_user_id' => $user->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'APER form forwarded to Countersigning Officer.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete form: ' . $e->getMessage());
        }
    }

    // Countersigning Officer: Access APER form
    public function accessCountersigningForm($id)
    {
        $user = auth()->user();
        $form = APERForm::with('officer')->findOrFail($id);

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

        DB::beginTransaction();
        try {
            $form->update([
                'status' => 'OFFICER_REVIEW',
                'countersigning_officer_completed_at' => now(),
                'countersigning_officer_user_id' => $user->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'APER form forwarded to Officer for review.');
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
                'is_rejected' => false,
                'rejection_reason' => null,
            ]);

            DB::commit();
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
            // Determine which role to send back to
            $previousStatus = 'REPORTING_OFFICER'; // Default to reporting officer
            
            $form->update([
                'status' => $previousStatus,
                'is_rejected' => true,
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_by_role' => 'OFFICER',
                'rejected_at' => now(),
                'officer_reviewed_at' => now(),
                // Reset countersigning officer so it can be reassigned
                'countersigning_officer_id' => null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'APER form rejected. It has been sent back to Reporting Officer for revision.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject form: ' . $e->getMessage());
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
            $query->whereHas('officer', function($q) use ($search) {
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
            'work_speed_accuracy_grade' => 'nullable|string|max:1',
            'work_speed_accuracy_comment' => 'nullable|string',
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
            'job_understanding', 'knowledge_application', 'accomplishment', 'judgement', 
            'work_speed_accuracy', 'written_expression', 'oral_expression', 'staff_relations',
            'public_relations', 'staff_management', 'quality_of_work', 'productivity',
            'effective_use_of_data', 'initiative', 'dependability', 'loyalty', 'honesty',
            'reliability_under_pressure', 'sense_of_responsibility', 'appearance',
            'punctuality', 'attendance', 'drive_determination', 'resource_utilization',
            'encourage_standards', 'train_subordinates', 'good_example', 'suggestions_improvements'
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

        // For now, return a view. To generate actual PDF, install: composer require barryvdh/laravel-dompdf
        // Then use: return PDF::loadView('forms.aper.pdf', compact('form'))->download("aper-form-{$form->year}.pdf");
        
        return view('forms.aper.pdf', compact('form'));
    }
}

