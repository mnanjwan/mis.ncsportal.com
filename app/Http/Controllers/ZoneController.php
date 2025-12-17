<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZoneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = Zone::withCount('commands');

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $sortableColumns = [
            'name' => 'name',
            'code' => 'code',
            'commands_count' => 'commands_count',
            'status' => 'is_active',
            'created_at' => 'created_at',
        ];

        $column = $sortableColumns[$sortBy] ?? 'name';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

        $query->orderBy($column, $order);

        $zones = $query->paginate(20)->withQueryString();

        return view('dashboards.hrd.zones.index', compact('zones'));
    }

    public function create()
    {
        return view('dashboards.hrd.zones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:zones,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Zone::create($validated);

        return redirect()->route('hrd.zones.index')
            ->with('success', 'Zone created successfully!');
    }

    public function edit($id)
    {
        $zone = Zone::findOrFail($id);
        return view('dashboards.hrd.zones.edit', compact('zone'));
    }

    public function update(Request $request, $id)
    {
        $zone = Zone::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('zones')->ignore($zone->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $zone->update($validated);

        return redirect()->route('hrd.zones.index')
            ->with('success', 'Zone updated successfully!');
    }

    public function show($id)
    {
        $zone = Zone::with(['commands' => function ($query) {
            $query->orderBy('name');
        }])->findOrFail($id);

        return view('dashboards.hrd.zones.show', compact('zone'));
    }
}

