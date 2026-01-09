<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\Emolument;
use App\Models\EmolumentTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ICTController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:ICT');
    }

    /**
     * Display dashboard with officers needing email creation
     */
    public function index()
    {
        // Officers with service numbers but no customs email
        $officersNeedingEmail = Officer::whereNotNull('service_number')
            ->where(function($query) {
                $query->whereNull('customs_email')
                    ->orWhere('email_status', 'personal');
            })
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->with('trainingResult')
            ->orderBy('service_number')
            ->paginate(20);

        $stats = [
            'needing_email' => Officer::whereNotNull('service_number')
                ->where(function($query) {
                    $query->whereNull('customs_email')
                        ->orWhere('email_status', 'personal');
                })
                ->where('is_active', true)
                ->where('is_deceased', false)
                ->count(),
            'with_customs_email' => Officer::whereNotNull('customs_email')
                ->where('email_status', 'customs')
                ->where('is_active', true)
                ->count(),
            'migrated' => Officer::where('email_status', 'migrated')
                ->where('is_active', true)
                ->count(),
        ];

        return view('dashboards.ict.index', compact('officersNeedingEmail', 'stats'));
    }

    /**
     * Create email addresses for officers
     */
    public function createEmails(Request $request)
    {
        $request->validate([
            'officer_ids' => 'required|array',
            'officer_ids.*' => 'exists:officers,id',
        ]);

        DB::beginTransaction();
        try {
            $officers = Officer::whereIn('id', $request->officer_ids)
                ->whereNotNull('service_number')
                ->where('is_active', true)
                ->where('is_deceased', false)
                ->get();

            $created = 0;
            $errors = [];

            foreach ($officers as $officer) {
                if (empty($officer->service_number)) {
                    $errors[] = "Officer {$officer->full_name} does not have a service number";
                    continue;
                }

                // Generate email: service_number@customs.gov.ng
                $email = strtolower($officer->service_number) . '@customs.gov.ng';

                // Check if email already exists
                if (Officer::where('customs_email', $email)->where('id', '!=', $officer->id)->exists()) {
                    $errors[] = "Email {$email} already exists for another officer";
                    continue;
                }

                // Store personal email if not already stored
                if (empty($officer->personal_email) && !empty($officer->email)) {
                    $officer->personal_email = $officer->email;
                }

                // Update officer with customs email
                $officer->update([
                    'customs_email' => $email,
                    'email' => $email, // Update main email field
                    'email_status' => 'customs',
                ]);

                $created++;
            }

            DB::commit();

            $message = "Successfully created {$created} email address(es).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->route('ict.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ICT email creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create email addresses: ' . $e->getMessage());
        }
    }

    /**
     * Delete personal email addresses
     */
    public function deletePersonalEmails(Request $request)
    {
        $request->validate([
            'officer_ids' => 'required|array',
            'officer_ids.*' => 'exists:officers,id',
        ]);

        DB::beginTransaction();
        try {
            $officers = Officer::whereIn('id', $request->officer_ids)
                ->whereNotNull('customs_email')
                ->where('email_status', 'customs')
                ->get();

            $deleted = 0;

            foreach ($officers as $officer) {
                // Clear personal email (but keep it in personal_email field for records)
                $officer->update([
                    'email_status' => 'migrated',
                ]);

                $deleted++;
            }

            DB::commit();

            return redirect()->route('ict.index')
                ->with('success', "Successfully processed {$deleted} email migration(s). Personal emails have been replaced with customs.gov.ng emails.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ICT email deletion error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process email migration: ' . $e->getMessage());
        }
    }

    /**
     * Bulk create emails for all officers with service numbers
     */
    public function bulkCreateEmails()
    {
        DB::beginTransaction();
        try {
            $officers = Officer::whereNotNull('service_number')
                ->where(function($query) {
                    $query->whereNull('customs_email')
                        ->orWhere('email_status', 'personal');
                })
                ->where('is_active', true)
                ->where('is_deceased', false)
                ->get();

            $created = 0;
            $skipped = 0;

            foreach ($officers as $officer) {
                $email = strtolower($officer->service_number) . '@customs.gov.ng';

                // Check if email already exists
                if (Officer::where('customs_email', $email)->where('id', '!=', $officer->id)->exists()) {
                    $skipped++;
                    continue;
                }

                if (empty($officer->personal_email) && !empty($officer->email)) {
                    $officer->personal_email = $officer->email;
                }

                $officer->update([
                    'customs_email' => $email,
                    'email' => $email,
                    'email_status' => 'customs',
                ]);

                $created++;
            }

            DB::commit();

            $message = "Successfully created {$created} email address(es).";
            if ($skipped > 0) {
                $message .= " {$skipped} skipped (email already exists).";
            }

            return redirect()->route('ict.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ICT bulk email creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create email addresses: ' . $e->getMessage());
        }
    }

    /**
     * Display officers who did not submit emoluments
     */
    public function nonSubmitters(Request $request)
    {
        // Get active timeline or selected timeline
        $selectedYear = $request->get('year', date('Y'));
        $timeline = EmolumentTimeline::where('year', $selectedYear)
            ->where('is_active', true)
            ->first();

        // Get all active officers
        $query = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->with(['presentStation.zone']);

        // Filter by command if provided
        if ($request->filled('command_id')) {
            $query->where('present_station', $request->command_id);
        }

        // Filter by zone if provided
        if ($request->filled('zone_id')) {
            $query->whereHas('presentStation', function($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%");
            });
        }

        $allOfficers = $query->get();

        // Get officers who submitted emoluments for the selected year
        $submittedOfficerIds = [];
        if ($timeline) {
            $submittedOfficerIds = Emolument::where('timeline_id', $timeline->id)
                ->pluck('officer_id')
                ->toArray();
        } else {
            // If no timeline, check by year
            $submittedOfficerIds = Emolument::where('year', $selectedYear)
                ->pluck('officer_id')
                ->toArray();
        }

        // Filter to only officers who didn't submit
        $nonSubmitters = $allOfficers->filter(function($officer) use ($submittedOfficerIds) {
            return !in_array($officer->id, $submittedOfficerIds);
        });

        // Sorting
        $sortBy = $request->get('sort_by', 'service_number');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'service_number') {
            $nonSubmitters = $nonSubmitters->sortBy('service_number', SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'name') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return ($officer->surname ?? '') . ($officer->initials ?? '');
            }, SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'rank') {
            $nonSubmitters = $nonSubmitters->sortBy('substantive_rank', SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'command') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return $officer->presentStation->name ?? '';
            }, SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'zone') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return $officer->presentStation->zone->name ?? '';
            }, SORT_REGULAR, $sortOrder === 'desc');
        }

        // Paginate
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $items = $nonSubmitters->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $nonSubmitters->count();
        $nonSubmitters = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get available years from timelines
        $years = EmolumentTimeline::where('is_active', true)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        if (!in_array($selectedYear, $years)) {
            $years[] = $selectedYear;
            sort($years);
            $years = array_reverse($years);
        }

        // Get zones and commands for filters
        $zones = \App\Models\Zone::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $commands = \App\Models\Command::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboards.ict.non-submitters', compact(
            'nonSubmitters',
            'selectedYear',
            'timeline',
            'years',
            'zones',
            'commands'
        ));
    }

    /**
     * Print non-submitters report
     */
    public function printNonSubmitters(Request $request)
    {
        $selectedYear = $request->get('year', date('Y'));
        $timeline = EmolumentTimeline::where('year', $selectedYear)
            ->where('is_active', true)
            ->first();

        // Get all active officers
        $query = Officer::where('is_active', true)
            ->where('is_deceased', false)
            ->with(['presentStation.zone']);

        // Filter by command if provided
        if ($request->filled('command_id')) {
            $query->where('present_station', $request->command_id);
        }

        // Filter by zone if provided
        if ($request->filled('zone_id')) {
            $query->whereHas('presentStation', function($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('initials', 'like', "%{$search}%");
            });
        }

        $allOfficers = $query->get();

        // Get officers who submitted
        $submittedOfficerIds = [];
        if ($timeline) {
            $submittedOfficerIds = Emolument::where('timeline_id', $timeline->id)
                ->pluck('officer_id')
                ->toArray();
        } else {
            $submittedOfficerIds = Emolument::where('year', $selectedYear)
                ->pluck('officer_id')
                ->toArray();
        }

        // Filter non-submitters
        $nonSubmitters = $allOfficers->filter(function($officer) use ($submittedOfficerIds) {
            return !in_array($officer->id, $submittedOfficerIds);
        });

        // Sort
        $sortBy = $request->get('sort_by', 'service_number');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'service_number') {
            $nonSubmitters = $nonSubmitters->sortBy('service_number', SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'name') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return ($officer->surname ?? '') . ($officer->initials ?? '');
            }, SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'rank') {
            $nonSubmitters = $nonSubmitters->sortBy('substantive_rank', SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'command') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return $officer->presentStation->name ?? '';
            }, SORT_REGULAR, $sortOrder === 'desc');
        } elseif ($sortBy === 'zone') {
            $nonSubmitters = $nonSubmitters->sortBy(function($officer) {
                return $officer->presentStation->zone->name ?? '';
            }, SORT_REGULAR, $sortOrder === 'desc');
        }

        return view('prints.non-submitters', compact('nonSubmitters', 'selectedYear', 'timeline'));
    }
}
