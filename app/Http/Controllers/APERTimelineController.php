<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\APERTimeline;
use App\Models\Officer;

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
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        // Combine date and time into datetime
        $startDateTime = \Carbon\Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']);
        $endDateTime = \Carbon\Carbon::parse($validated['end_date'] . ' ' . $validated['end_time']);

        // Validate that end datetime is after start datetime
        if ($endDateTime <= $startDateTime) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'End date and time must be after start date and time.');
        }

        // Check if timeline for this year already exists
        $existingTimeline = APERTimeline::where('year', $validated['year'])->first();
        if ($existingTimeline) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A timeline for year ' . $validated['year'] . ' already exists. Please edit the existing timeline or choose a different year.');
        }

        // Handle is_active checkbox - check if it was checked
        $isActive = $request->has('is_active') && $request->input('is_active') == '1';
        
        // If setting as active, deactivate other timelines
        if ($isActive) {
            APERTimeline::where('is_active', true)->update(['is_active' => false]);
        }

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $isActive;
        $validated['start_date'] = $startDateTime;
        $validated['end_date'] = $endDateTime;
        
        // Remove time fields as they're now combined
        unset($validated['start_time'], $validated['end_time']);
        
        try {
            $timeline = APERTimeline::create($validated);
            
            // Send notifications to all officers if timeline is active and starts today or in the past
            if ($timeline->is_active && $timeline->start_date <= now()) {
                $this->sendTimelineOpenedNotifications($timeline);
            }
            
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

        $endDateTime = $timeline->end_date instanceof \Carbon\Carbon 
            ? $timeline->end_date 
            : \Carbon\Carbon::parse($timeline->end_date);
            
        $validated = $request->validate([
            'extension_end_date' => 'required|date',
            'extension_end_time' => 'required|date_format:H:i',
            'extension_reason' => 'nullable|string|max:1000',
        ]);

        // Combine date and time into datetime
        $extensionEndDateTime = \Carbon\Carbon::parse($validated['extension_end_date'] . ' ' . $validated['extension_end_time']);

        // Validate that extension end datetime is after current end datetime
        if ($extensionEndDateTime <= $endDateTime) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Extension end date and time must be after the current end date and time (' . $endDateTime->format('d/m/Y H:i') . ').');
        }

        $timeline->update([
            'is_extended' => true,
            'extension_end_date' => $extensionEndDateTime,
        ]);

        // Refresh timeline to get updated data
        $timeline->refresh();

        // Send notifications to officers about the extension
        $this->sendTimelineExtensionNotifications($timeline);

        return redirect()->route('hrd.aper-timeline')
            ->with('success', 'APER timeline extended successfully! Notifications sent to officers.');
    }

    private function sendTimelineOpenedNotifications(APERTimeline $timeline)
    {
        $officers = Officer::whereHas('user', function($query) {
            $query->whereNotNull('email');
        })->get();

        $sent = 0;
        foreach ($officers as $officer) {
            if ($officer->user && $officer->user->email) {
                \App\Jobs\SendAPERTimelineOpenedMailJob::dispatch($officer, $timeline);
                $sent++;
            }
        }
        
        \Log::info("Dispatched {$sent} APER timeline opened notification jobs", [
            'timeline_id' => $timeline->id,
            'year' => $timeline->year,
        ]);
    }

    private function sendTimelineExtensionNotifications(APERTimeline $timeline)
    {
        $officers = Officer::whereHas('user', function($query) {
            $query->whereNotNull('email');
        })->get();

        $sent = 0;
        $endDate = $timeline->is_extended && $timeline->extension_end_date
            ? $timeline->extension_end_date
            : $timeline->end_date;

        foreach ($officers as $officer) {
            if ($officer->user && $officer->user->email) {
                try {
                    // Check if officer has submitted form for this timeline
                    $submittedForm = \App\Models\APERForm::where('officer_id', $officer->id)
                        ->where('timeline_id', $timeline->id)
                        ->where('status', '!=', 'DRAFT')
                        ->first();

                    // Only send to officers who haven't submitted
                    if (!$submittedForm) {
                        // Check if they have a draft
                        $draftForm = \App\Models\APERForm::where('officer_id', $officer->id)
                            ->where('timeline_id', $timeline->id)
                            ->where('status', 'DRAFT')
                            ->first();

                        $daysRemaining = \Carbon\Carbon::now()->diffInDays($endDate, false);

                        \App\Jobs\SendAPERTimelineClosingMailJob::dispatch(
                            $officer,
                            $timeline,
                            $daysRemaining,
                            $draftForm ? true : false,
                            $draftForm ? $draftForm->id : null
                        );
                        $sent++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to dispatch APER timeline extension notification job", [
                        'officer_id' => $officer->id,
                        'email' => $officer->user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        \Log::info("Dispatched {$sent} APER timeline extension notification jobs", [
            'timeline_id' => $timeline->id,
            'year' => $timeline->year,
        ]);
    }
}

