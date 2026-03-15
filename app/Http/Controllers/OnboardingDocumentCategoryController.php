<?php

namespace App\Http\Controllers;

use App\Models\OnboardingDocumentCategory;
use App\Services\OnboardingDocumentCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OnboardingDocumentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index()
    {
        if (!Schema::hasTable('onboarding_document_categories')) {
            return redirect()
                ->route('hrd.dashboard')
                ->with('error', 'Document category table is missing. Please run database migrations.');
        }

        $categories = OnboardingDocumentCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboards.hrd.document-categories', compact('categories'));
    }

    public function store(Request $request, OnboardingDocumentCategoryService $categoryService)
    {
        if (!Schema::hasTable('onboarding_document_categories')) {
            return redirect()
                ->route('hrd.dashboard')
                ->with('error', 'Document category table is missing. Please run database migrations.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'applies_to' => 'required|in:recruit,officer,both',
            'is_active' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);
        if ($name === '') {
            return back()->with('error', 'Category name cannot be empty.');
        }

        $nameExists = OnboardingDocumentCategory::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();

        if ($nameExists) {
            return back()->with('error', 'A category with that name already exists.');
        }

        $nextSortOrder = (int) (OnboardingDocumentCategory::max('sort_order') ?? 0) + 1;

        OnboardingDocumentCategory::create([
            'key' => $categoryService->generateUniqueKey($name),
            'name' => $name,
            'applies_to' => $validated['applies_to'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => $nextSortOrder,
        ]);

        return redirect()->route('hrd.document-categories.index')
            ->with('success', 'Document category created successfully.');
    }

    public function update(Request $request, int $category)
    {
        if (!Schema::hasTable('onboarding_document_categories')) {
            return redirect()
                ->route('hrd.dashboard')
                ->with('error', 'Document category table is missing. Please run database migrations.');
        }

        $categoryModel = OnboardingDocumentCategory::findOrFail($category);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'applies_to' => 'required|in:recruit,officer,both',
            'is_active' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);
        if ($name === '') {
            return back()->with('error', 'Category name cannot be empty.');
        }

        $nameExists = OnboardingDocumentCategory::query()
            ->where('id', '!=', $categoryModel->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();

        if ($nameExists) {
            return back()->with('error', 'Another category with that name already exists.');
        }

        $categoryModel->update([
            'name' => $name,
            'applies_to' => $validated['applies_to'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('hrd.document-categories.index')
            ->with('success', 'Document category updated successfully.');
    }
}
