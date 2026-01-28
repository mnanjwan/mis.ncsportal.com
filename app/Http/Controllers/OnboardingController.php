<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OnboardingLinkMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:HRD');
    }

    public function index(Request $request)
    {
        // Get all officers with user accounts (initiated onboarding)
        // Show their email delivery status and onboarding completion status
        $query = Officer::whereHas('user')
            ->with(['user', 'presentStation']);

        // Apply search filter if provided
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('service_number', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('initials', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $onboardingOfficers = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->only('search'));

        $searchTerm = $search ?? null;

        // Return partial view for AJAX requests
        if ($request->ajax()) {
            return view('dashboards.hrd.partials.onboarding-table', compact('onboardingOfficers', 'searchTerm'));
        }
        
        return view('dashboards.hrd.onboarding', compact('onboardingOfficers', 'searchTerm'));
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'service_number' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            // Find officer by service number (check all, not just active)
            $officer = Officer::where('service_number', $validated['service_number'])->first();
            
            // If officer doesn't exist, create a minimal record for serving officer onboarding
            // Provide default values for required fields - officer will update during onboarding
            if (!$officer) {
                $currentUserId = Auth::id();
                $officerName = $validated['name'] ?? '';
                // Extract initials and surname from name if provided, otherwise use placeholders
                $nameParts = $officerName ? explode(' ', trim($officerName), 2) : [];
                $initials = !empty($nameParts) ? strtoupper(substr($nameParts[0], 0, 2)) : 'TBD';
                $surname = !empty($nameParts) && count($nameParts) > 1 ? $nameParts[1] : ($nameParts[0] ?? 'TBD');
                
                $officer = Officer::create([
                    'service_number' => $validated['service_number'],
                    'initials' => $initials,
                    'surname' => $surname,
                    'sex' => 'M', // Default, will be updated during onboarding
                    'date_of_birth' => '1980-01-01', // Placeholder, will be updated during onboarding
                    'date_of_first_appointment' => now()->toDateString(), // Placeholder
                    'date_of_present_appointment' => now()->toDateString(), // Placeholder
                    'substantive_rank' => 'TBD', // To Be Determined
                    'salary_grade_level' => 'TBD',
                    'state_of_origin' => 'TBD',
                    'lga' => 'TBD',
                    'geopolitical_zone' => 'TBD',
                    'entry_qualification' => 'TBD',
                    'permanent_home_address' => 'To be provided during onboarding',
                    'phone_number' => '00000000000', // Placeholder
                    'email' => $validated['email'],
                    'is_active' => true,
                    'is_deceased' => false,
                    'created_by' => $currentUserId,
                ]);
            } else {
                // Check if officer is active and not deceased
                if (!$officer->is_active || $officer->is_deceased) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Officer with service number ' . $validated['service_number'] . ' is not active or is marked as deceased.');
                }
            }
            
            // Check if officer already has a user account
            if ($officer->user) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This officer already has an account. Onboarding is not needed.');
            }

            // Generate random 8-digit password
            $tempPassword = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Create user account
            $currentUserId = Auth::id();
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($tempPassword),
                'temp_password' => $tempPassword, // Store plain password temporarily
                'is_active' => true,
                'created_by' => $currentUserId,
            ]);

            // Link user to officer
            $officer->update(['user_id' => $user->id]);

            // Assign Officer role to user
            $officerRole = \App\Models\Role::where('name', 'Officer')->first();
            if ($officerRole && $officer->present_station) {
                $user->roles()->attach($officerRole->id, [
                    'command_id' => $officer->present_station,
                    'assigned_at' => now(),
                    'assigned_by' => $currentUserId,
                    'is_active' => true,
                ]);
            }

            // Generate onboarding link
            $onboardingLink = route('onboarding.step1') . '?token=' . base64_encode($user->id . '|' . $tempPassword);

            // Send email notification
            $emailSent = false;
            $emailDelivered = false;
            try {
                $officerName = $validated['name'] ?? trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                Mail::to($validated['email'])->send(new OnboardingLinkMail($onboardingLink, $tempPassword, $officerName, $validated['email']));
                $emailSent = true;
                $emailDelivered = true;
            } catch (\Exception $e) {
                Log::error("Failed to send onboarding email: " . $e->getMessage());
                $emailSent = false;
                $emailDelivered = false;
            }

            // Store email delivery status in user meta or session
            $user->update([
                'email_verified_at' => $emailDelivered ? now() : null,
            ]);

            $message = "Onboarding initiated for " . ($validated['name'] ?? $officer->service_number) . ".";
            if ($emailSent) {
                $message .= " Email has been sent to {$validated['email']}.";
            } else {
                $message .= " Email failed to send. Onboarding link: {$onboardingLink}";
            }

            return redirect()->route('hrd.onboarding')
                ->with('success', $message)
                ->with('onboarding_link', $onboardingLink)
                ->with('temp_password', $tempPassword)
                ->with('email_sent', $emailSent);
        } catch (\Exception $e) {
            Log::error('Onboarding initiation error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to initiate onboarding: ' . $e->getMessage());
        }
    }

    public function bulkInitiate(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array|max:10',
            'entries.*.service_number' => 'required|string|max:50',
            'entries.*.email' => 'required|email|max:255',
            'entries.*.name' => 'nullable|string|max:255',
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

            foreach ($validated['entries'] as $index => $entry) {
                try {
                    // Find officer by service number (check all, not just active)
                    $officer = Officer::where('service_number', $entry['service_number'])->first();
                    
                    // If officer doesn't exist, create a minimal record for serving officer onboarding
                    // Provide default values for required fields - officer will update during onboarding
                    if (!$officer) {
                        $currentUserId = Auth::id();
                        $officerName = $entry['name'] ?? '';
                        // Extract initials and surname from name if provided, otherwise use placeholders
                        $nameParts = $officerName ? explode(' ', trim($officerName), 2) : [];
                        $initials = !empty($nameParts) ? strtoupper(substr($nameParts[0], 0, 2)) : 'TBD';
                        $surname = !empty($nameParts) && count($nameParts) > 1 ? $nameParts[1] : ($nameParts[0] ?? 'TBD');
                        
                        $officer = Officer::create([
                            'service_number' => $entry['service_number'],
                            'initials' => $initials,
                            'surname' => $surname,
                            'sex' => 'M', // Default, will be updated during onboarding
                            'date_of_birth' => '1980-01-01', // Placeholder, will be updated during onboarding
                            'date_of_first_appointment' => now()->toDateString(), // Placeholder
                            'date_of_present_appointment' => now()->toDateString(), // Placeholder
                            'substantive_rank' => 'TBD', // To Be Determined
                            'salary_grade_level' => 'TBD',
                            'state_of_origin' => 'TBD',
                            'lga' => 'TBD',
                            'geopolitical_zone' => 'TBD',
                            'entry_qualification' => 'TBD',
                            'permanent_home_address' => 'To be provided during onboarding',
                            'phone_number' => '00000000000', // Placeholder
                            'email' => $entry['email'],
                            'is_active' => true,
                            'is_deceased' => false,
                            'created_by' => $currentUserId,
                        ]);
                    } else {
                        // Check if officer is active and not deceased
                        if (!$officer->is_active || $officer->is_deceased) {
                            $results[] = [
                                'service_number' => $entry['service_number'],
                                'status' => 'error',
                                'message' => 'Officer is not active or is marked as deceased'
                            ];
                            $errorCount++;
                            continue;
                        }
                    }
                    
                    // Check if officer already has a user account
                    if ($officer->user) {
                        $results[] = [
                            'service_number' => $entry['service_number'],
                            'status' => 'error',
                            'message' => 'Officer already has an account'
                        ];
                        $errorCount++;
                        continue;
                    }

                // Check if email already exists
                if (User::where('email', $entry['email'])->exists()) {
                    $results[] = [
                        'service_number' => $entry['service_number'],
                        'status' => 'error',
                        'message' => 'Email already exists'
                    ];
                    $errorCount++;
                    continue;
                }

                // Generate random 8-digit password
                $tempPassword = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
                
                // Create user account
                $currentUserId = Auth::id();
                $user = User::create([
                    'email' => $entry['email'],
                    'password' => Hash::make($tempPassword),
                    'temp_password' => $tempPassword, // Store plain password temporarily
                    'is_active' => true,
                    'created_by' => $currentUserId,
                ]);

                // Link user to officer
                $officer->update(['user_id' => $user->id]);

                // Assign Officer role to user
                $officerRole = \App\Models\Role::where('name', 'Officer')->first();
                if ($officerRole && $officer->present_station) {
                    $user->roles()->attach($officerRole->id, [
                        'command_id' => $officer->present_station,
                        'assigned_at' => now(),
                        'assigned_by' => $currentUserId,
                        'is_active' => true,
                    ]);
                }

                // Generate onboarding link
                $onboardingLink = route('onboarding.step1') . '?token=' . base64_encode($user->id . '|' . $tempPassword);

                // Send email notification
                $emailSent = false;
                try {
                    $officerName = $entry['name'] ?? trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                    Mail::to($entry['email'])->send(new OnboardingLinkMail($onboardingLink, $tempPassword, $officerName, $entry['email']));
                    $emailSent = true;
                    $user->update(['email_verified_at' => now()]);
                } catch (\Exception $e) {
                    Log::error("Failed to send onboarding email for {$entry['service_number']}: " . $e->getMessage());
                }

                $results[] = [
                    'service_number' => $entry['service_number'],
                    'email' => $entry['email'],
                    'status' => 'success',
                    'email_sent' => $emailSent,
                    'message' => $emailSent ? 'Email sent successfully' : 'Email failed to send'
                ];
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Bulk onboarding error for {$entry['service_number']}: " . $e->getMessage());
                $results[] = [
                    'service_number' => $entry['service_number'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        $message = "Bulk onboarding completed: {$successCount} successful, {$errorCount} failed.";
        
        return redirect()->route('hrd.onboarding')
            ->with('success', $message)
            ->with('bulk_results', $results);
    }

    public function resendLink($id)
    {
        try {
            $officer = Officer::with('user')->findOrFail($id);
            
            if (!$officer->user) {
                return redirect()->back()
                    ->with('error', 'Officer does not have a user account. Please initiate onboarding first.');
            }

            // Generate new random 8-digit password
            $tempPassword = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $officer->user->update([
                'password' => Hash::make($tempPassword),
                'temp_password' => $tempPassword, // Store plain password temporarily
            ]);

            // Generate onboarding link
            $onboardingLink = route('onboarding.step1') . '?token=' . base64_encode($officer->user->id . '|' . $tempPassword);

            // Send email notification
            $emailSent = false;
            $emailDelivered = false;
            try {
                $officerName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                Mail::to($officer->user->email)->send(new OnboardingLinkMail($onboardingLink, $tempPassword, $officerName, $officer->user->email));
                $emailSent = true;
                $emailDelivered = true;
            } catch (\Exception $e) {
                Log::error("Failed to send onboarding email: " . $e->getMessage());
                $emailSent = false;
                $emailDelivered = false;
            }
            
            // Update email delivery status
            $officer->user->update([
                'email_verified_at' => $emailDelivered ? now() : null,
            ]);
            
            // Refresh the relationship to ensure updated data is available
            $officer->load('user');

            $message = "Onboarding link regenerated for {$officer->initials} {$officer->surname}.";
            if ($emailSent) {
                $message .= " Link has been sent to {$officer->user->email}.";
            } else {
                $message .= " Link: {$onboardingLink}";
            }

            return redirect()->route('hrd.onboarding')
                ->with('success', $message)
                ->with('onboarding_link', $onboardingLink)
                ->with('temp_password', $tempPassword)
                ->with('email_sent', $emailSent);
        } catch (\Exception $e) {
            Log::error('Resend onboarding link error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to resend onboarding link: ' . $e->getMessage());
        }
    }

    public function updateEmail(Request $request, $id)
    {
        try {
            $officer = Officer::with('user')->findOrFail($id);
            
            if (!$officer->user) {
                return redirect()->back()
                    ->with('error', 'Officer does not have a user account. Please initiate onboarding first.');
            }

            // Validate the new email (unique check excluding current user)
            $validated = $request->validate([
                'email' => 'required|email|max:255|unique:users,email,' . $officer->user->id,
            ]);

            $oldEmail = $officer->user->email;
            $newEmail = $validated['email'];

            // Update email in users table
            $officer->user->update([
                'email' => $newEmail,
                'email_verified_at' => null, // Reset email verification
            ]);

            // Update email in officers table
            $officer->update([
                'email' => $newEmail,
            ]);

            // Generate new random 8-digit password
            $tempPassword = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            $officer->user->update([
                'password' => Hash::make($tempPassword),
                'temp_password' => $tempPassword,
            ]);

            // Generate new onboarding link
            $onboardingLink = route('onboarding.step1') . '?token=' . base64_encode($officer->user->id . '|' . $tempPassword);

            // Send email notification to new email
            $emailSent = false;
            $emailDelivered = false;
            try {
                $officerName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                Mail::to($newEmail)->send(new OnboardingLinkMail($onboardingLink, $tempPassword, $officerName, $newEmail));
                $emailSent = true;
                $emailDelivered = true;
            } catch (\Exception $e) {
                Log::error("Failed to send onboarding email to updated address: " . $e->getMessage());
                $emailSent = false;
                $emailDelivered = false;
            }

            // Update email delivery status
            $officer->user->update([
                'email_verified_at' => $emailDelivered ? now() : null,
            ]);

            $message = "Email updated from {$oldEmail} to {$newEmail} for {$officer->initials} {$officer->surname}.";
            if ($emailSent) {
                $message .= " New onboarding link has been sent to {$newEmail}.";
            } else {
                $message .= " Failed to send email. Onboarding link: {$onboardingLink}";
            }

            return redirect()->route('hrd.onboarding')
                ->with('success', $message)
                ->with('onboarding_link', $onboardingLink)
                ->with('temp_password', $tempPassword)
                ->with('email_sent', $emailSent);
        } catch (\Exception $e) {
            Log::error('Update onboarding email error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update email: ' . $e->getMessage());
        }
    }

    public function csvUpload(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            // Remove header row
            $headers = array_shift($csvData);
            
            // Validate headers
            $expectedHeaders = ['service_number', 'email', 'name'];
            if (count($headers) < 2 || !in_array('service_number', $headers) || !in_array('email', $headers)) {
                return redirect()->back()
                    ->with('error', 'CSV file must have columns: service_number, email, and optionally name');
            }

            $entries = [];
            $errors = [];

            foreach ($csvData as $rowIndex => $row) {
                if (count($row) < 2) continue; // Skip empty rows
                
                $entry = [];
                foreach ($headers as $index => $header) {
                    $header = trim(strtolower($header));
                    if (isset($row[$index])) {
                        $entry[$header] = trim($row[$index]);
                    }
                }

                // Validate entry
                $validator = Validator::make($entry, [
                    'service_number' => 'required|string|max:50',
                    'email' => 'required|email|max:255',
                    'name' => 'nullable|string|max:255',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $entries[] = $entry;
            }

            if (count($entries) > 10) {
                return redirect()->back()
                    ->with('error', 'CSV file contains more than 10 entries. Maximum 10 entries allowed per upload.');
            }

            if (empty($entries)) {
                return redirect()->back()
                    ->with('error', 'No valid entries found in CSV file.');
            }

            // Process bulk initiate
            $request->merge(['entries' => $entries]);
            return $this->bulkInitiate($request);

        } catch (\Exception $e) {
            Log::error('CSV upload error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to process CSV file: ' . $e->getMessage());
        }
    }
}

