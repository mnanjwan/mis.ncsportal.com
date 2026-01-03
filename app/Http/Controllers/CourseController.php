<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficerCourse;
use App\Models\Officer;
use App\Models\Course;
use App\Services\NotificationService;
use Carbon\Carbon;

class CourseController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = OfficerCourse::with(['officer', 'nominatedBy']);

        // Filter by status tab
        $tab = $request->get('tab', 'all'); // 'all', 'in_progress', 'completed'
        if ($tab === 'in_progress') {
            $query->where('is_completed', false);
        } elseif ($tab === 'completed') {
            $query->where('is_completed', true);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'start_date');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $sortableColumns = [
            'officer' => function($query, $order) {
                $query->leftJoin('officers', 'officer_courses.officer_id', '=', 'officers.id')
                      ->orderBy('officers.surname', $order);
            },
            'course_name' => 'course_name',
            'course_type' => 'course_type',
            'start_date' => 'start_date',
            'status' => 'is_completed',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'start_date';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $courses = $query->select('officer_courses.*')->paginate(20)->withQueryString();
        
        return view('dashboards.hrd.courses', compact('courses', 'tab'));
    }

    public function create()
    {
        $officers = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->orderBy('surname')
            ->get();
        
        // Get active courses for dropdown
        $courses = \App\Models\Course::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('forms.course.create', compact('officers', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'officer_ids' => 'required|array|min:1',
            'officer_ids.*' => 'required|exists:officers,id',
            'course_name' => 'required|string|max:255',
            'course_name_custom' => 'nullable|string|max:255',
            'course_type' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $officerIds = $validated['officer_ids'];
            
            // Handle course_name: if it's "__NEW__", use course_name_custom instead
            $courseName = $validated['course_name'];
            if ($courseName === '__NEW__') {
                if (empty($validated['course_name_custom'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please enter a course name.');
                }
                $courseName = trim($validated['course_name_custom']);
                
                // Create the course in the master Course table if it doesn't exist
                Course::firstOrCreate(
                    ['name' => $courseName],
                    ['is_active' => true]
                );
            }
            
            $createdCount = 0;

            foreach ($officerIds as $officerId) {
                $course = OfficerCourse::create([
                    'officer_id' => $officerId,
                    'course_name' => $courseName,
                    'course_type' => $validated['course_type'] ?? null,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? null,
                    'is_completed' => false,
                    'nominated_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Dispatch job to send notification and email asynchronously
                \App\Jobs\SendCourseNominationNotificationJob::dispatch($course);
                $createdCount++;
            }

            $message = $createdCount === 1 
                ? 'Officer nominated for course successfully!'
                : "{$createdCount} officers nominated for course successfully!";

            return redirect()->route('hrd.courses')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to nominate officers: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $course = OfficerCourse::with(['officer.presentStation', 'nominatedBy'])
            ->findOrFail($id);
        
        return view('dashboards.hrd.course-show', compact('course'));
    }

    public function edit($id)
    {
        $course = OfficerCourse::findOrFail($id);
        $officers = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->orderBy('surname')
            ->get();
        
        // Get active courses for dropdown
        $courses = \App\Models\Course::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('forms.course.edit', compact('course', 'officers', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $course = OfficerCourse::findOrFail($id);
        
        $validated = $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'course_name' => 'required|string|max:255',
            'course_type' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $course->update([
                'officer_id' => $validated['officer_id'],
                'course_name' => $validated['course_name'],
                'course_type' => $validated['course_type'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('hrd.courses.show', $id)
                ->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update course: ' . $e->getMessage());
        }
    }

    public function markComplete(Request $request, $id)
    {
        $course = OfficerCourse::findOrFail($id);
        
        $validated = $request->validate([
            'completion_date' => 'required|date|after_or_equal:start_date',
            'certificate_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $course->update([
                'is_completed' => true,
                'completion_date' => $validated['completion_date'],
                'certificate_url' => $validated['certificate_url'] ?? null,
                'notes' => $validated['notes'] ?? $course->notes,
            ]);

            // Course completion goes directly into officer's record
            // The relationship already exists, so it's automatically linked

            // Send notification to officer
            $this->notificationService->notifyCourseCompleted($course);

            return redirect()->route('hrd.courses.show', $id)
                ->with('success', 'Course marked as completed! This has been recorded in the officer\'s record.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to mark course as complete: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $course = OfficerCourse::findOrFail($id);
            
            // Only allow deletion if course is not completed
            if ($course->is_completed) {
                return redirect()->route('hrd.courses')
                    ->with('error', 'Cannot delete completed courses. They are part of the officer\'s permanent record.');
            }
            
            $course->delete();
            
            return redirect()->route('hrd.courses')
                ->with('success', 'Course nomination deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('hrd.courses')
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }

    // Print Course Nominations grouped by course
    public function print(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('HRD')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Get tab filter (all, in_progress, completed)
        $tab = $request->get('tab', 'all');

        // Get course name filter
        $courseName = $request->get('course_name');

        // Get date range filters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Build query
        $query = OfficerCourse::with(['officer.presentStation'])
            ->whereHas('officer', function($q) {
                $q->where('is_active', true)
                  ->where('is_deceased', false);
            });

        // Apply course name filter if provided
        if ($courseName) {
            $query->where('course_name', $courseName);
        }

        // Apply tab filter (status filter)
        if ($tab === 'in_progress') {
            $query->where('is_completed', false);
        } elseif ($tab === 'completed') {
            $query->where('is_completed', true);
        }
        // 'all' tab doesn't need additional filtering

        // Apply date range filters if provided
        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }

        // Get all course nominations grouped by course name
        $courseNominations = $query
            ->orderBy('course_name')
            ->orderBy('officer_id')
            ->get()
            ->groupBy('course_name');

        // Format data for print
        $printData = [];
        foreach ($courseNominations as $courseName => $nominations) {
            $courseData = [
                'course_name' => $courseName,
                'officers' => []
            ];

            foreach ($nominations as $index => $nomination) {
                $courseData['officers'][] = [
                    'serial_number' => $index + 1,
                    'service_number' => $nomination->officer->service_number ?? 'N/A',
                    'rank' => $nomination->officer->substantive_rank ?? 'N/A',
                    'name' => ($nomination->officer->initials ?? '') . ' ' . ($nomination->officer->surname ?? ''),
                    'remarks' => '', // Empty as per image
                ];
            }

            $printData[] = $courseData;
        }

        return view('prints.course-nominations', compact('printData', 'startDate', 'endDate', 'tab', 'courseName'));
    }
}

