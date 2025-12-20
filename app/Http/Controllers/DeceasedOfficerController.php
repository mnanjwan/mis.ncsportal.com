<?php

namespace App\Http\Controllers;

use App\Models\DeceasedOfficer;
use App\Models\Officer;
use App\Models\NextOfKin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeceasedOfficerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of deceased officers for Welfare
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = DeceasedOfficer::with(['officer.presentStation', 'reportedBy', 'validatedBy']);

        // Filter by validation status
        if (request('status') === 'pending') {
            $query->whereNull('validated_at');
        } elseif (request('status') === 'validated') {
            $query->whereNotNull('validated_at');
        }

        $deceasedOfficers = $query->orderBy('created_at', 'desc')->paginate(15);

        $pendingCount = DeceasedOfficer::whereNull('validated_at')->count();
        $validatedCount = DeceasedOfficer::whereNotNull('validated_at')->count();
        $totalCount = DeceasedOfficer::count();

        return view('dashboards.welfare.deceased-officers', compact(
            'deceasedOfficers',
            'pendingCount',
            'validatedCount',
            'totalCount'
        ));
    }

    /**
     * Show form to report deceased officer (Area Controller/Staff Officer)
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user has permission (Area Controller or Staff Officer)
        if (!$user->hasAnyRole(['Area Controller', 'Staff Officer'])) {
            abort(403, 'Only Area Controller or Staff Officer can report deceased officers.');
        }

        // Get officers from user's command if Staff Officer
        $officers = collect();
        if ($user->hasRole('Staff Officer')) {
            $staffOfficerRole = $user->roles()
                ->where('name', 'Staff Officer')
                ->wherePivot('is_active', true)
                ->first();
            
            $commandId = $staffOfficerRole?->pivot->command_id ?? null;
            if ($commandId) {
                $officers = Officer::where('present_station', $commandId)
                    ->where('is_deceased', false)
                    ->where('is_active', true)
                    ->orderBy('surname')
                    ->get();
            }
        } else {
            // Area Controller can see all officers
            $officers = Officer::where('is_deceased', false)
                ->where('is_active', true)
                ->orderBy('surname')
                ->get();
        }

        return view('forms.deceased-officer.create', compact('officers'));
    }

    /**
     * Store deceased officer report (Area Controller/Staff Officer)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!$user->hasAnyRole(['Area Controller', 'Staff Officer'])) {
            abort(403, 'Only Area Controller or Staff Officer can report deceased officers.');
        }

        $validated = $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'date_of_death' => 'required|date|before_or_equal:today',
            'death_certificate' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $officer = Officer::findOrFail($validated['officer_id']);

        // Check if already reported
        $existing = DeceasedOfficer::where('officer_id', $officer->id)->first();
        if ($existing) {
            return back()->withErrors(['error' => 'This officer has already been reported as deceased.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $deathCertificateUrl = null;
            if ($request->hasFile('death_certificate')) {
                $deathCertificateUrl = $request->file('death_certificate')->store('death-certificates', 'public');
            }

            DeceasedOfficer::create([
                'officer_id' => $officer->id,
                'reported_by' => $user->id,
                'reported_at' => now(),
                'date_of_death' => $validated['date_of_death'],
                'death_certificate_url' => $deathCertificateUrl,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Mark officer as potentially deceased (not fully validated yet)
            $officer->update([
                'is_deceased' => true,
                'deceased_date' => $validated['date_of_death'],
            ]);

            DB::commit();

            // Redirect based on user role
            if ($user->hasRole('Area Controller')) {
                return redirect()->route('area-controller.dashboard')
                    ->with('success', 'Deceased officer reported successfully. Welfare will validate the report.');
            } else {
                return redirect()->route('staff-officer.dashboard')
                    ->with('success', 'Deceased officer reported successfully. Welfare will validate the report.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to report deceased officer. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show deceased officer details for validation (Welfare)
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Welfare')) {
            abort(403, 'Only Welfare can view deceased officer details.');
        }

        $deceasedOfficer = DeceasedOfficer::with([
            'officer.presentStation',
            'officer.nextOfKin',
            'reportedBy',
            'validatedBy'
        ])->findOrFail($id);

        return view('dashboards.welfare.deceased-officer-show', compact('deceasedOfficer'));
    }

    /**
     * Validate deceased officer and generate comprehensive data (Welfare)
     */
    public function validate(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Welfare')) {
            abort(403, 'Only Welfare can validate deceased officers.');
        }

        $deceasedOfficer = DeceasedOfficer::with(['officer.nextOfKin'])->findOrFail($id);

        if ($deceasedOfficer->validated_at) {
            return back()->with('error', 'This officer has already been validated.');
        }

        DB::beginTransaction();
        try {
            $officer = $deceasedOfficer->officer;

            // Generate comprehensive deceased officer data with all required fields
            $nextOfKinData = $officer->nextOfKin->map(function ($kin) {
                return [
                    'name' => $kin->name,
                    'relationship' => $kin->relationship,
                    'phone_number' => $kin->phone_number,
                    'address' => $kin->address,
                    'email' => $kin->email,
                ];
            })->toArray();

            // Update deceased officer record with comprehensive data
            $deceasedOfficer->update([
                'validated_by' => $user->id,
                'validated_at' => now(),
                'next_of_kin_data' => $nextOfKinData,
                'bank_name' => $officer->bank_name,
                'bank_account_number' => $officer->bank_account_number,
                'rsa_administrator' => $officer->pfa_name,
            ]);

            // Ensure officer is marked as deceased
            $officer->update([
                'is_deceased' => true,
                'is_active' => false,
            ]);

            DB::commit();

            return redirect()->route('welfare.deceased-officers')
                ->with('success', 'Deceased officer validated and comprehensive data generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to validate deceased officer. Please try again.');
        }
    }

    /**
     * Generate deceased officer data report (Welfare)
     */
    public function generateReport($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Welfare')) {
            abort(403, 'Only Welfare can generate deceased officer reports.');
        }

        $deceasedOfficer = DeceasedOfficer::with([
            'officer.presentStation',
            'officer.nextOfKin',
            'reportedBy',
            'validatedBy'
        ])->findOrFail($id);

        if (!$deceasedOfficer->validated_at) {
            return back()->with('error', 'Officer must be validated before generating report.');
        }

        // Return view with all required fields for report
        return view('dashboards.welfare.deceased-officer-report', compact('deceasedOfficer'));
    }

    /**
     * Mark benefits as processed (Welfare)
     */
    public function markBenefitsProcessed(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Welfare')) {
            abort(403, 'Only Welfare can mark benefits as processed.');
        }

        $deceasedOfficer = DeceasedOfficer::findOrFail($id);

        if (!$deceasedOfficer->validated_at) {
            return back()->with('error', 'Officer must be validated before marking benefits as processed.');
        }

        DB::beginTransaction();
        try {
            $deceasedOfficer->update([
                'benefits_processed' => true,
                'benefits_processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('welfare.deceased-officers.show', $deceasedOfficer->id)
                ->with('success', 'Benefits marked as processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark benefits as processed. Please try again.');
        }
    }

    /**
     * Export deceased officer data (Welfare)
     */
    public function export($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Welfare')) {
            abort(403, 'Only Welfare can export deceased officer data.');
        }

        $deceasedOfficer = DeceasedOfficer::with([
            'officer.presentStation',
            'officer.nextOfKin',
            'reportedBy',
            'validatedBy'
        ])->findOrFail($id);

        if (!$deceasedOfficer->validated_at) {
            return back()->with('error', 'Officer must be validated before exporting data.');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="deceased_officer_' . $deceasedOfficer->officer->service_number . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($deceasedOfficer) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, ['Field', 'Value']);
            
            // Write data
            fputcsv($file, ['SVC no', $deceasedOfficer->officer->service_number]);
            fputcsv($file, ['Rank', $deceasedOfficer->officer->substantive_rank]);
            fputcsv($file, ['DOB', $deceasedOfficer->officer->date_of_birth ? $deceasedOfficer->officer->date_of_birth->format('Y-m-d') : 'N/A']);
            fputcsv($file, ['Date of Death', $deceasedOfficer->date_of_death->format('Y-m-d')]);
            fputcsv($file, ['Bank Name', $deceasedOfficer->bank_name ?? 'N/A']);
            fputcsv($file, ['Account Number', $deceasedOfficer->bank_account_number ?? 'N/A']);
            fputcsv($file, ['RSA Administrator', $deceasedOfficer->rsa_administrator ?? 'N/A']);
            
            // Next of Kin data
            if ($deceasedOfficer->next_of_kin_data) {
                fputcsv($file, ['', '']);
                fputcsv($file, ['Next of Kin', '']);
                foreach ($deceasedOfficer->next_of_kin_data as $index => $kin) {
                    $prefix = 'NOK ' . ($index + 1) . ' - ';
                    fputcsv($file, [$prefix . 'Name', $kin['name'] ?? 'N/A']);
                    fputcsv($file, [$prefix . 'Relationship', $kin['relationship'] ?? 'N/A']);
                    fputcsv($file, [$prefix . 'Phone', $kin['phone_number'] ?? 'N/A']);
                    fputcsv($file, [$prefix . 'Address', $kin['address'] ?? 'N/A']);
                    fputcsv($file, [$prefix . 'Email', $kin['email'] ?? 'N/A']);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
