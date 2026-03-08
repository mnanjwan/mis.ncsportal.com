<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyDrug;
use App\Models\PharmacyUnitOfMeasurement;
use Illuminate\Http\Request;

class PharmacyDrugController extends Controller
{
    /**
     * Display a listing of drugs.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $showInactive = $request->boolean('show_inactive', false);

        $query = PharmacyDrug::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category) {
            $query->where('category', $category);
        }

        if (!$showInactive) {
            $query->active();
        }

        $drugs = $query->orderBy('name')->paginate(30);
        $categories = PharmacyDrug::distinct()->pluck('category')->filter()->sort();

        return view('pharmacy.drugs.index', compact('drugs', 'categories', 'search', 'category', 'showInactive'));
    }

    /**
     * Show the form for creating a new drug.
     */
    public function create()
    {
        $categories = PharmacyDrug::distinct()->pluck('category')->filter()->sort();
        $existingDrugNames = PharmacyDrug::orderBy('name')->pluck('name')->values()->toArray();
        $unitOptions = $this->getUnitOfMeasureOptions();

        return view('pharmacy.drugs.create', compact('categories', 'existingDrugNames', 'unitOptions'));
    }

    /**
     * Get unit of measure options: seeded list + any distinct from existing drugs (for legacy values).
     */
    private function getUnitOfMeasureOptions(): array
    {
        $fromTable = PharmacyUnitOfMeasurement::orderBy('name')->pluck('name')->values()->toArray();
        $fromDrugs = PharmacyDrug::distinct()->pluck('unit_of_measure')->filter()->sort()->values()->toArray();
        $merged = array_values(array_unique(array_merge($fromTable, $fromDrugs)));
        sort($merged);
        return $merged;
    }

    /**
     * Store a newly created drug.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:pharmacy_drugs,name',
            'description' => 'nullable|string|max:1000',
            'unit_of_measure' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
        ], [
            'name.unique' => 'A drug / item with this name already exists. Search and select it from the list or use a different name.',
        ]);

        $unit = trim($request->unit_of_measure);
        if ($unit === '' || $unit === '__ADD_NEW__') {
            return redirect()->back()->withErrors(['unit_of_measure' => 'Please select or enter a unit of measure.'])->withInput();
        }
        PharmacyUnitOfMeasurement::firstOrCreate(['name' => $unit]);

        PharmacyDrug::create($request->only(['name', 'description', 'unit_of_measure', 'category']));

        return redirect()
            ->route('pharmacy.drugs.index')
            ->with('success', 'Drug / item added successfully.');
    }

    /**
     * Display the specified drug.
     */
    public function show($id)
    {
        $drug = PharmacyDrug::findOrFail($id);
        
        return view('pharmacy.drugs.show', compact('drug'));
    }

    /**
     * Show the form for editing the drug.
     */
    public function edit($id)
    {
        $drug = PharmacyDrug::findOrFail($id);
        $categories = PharmacyDrug::distinct()->pluck('category')->filter()->sort();
        $unitOptions = $this->getUnitOfMeasureOptions();

        return view('pharmacy.drugs.edit', compact('drug', 'categories', 'unitOptions'));
    }

    /**
     * Update the specified drug.
     */
    public function update(Request $request, $id)
    {
        $drug = PharmacyDrug::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:pharmacy_drugs,name,' . $id,
            'description' => 'nullable|string|max:1000',
            'unit_of_measure' => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $unit = trim($request->unit_of_measure);
        if ($unit === '' || $unit === '__ADD_NEW__') {
            return redirect()->back()->withErrors(['unit_of_measure' => 'Please select or enter a unit of measure.'])->withInput();
        }
        PharmacyUnitOfMeasurement::firstOrCreate(['name' => $unit]);

        $drug->update($request->only(['name', 'description', 'unit_of_measure', 'category', 'is_active']));

        return redirect()
            ->route('pharmacy.drugs.index')
            ->with('success', 'Drug / item updated successfully.');
    }

    /**
     * Toggle the drug's active status.
     */
    public function toggleActive($id)
    {
        $drug = PharmacyDrug::findOrFail($id);
        $drug->update(['is_active' => !$drug->is_active]);

        $status = $drug->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('pharmacy.drugs.index')
            ->with('success', "Drug / item {$status} successfully.");
    }
}
