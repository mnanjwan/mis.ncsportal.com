<?php

namespace App\Http\Controllers;

use App\Models\LeavePassCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LeavePassCriteriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index()
    {
        if (!Schema::hasTable('leave_pass_criteria')) {
            return redirect()
                ->route('hrd.dashboard')
                ->with('error', 'Leave & Pass criteria table is missing. Please run database migrations.');
        }

        $records = LeavePassCriterion::query()->get();
        $criteria = [];

        foreach ([LeavePassCriterion::TYPE_ANNUAL_LEAVE, LeavePassCriterion::TYPE_PASS] as $type) {
            foreach (LeavePassCriterion::RANKS as $rank) {
                $existing = $records->first(function ($row) use ($type, $rank) {
                    return $row->type === $type && $row->rank === $rank;
                });
                $band = LeavePassCriterion::rankBand($rank);
                $defaultDays = $band === LeavePassCriterion::BAND_GL07_ABOVE ? 30 : ($band === LeavePassCriterion::BAND_GL04_06 ? 21 : 14);

                $criteria[$type][$rank] = $existing ?: new LeavePassCriterion([
                    'type' => $type,
                    'rank' => $rank,
                    'grade_band' => $band,
                    'max_times_per_year' => 2,
                    'duration_type' => LeavePassCriterion::DURATION_WORKING_DAYS,
                    'max_duration_days' => $defaultDays,
                    'qualification_months' => 0,
                ]);
            }
        }

        return view('dashboards.hrd.leave-pass-criteria', [
            'criteria' => $criteria,
            'ranks' => LeavePassCriterion::RANKS,
            'durationTypes' => [
                LeavePassCriterion::DURATION_WORKING_DAYS => 'Working Days',
                LeavePassCriterion::DURATION_CALENDAR_DAYS => 'Calendar Days',
            ],
        ]);
    }

    public function update(Request $request)
    {
        if (!Schema::hasTable('leave_pass_criteria')) {
            return redirect()
                ->route('hrd.dashboard')
                ->with('error', 'Leave & Pass criteria table is missing. Please run database migrations.');
        }

        $validated = $request->validate([
            'criteria' => 'required|array',
            'criteria.*' => 'required|array',
            'criteria.*.*' => 'required|array',
            'criteria.*.*.max_times_per_year' => 'required|integer|min:1|max:12',
            'criteria.*.*.duration_type' => 'required|in:working_days,calendar_days',
            'criteria.*.*.max_duration_days' => 'required|integer|min:1|max:365',
            'criteria.*.*.qualification_months' => 'required|integer|min:0|max:600',
        ]);

        try {
            foreach ($validated['criteria'] as $type => $bands) {
                if (!in_array($type, [LeavePassCriterion::TYPE_ANNUAL_LEAVE, LeavePassCriterion::TYPE_PASS], true)) {
                    continue;
                }

                foreach ($bands as $rank => $values) {
                    if (!in_array($rank, LeavePassCriterion::RANKS, true)) {
                        continue;
                    }

                    LeavePassCriterion::updateOrCreate(
                        [
                            'type' => $type,
                            'rank' => $rank,
                        ],
                        [
                            'grade_band' => LeavePassCriterion::rankBand($rank),
                            'max_times_per_year' => (int) $values['max_times_per_year'],
                            'duration_type' => $values['duration_type'],
                            'max_duration_days' => (int) $values['max_duration_days'],
                            'qualification_months' => (int) $values['qualification_months'],
                        ]
                    );
                }
            }

            return redirect()
                ->route('hrd.leave-pass-criteria')
                ->with('success', 'Leave and pass criteria updated successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update criteria: ' . $e->getMessage());
        }
    }
}
