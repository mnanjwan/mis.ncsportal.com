<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\OfficerCourse;
use Illuminate\Http\Request;

class CourseManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Course::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $sortableColumns = [
            'name' => 'name',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'name';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

        $query->orderBy($column, $order);

        $courses = $query->paginate(20)->withQueryString();

        return view('dashboards.hrd.course-management.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.hrd.course-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            Course::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('hrd.course-management.index')
                ->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $course = Course::findOrFail($id);
        return view('dashboards.hrd.course-management.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return view('dashboards.hrd.course-management.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $course->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return redirect()->route('hrd.course-management.index')
                ->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update course: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $course = Course::findOrFail($id);

            // Check if course is being used
            $usageCount = OfficerCourse::where('course_name', $course->name)->count();
            if ($usageCount > 0) {
                return redirect()->route('hrd.course-management.index')
                    ->with('error', "Cannot delete course. It is being used by {$usageCount} course nomination(s).");
            }

            $course->delete();

            return redirect()->route('hrd.course-management.index')
                ->with('success', 'Course deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('hrd.course-management.index')
                ->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }
}
