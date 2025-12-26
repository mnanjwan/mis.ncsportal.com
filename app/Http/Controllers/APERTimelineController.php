<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\APERTimeline;

class APERTimelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index()
    {
        $timelines = APERTimeline::orderBy('created_at', 'desc')->paginate(20);
        
        return view('dashboards.hrd.aper-timeline', compact('timelines'));
    }

    public function create()
    {
        return view('forms.aper-timeline.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        // Check if timeline for this year already exists
        $existingTimeline = APERTimeline::where('year', $validated['year'])->first();
        if ($existingTimeline) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A timeline for year ' . $validated['year'] . ' already exists. Please edit the existing timeline or choose a different year.');
        }

        // If setting as active, deactivate other timelines
        if ($request->has('is_active') && $request->is_active) {
            APERTimeline::where('is_active', true)->update(['is_active' => false]);
        }

        $validated['created_by'] = auth()->id();
        
        try {
            $timeline = APERTimeline::create($validated);
            return redirect()->route('hrd.aper-timeline')
                ->with('success', 'APER timeline created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create timeline: ' . $e->getMessage());
        }
    }

    public function extend($id)
    {
        $timeline = APERTimeline::findOrFail($id);
        
        return view('forms.aper-timeline.extend', compact('timeline'));
    }

    public function extendStore(Request $request, $id)
    {
        $timeline = APERTimeline::findOrFail($id);

        $endDate = $timeline->end_date instanceof \Carbon\Carbon 
            ? $timeline->end_date->format('Y-m-d') 
            : $timeline->end_date;
            
        $validated = $request->validate([
            'extension_end_date' => 'required|date|after:' . $endDate,
            'extension_reason' => 'nullable|string|max:1000',
        ]);

        $timeline->update([
            'is_extended' => true,
            'extension_end_date' => $validated['extension_end_date'],
        ]);

        return redirect()->route('hrd.aper-timeline')
            ->with('success', 'APER timeline extended successfully!');
    }
}

