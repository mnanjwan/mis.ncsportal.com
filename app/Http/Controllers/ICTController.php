<?php

namespace App\Http\Controllers;

use App\Models\Officer;
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
}
