<?php

namespace App\Http\Controllers;

use App\Models\Command;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        $query = Command::with('zone');

        // Filter by zone if provided
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $sortableColumns = [
            'name' => 'name',
            'code' => 'code',
            'zone' => function($query, $order) {
                $query->leftJoin('zones', 'commands.zone_id', '=', 'zones.id')
                      ->orderBy('zones.name', $order);
            },
            'location' => 'location',
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

        $commands = $query->select('commands.*')->paginate(20)->withQueryString();
        $zones = Zone::where('is_active', true)->orderBy('name')->get();

        return view('dashboards.hrd.commands.index', compact('commands', 'zones'));
    }

    public function create()
    {
        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        return view('dashboards.hrd.commands.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:commands,code',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'area_controller_id' => 'nullable|exists:officers,id',
            'is_active' => 'boolean',
        ]);

        Command::create($validated);

        return redirect()->route('hrd.commands.index')
            ->with('success', 'Command created successfully!');
    }

    public function edit($id)
    {
        $command = Command::findOrFail($id);
        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        return view('dashboards.hrd.commands.edit', compact('command', 'zones'));
    }

    public function update(Request $request, $id)
    {
        $command = Command::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('commands')->ignore($command->id)],
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'area_controller_id' => 'nullable|exists:officers,id',
            'is_active' => 'boolean',
        ]);

        $command->update($validated);

        return redirect()->route('hrd.commands.index')
            ->with('success', 'Command updated successfully!');
    }

    public function show(Request $request, $id)
    {
        $command = Command::with(['zone', 'areaController'])
            ->withCount(['officers' => function($query) {
                $query->where('is_active', true);
            }])
            ->findOrFail($id);
        
        // Load active officers for this command with sorting
        $query = \App\Models\Officer::where('present_station', $command->id)
            ->where('is_active', true);

        // Sorting
        $sortBy = $request->get('sort_by', 'surname');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $sortableColumns = [
            'name' => function($query, $order) {
                $query->orderBy('surname', $order)
                      ->orderBy('initials', $order);
            },
            'service_number' => 'service_number',
            'rank' => 'substantive_rank',
            'status' => 'is_active',
        ];

        $column = $sortableColumns[$sortBy] ?? 'surname';
        $order = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'asc';

        if (is_callable($column)) {
            $column($query, $order);
        } else {
            $query->orderBy($column, $order);
        }

        // If sorting by name (default), add initials as secondary sort
        if ($sortBy === 'name' || !$request->has('sort_by')) {
            if ($order === 'asc') {
                $query->orderBy('initials', 'asc');
            } else {
                $query->orderBy('initials', 'desc');
            }
        }

        $officers = $query->get();

        return view('dashboards.hrd.commands.show', compact('command', 'officers'));
    }
}

