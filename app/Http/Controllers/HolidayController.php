<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->paginate(20);
        return view('dashboards.hrd.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('dashboards.hrd.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'is_floating' => 'boolean',
        ]);

        $date = Carbon::parse($request->date);

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'is_floating' => $request->has('is_floating'),
            'year' => $date->year,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hrd.holidays.index')->with('success', 'Holiday added successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('dashboards.hrd.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'is_floating' => 'boolean',
        ]);

        $date = Carbon::parse($request->date);

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'is_floating' => $request->has('is_floating'),
            'year' => $date->year,
        ]);

        return redirect()->route('hrd.holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('hrd.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
