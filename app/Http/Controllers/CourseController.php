<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficerCourse;
use App\Models\Officer;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = OfficerCourse::with(['officer', 'nominatedBy']);

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
        
        return view('dashboards.hrd.courses', compact('courses'));
    }

    public function create()
    {
        $officers = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->orderBy('surname')
            ->get();
        
        return view('forms.course.create', compact('officers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'course_name' => 'required|string|max:255',
            'course_type' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            OfficerCourse::create([
                'officer_id' => $validated['officer_id'],
                'course_name' => $validated['course_name'],
                'course_type' => $validated['course_type'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'is_completed' => false,
                'nominated_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('hrd.courses')
                ->with('success', 'Officer nominated for course successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to nominate officer: ' . $e->getMessage());
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
        
        return view('forms.course.edit', compact('course', 'officers'));
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
}

