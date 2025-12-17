<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = LeaveType::with('createdBy')
            ->withCount('leaveApplications as applications_count');

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $sortableColumns = [
            'name' => 'name',
            'code' => 'code',
            'duration' => function($query, $order) {
                $query->orderByRaw("COALESCE(max_duration_days, max_duration_months * 30) " . strtoupper($order));
            },
            'max_per_year' => 'max_occurrences_per_year',
            'applications' => 'applications_count',
            'status' => 'is_active',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'name';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        $leaveTypes = $query->paginate(20)->withQueryString();
        
        return view('dashboards.hrd.leave-types', compact('leaveTypes'));
    }

    public function create()
    {
        return view('forms.leave-type.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'code' => 'required|string|max:100|unique:leave_types,code',
            'max_duration_days' => 'nullable|integer|min:0|max:365',
            'max_duration_months' => 'nullable|integer|min:0|max:12',
            'max_occurrences_per_year' => 'nullable|integer|min:0|max:100',
            'requires_medical_certificate' => 'boolean',
            'requires_approval_level' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Ensure at least one duration is set
        if (empty($validated['max_duration_days']) && empty($validated['max_duration_months'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Either max duration days or months must be specified.');
        }

        try {
            LeaveType::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'max_duration_days' => $validated['max_duration_days'] ?? null,
                'max_duration_months' => $validated['max_duration_months'] ?? null,
                'max_occurrences_per_year' => $validated['max_occurrences_per_year'] ?? null,
                'requires_medical_certificate' => $request->has('requires_medical_certificate') ? true : false,
                'requires_approval_level' => $validated['requires_approval_level'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('hrd.leave-types')
                ->with('success', 'Leave type created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create leave type: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $leaveType = LeaveType::findOrFail($id);
        return view('forms.leave-type.form', compact('leaveType'));
    }

    public function update(Request $request, $id)
    {
        $leaveType = LeaveType::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name,' . $id,
            'code' => 'required|string|max:100|unique:leave_types,code,' . $id,
            'max_duration_days' => 'nullable|integer|min:0|max:365',
            'max_duration_months' => 'nullable|integer|min:0|max:12',
            'max_occurrences_per_year' => 'nullable|integer|min:0|max:100',
            'requires_medical_certificate' => 'boolean',
            'requires_approval_level' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Ensure at least one duration is set
        if (empty($validated['max_duration_days']) && empty($validated['max_duration_months'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Either max duration days or months must be specified.');
        }

        try {
            $leaveType->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'max_duration_days' => $validated['max_duration_days'] ?? null,
                'max_duration_months' => $validated['max_duration_months'] ?? null,
                'max_occurrences_per_year' => $validated['max_occurrences_per_year'] ?? null,
                'requires_medical_certificate' => $request->has('requires_medical_certificate') ? true : false,
                'requires_approval_level' => $validated['requires_approval_level'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->route('hrd.leave-types')
                ->with('success', 'Leave type updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update leave type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $leaveType = LeaveType::withCount('leaveApplications')->findOrFail($id);
            
            // Only allow deletion if no applications exist
            if ($leaveType->leave_applications_count > 0) {
                return redirect()->route('hrd.leave-types')
                    ->with('error', 'Cannot delete leave type with existing applications. Please deactivate it instead.');
            }
            
            $leaveType->delete();
            
            return redirect()->route('hrd.leave-types')
                ->with('success', 'Leave type deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('hrd.leave-types')
                ->with('error', 'Failed to delete leave type: ' . $e->getMessage());
        }
    }
}

