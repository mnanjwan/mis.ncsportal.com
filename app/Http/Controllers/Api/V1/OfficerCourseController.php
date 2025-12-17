<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\OfficerCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficerCourseController extends BaseController
{
    /**
     * List officer courses
     */
    public function index(Request $request): JsonResponse
    {
        $query = OfficerCourse::with('officer');

        if ($request->has('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        }

        if ($request->has('course_type')) {
            $query->where('course_type', $request->course_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $courses = $query->paginate($perPage);

        return $this->paginatedResponse(
            $courses->items(),
            [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
            ]
        );
    }

    /**
     * Create officer course record (HRD/Staff Officer)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['HRD', 'Staff Officer'])) {
            return $this->errorResponse(
                'Only HRD or Staff Officer can create course records',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'course_name' => 'required|string|max:255',
            'course_type' => 'required|string',
            'institution' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'certificate_url' => 'nullable|string',
        ]);

        $course = OfficerCourse::create([
            'officer_id' => $request->officer_id,
            'course_name' => $request->course_name,
            'course_type' => $request->course_type,
            'institution' => $request->institution,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'certificate_url' => $request->certificate_url,
            'created_by' => $user->id,
        ]);

        return $this->successResponse([
            'id' => $course->id,
            'course_name' => $course->course_name,
        ], 'Course record created successfully', 201);
    }

    /**
     * Get course details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $course = OfficerCourse::with('officer')->findOrFail($id);

        return $this->successResponse($course);
    }
}

