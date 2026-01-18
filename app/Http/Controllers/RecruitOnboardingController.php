<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Pfa;
use App\Models\Officer;
use App\Models\User;
use App\Models\Zone;
use App\Models\Command;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RecruitOnboardingController extends Controller
{
    /**
     * No middleware - these routes are public and token-based
     */
    public function __construct()
    {
        // No middleware - public routes
    }

    /**
     * Validate onboarding token and get recruit
     */
    private function validateRecruitToken($token)
    {
        if (!$token) {
            Log::warning('Recruit Onboarding - No token provided');
            return null;
        }

        // Check if token exists at all
        $tokenExists = Officer::where('onboarding_token', $token)->exists();
        Log::info('Recruit Onboarding - Token lookup', [
            'token' => $token,
            'token_exists' => $tokenExists,
        ]);

        if (!$tokenExists) {
            Log::warning('Recruit Onboarding - Token not found in database', [
                'token' => $token,
            ]);
            return null;
        }

        // Check recruit with token and status
        $recruit = Officer::where('onboarding_token', $token)
            ->where(function ($q) {
                $q->where('onboarding_status', 'pending')
                    ->orWhere('onboarding_status', 'link_sent')
                    ->orWhere('onboarding_status', 'in_progress');
            })
            ->first();

        if (!$recruit) {
            // Get the recruit to see what status it has
            $recruitWithToken = Officer::where('onboarding_token', $token)->first();
            Log::warning('Recruit Onboarding - Token found but status invalid', [
                'token' => $token,
                'recruit_id' => $recruitWithToken ? $recruitWithToken->id : null,
                'onboarding_status' => $recruitWithToken ? $recruitWithToken->onboarding_status : null,
                'expected_statuses' => ['pending', 'link_sent', 'in_progress'],
            ]);
            return null;
        }

        Log::info('Recruit Onboarding - Token validated successfully', [
            'recruit_id' => $recruit->id,
            'onboarding_status' => $recruit->onboarding_status,
        ]);

        return $recruit;
    }

    /**
     * Get ranks and grade levels for forms
     */
    private function getRanksAndGradeLevels()
    {
        $ranks = [
            'DC',
            'AC',
            'CSC',
            'SC',
            'DSC',
            'ASC I',
            'ASC II',
            'IC',
            'AIC',
            'CA I',
            'CA II',
            'CA III',
        ];
        
        $gradeLevels = [
            'GL 03', 'GL 04', 'GL 05', 'GL 06', 'GL 07',
            'GL 08', 'GL 09', 'GL 10', 'GL 11', 'GL 12',
            'GL 13', 'GL 14', 'GL 16', 'GL 17',
        ];

        // Rank to Grade Level mapping
        $rankToGradeMap = [
            'DC' => 'GL 14',
            'AC' => 'GL 13',
            'CSC' => 'GL 12',
            'SC' => 'GL 11',
            'DSC' => 'GL 10',
            'ASC I' => 'GL 09',
            'ASC II' => 'GL 08',
            'IC' => 'GL 07',
            'AIC' => 'GL 06',
            'CA I' => 'GL 05',
            'CA II' => 'GL 04',
            'CA III' => 'GL 03'
        ];

        return compact('ranks', 'gradeLevels', 'rankToGradeMap');
    }

    /**
     * Recruit Onboarding Step 1: Personal Information (Token-based)
     */
    public function step1(Request $request)
    {
        $token = $request->query('token');
        
        // Log access attempt
        Log::info('Recruit Onboarding Step 1 accessed', [
            'token' => $token,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        $recruit = $this->validateRecruitToken($token);

        if (!$recruit) {
            Log::warning('Recruit Onboarding Step 1 - Invalid token', [
                'token' => $token,
                'ip' => $request->ip(),
            ]);
            return redirect()->route('login')
                ->with('error', 'Invalid or expired onboarding link. Please contact Establishment office.');
        }

        Log::info('Recruit Onboarding Step 1 - Valid token, recruit found', [
            'recruit_id' => $recruit->id,
            'recruit_email' => $recruit->email,
            'onboarding_status' => $recruit->onboarding_status,
            'token' => $token,
        ]);

        // Set session if not already set (only on initial visit)
        if (!session('recruit_onboarding_id') || session('recruit_onboarding_id') != $recruit->id) {
            // Clear any existing session data only if this is a new recruit
            session()->forget(['recruit_onboarding_step1', 'recruit_onboarding_step2', 'recruit_onboarding_step3', 'recruit_onboarding_step4']);
            session(['recruit_onboarding_id' => $recruit->id, 'recruit_onboarding_token' => $token]);
        } else {
            // Ensure token is set
            session(['recruit_onboarding_token' => $token]);
        }

        // Update status to in_progress
        if ($recruit->onboarding_status !== 'in_progress') {
            $recruit->update(['onboarding_status' => 'in_progress']);
            Log::info('Recruit Onboarding Step 1 - Status updated to in_progress', [
                'recruit_id' => $recruit->id,
            ]);
        }

        extract($this->getRanksAndGradeLevels());
        
        // Get saved data from session if available
        $savedData = session('recruit_onboarding_step1', []);
        
        Log::info('Recruit Onboarding Step 1 - Rendering view', [
            'recruit_id' => $recruit->id,
            'view' => 'forms.establishment.recruit-step1',
            'has_saved_data' => !empty($savedData),
        ]);
        
        return view('forms.establishment.recruit-step1', compact('ranks', 'gradeLevels', 'recruit', 'savedData'));
    }

    /**
     * Save Recruit Onboarding Step 1
     */
    public function saveStep1(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = session('recruit_onboarding_token');

        if (!$recruitId || !$token) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please use the onboarding link from your email.');
        }

        $recruit = Officer::find($recruitId);
        if (!$recruit || $recruit->onboarding_token !== $token) {
            return redirect()->route('login')
                ->with('error', 'Invalid onboarding session.');
        }

        $validated = $request->validate([
            'initials' => 'required|string|max:50',
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'sex' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'state_of_origin' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'geopolitical_zone' => 'required|string|max:255',
            'marital_status' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'residential_address' => 'required|string',
            'permanent_home_address' => 'required|string',
        ], [
            'email.email' => 'The email must be a valid email address.',
            'email.regex' => 'The email format is invalid.',
        ]);

        // Check if email already exists (excluding current recruit)
        if (User::where('email', $validated['email'])->exists()) {
            return back()->with('error', "Email '{$validated['email']}' already exists in the system.")->withInput();
        }

        if (Officer::where('email', $validated['email'])->where('id', '!=', $recruitId)->exists()) {
            return back()->with('error', "Email '{$validated['email']}' already exists for another officer.")->withInput();
        }

        session(['recruit_onboarding_step1' => $validated]);
        return redirect()->route('recruit.onboarding.step2', ['token' => $token]);
    }

    /**
     * Recruit Onboarding Step 2: Employment Details
     */
    public function step2(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = $request->query('token') ?? session('recruit_onboarding_token');

        if (!$recruitId || !$token) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please use the onboarding link from your email.');
        }

        if (!session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $recruit = Officer::find($recruitId);
        extract($this->getRanksAndGradeLevels());
        $savedData = session('recruit_onboarding_step2', []);
        $zones = Zone::orderBy('name')->get();
        $commands = Command::with('zone')->orderBy('name')->get();
        
        // Find TRADOC command and set defaults
        $tradocCommand = Command::where(function($query) {
                $query->where('name', 'LIKE', '%TRADOC%')
                      ->orWhere('name', 'LIKE', '%TRAINING%')
                      ->orWhere('code', 'LIKE', '%TRADOC%')
                      ->orWhere('code', 'LIKE', '%TRAINING%');
            })
            ->where('is_active', true)
            ->first();
        
        // Populate savedData with recruit data if not already set
        if ($recruit && empty($savedData['date_of_first_appointment'])) {
            $savedData['date_of_first_appointment'] = $recruit->date_of_first_appointment ? \Carbon\Carbon::parse($recruit->date_of_first_appointment)->format('Y-m-d') : '';
        }
        if ($recruit && empty($savedData['date_of_present_appointment'])) {
            $savedData['date_of_present_appointment'] = $recruit->date_of_present_appointment ? \Carbon\Carbon::parse($recruit->date_of_present_appointment)->format('Y-m-d') : '';
        }
        if ($recruit && empty($savedData['date_posted_to_station'])) {
            $savedData['date_posted_to_station'] = $recruit->date_posted_to_station ? \Carbon\Carbon::parse($recruit->date_posted_to_station)->format('Y-m-d') : '';
        }
        if ($recruit && empty($savedData['command_id'])) {
            $savedData['command_id'] = $recruit->command_id;
        }
        if ($recruit && empty($savedData['unit'])) {
            $savedData['unit'] = $recruit->unit;
        }
        
        // Set default zone and command if not already saved
        if (empty($savedData['zone_id']) && $tradocCommand && $tradocCommand->zone_id) {
            $savedData['zone_id'] = $tradocCommand->zone_id;
        }
        if (empty($savedData['command_id']) && $tradocCommand) {
            $savedData['command_id'] = $tradocCommand->id;
        }
        
        return view('forms.establishment.recruit-step2', compact('ranks', 'gradeLevels', 'rankToGradeMap', 'savedData', 'recruit', 'zones', 'commands'));
    }

    /**
     * Save Recruit Onboarding Step 2
     */
    public function saveStep2(Request $request)
    {
        $token = $request->input('token') ?? $request->query('token') ?? session('recruit_onboarding_token');
        $recruitId = session('recruit_onboarding_id');
        
        // If token is provided, validate and set session
        if ($token && !session('recruit_onboarding_token')) {
            $recruit = $this->validateRecruitToken($token);
            if ($recruit) {
                session(['recruit_onboarding_id' => $recruit->id, 'recruit_onboarding_token' => $token]);
                $recruitId = $recruit->id;
            }
        }

        if (!$recruitId || !$token || !session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'date_of_first_appointment' => 'required|date',
            'date_of_present_appointment' => 'required|date',
            'substantive_rank' => 'required|string|max:100',
            'salary_grade_level' => 'required|string|max:10',
            'zone_id' => 'required|exists:zones,id',
            'command_id' => 'required|exists:commands,id',
            'date_posted_to_station' => 'required|date',
            'unit' => 'nullable|string|max:255',
            'education' => 'required|array|min:1',
            'education.*.university' => 'required|string|max:255',
            'education.*.qualification' => 'required|string|max:255',
            'education.*.year_obtained' => 'required|integer|min:1950|max:' . date('Y'),
            'education.*.discipline' => 'nullable|string|max:255',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpeg,jpg,png|max:5120',
        ]);
        
        // Handle document uploads - store file info for preview (same as step4)
        // Get existing documents from session first
        $existingStep2 = session('recruit_onboarding_step2', []);
        $existingDocuments = isset($existingStep2['documents']) && is_array($existingStep2['documents']) 
            ? $existingStep2['documents'] 
            : [];
        
        if ($request->hasFile('documents')) {
            // Add new documents to existing ones
            foreach ($request->file('documents') as $file) {
                $tempPath = $file->store('temp/recruit-documents', 'local');
                $existingDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'temp_path' => $tempPath,
                ];
            }
            $validated['documents'] = $existingDocuments;
            Log::info('Recruit Onboarding Step 2 - Documents saved', [
                'new_count' => count($request->file('documents')),
                'total_count' => count($existingDocuments),
                'files' => array_map(fn($d) => $d['name'], $existingDocuments),
            ]);
        } else {
            // If no new documents uploaded, use existing ones from session
            if (count($existingDocuments) > 0) {
                $validated['documents'] = $existingDocuments;
                Log::info('Recruit Onboarding Step 2 - Using existing documents from session', [
                    'count' => count($existingDocuments),
                ]);
            }
        }
        
        // Validate that at least one document exists (either new or from session)
        if (empty($validated['documents']) || count($validated['documents']) < 1) {
            return back()->withErrors(['documents' => 'At least one document is required.'])->withInput();
        }

        session(['recruit_onboarding_step2' => $validated]);
        return redirect()->route('recruit.onboarding.step3', ['token' => $token]);
    }

    /**
     * Recruit Onboarding Step 3: Banking Information
     */
    public function step3(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = $request->query('token') ?? session('recruit_onboarding_token');

        if (!$recruitId || !$token || !session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $savedData = session('recruit_onboarding_step3', []);

        $banks = Bank::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'account_number_digits']);

        $pfas = Pfa::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'rsa_prefix', 'rsa_digits']);

        return view('forms.establishment.recruit-step3', compact('savedData', 'banks', 'pfas'));
    }

    /**
     * Save Recruit Onboarding Step 3
     */
    public function saveStep3(Request $request)
    {
        $token = $request->input('token') ?? $request->query('token') ?? session('recruit_onboarding_token');
        $recruitId = session('recruit_onboarding_id');
        
        // If token is provided, validate and set session
        if ($token && !session('recruit_onboarding_token')) {
            $recruit = $this->validateRecruitToken($token);
            if ($recruit) {
                session(['recruit_onboarding_id' => $recruit->id, 'recruit_onboarding_token' => $token]);
                $recruitId = $recruit->id;
            }
        }

        if (!$recruitId || !$token || !session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:255',
                Rule::exists('banks', 'name')->where('is_active', true),
            ],
            'bank_account_number' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $bank = Bank::query()
                        ->where('name', $request->input('bank_name'))
                        ->where('is_active', true)
                        ->first();

                    if (!$bank) {
                        return;
                    }

                    $digits = max(1, (int) $bank->account_number_digits);
                    if (!preg_match('/^\d{' . $digits . '}$/', (string) $value)) {
                        $fail("Bank Account Number must be exactly {$digits} digits.");
                    }
                },
            ],
            'sort_code' => 'nullable|string|max:20',
            'pfa_name' => [
                'required',
                'string',
                'max:255',
                Rule::exists('pfas', 'name')->where('is_active', true),
            ],
            'rsa_number' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $pfa = Pfa::query()
                        ->where('name', $request->input('pfa_name'))
                        ->where('is_active', true)
                        ->first();

                    if (!$pfa) {
                        return;
                    }

                    $prefix = strtoupper((string) $pfa->rsa_prefix);
                    $digits = max(1, (int) $pfa->rsa_digits);
                    $pattern = '/^' . preg_quote($prefix, '/') . '\d{' . $digits . '}$/';

                    if (!preg_match($pattern, (string) $value)) {
                        $example = $prefix . str_repeat('0', $digits);
                        $fail("RSA Number must be {$prefix} followed by {$digits} digits (e.g., {$example}).");
                    }
                },
            ],
        ]);

        session(['recruit_onboarding_step3' => $validated]);
        return redirect()->route('recruit.onboarding.step4', ['token' => $token]);
    }

    /**
     * Recruit Onboarding Step 4: Next of Kin
     */
    public function step4(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = $request->query('token') ?? session('recruit_onboarding_token');

        if (!$recruitId || !$token || !session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $savedData = session('recruit_onboarding_step4', []);
        return view('forms.establishment.recruit-step4', compact('savedData'));
    }

    /**
     * Save Recruit Onboarding Step 4
     */
    public function saveStep4(Request $request)
    {
        $token = $request->input('token') ?? $request->query('token') ?? session('recruit_onboarding_token');
        $recruitId = session('recruit_onboarding_id');
        
        // If token is provided, validate and set session
        if ($token && !session('recruit_onboarding_token')) {
            $recruit = $this->validateRecruitToken($token);
            if ($recruit) {
                session(['recruit_onboarding_id' => $recruit->id, 'recruit_onboarding_token' => $token]);
                $recruitId = $recruit->id;
            }
        }

        if (!$recruitId || !$token || !session('recruit_onboarding_step1')) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'next_of_kin' => 'required|array|min:1|max:5',
            'next_of_kin.*.name' => 'required|string|max:255',
            'next_of_kin.*.relationship' => 'required|string|max:50',
            'next_of_kin.*.phone_number' => 'required|string|max:20',
            'next_of_kin.*.email' => 'nullable|email|max:255',
            'next_of_kin.*.address' => 'required|string',
            'next_of_kin.*.is_primary' => 'nullable|in:0,1',
            'profile_picture_data' => 'required|string',
            'interdicted' => 'nullable|boolean',
            'suspended' => 'nullable|boolean',
            'quartered' => 'nullable|boolean',
        ]);
        
        // Documents are now only uploaded in Step 2, so no document handling needed here
        // Get existing documents from step2 session if any
        $step2Data = session('recruit_onboarding_step2', []);
        if (isset($step2Data['documents']) && is_array($step2Data['documents'])) {
            // Keep step2 documents in step4 session for preview, but they're already saved in step2
            $validated['documents'] = $step2Data['documents'];
        }

        // Validate at least one primary next of kin
        $hasPrimary = false;
        foreach ($validated['next_of_kin'] as $nok) {
            if (isset($nok['is_primary']) && $nok['is_primary'] == '1') {
                $hasPrimary = true;
                break;
            }
        }

        if (!$hasPrimary) {
            return back()->withErrors(['next_of_kin' => 'At least one next of kin must be marked as primary.'])->withInput();
        }

        // Validate profile picture
        if (empty($validated['profile_picture_data']) || !preg_match('/^data:image\//', $validated['profile_picture_data'])) {
            return back()->withErrors(['profile_picture_data' => 'Please upload your official passport photo.'])->withInput();
        }

        session(['recruit_onboarding_step4' => $validated]);
        return redirect()->route('recruit.onboarding.preview', ['token' => $token]);
    }

    /**
     * Preview document from temp storage
     */
    public function documentPreview(Request $request)
    {
        $path = $request->query('path');
        
        if (!$path) {
            abort(404);
        }
        
        // Security: ensure path is within temp/recruit-documents
        if (!str_starts_with($path, 'temp/recruit-documents/')) {
            abort(403, 'Invalid file path');
        }
        
        $fullPath = storage_path('app/' . $path);
        
        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }
        
        $mimeType = mime_content_type($fullPath);
        $fileName = basename($path);
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Recruit Onboarding Preview
     */
    public function preview(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = $request->query('token') ?? session('recruit_onboarding_token');

        if (!$recruitId || !$token) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please use the onboarding link from your email.');
        }

        $step1 = session('recruit_onboarding_step1');
        $step2 = session('recruit_onboarding_step2');
        $step3 = session('recruit_onboarding_step3');
        $step4 = session('recruit_onboarding_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete all steps before preview.');
        }

        // Log step4 data for debugging
        Log::info('Recruit Onboarding Preview - Step4 data', [
            'has_documents' => isset($step4['documents']),
            'documents_count' => isset($step4['documents']) && is_array($step4['documents']) ? count($step4['documents']) : 0,
            'has_profile_picture' => isset($step4['profile_picture_data']) && !empty($step4['profile_picture_data']),
            'has_next_of_kin' => isset($step4['next_of_kin']),
        ]);

        // Load zone and command names for display
        $zone = null;
        $command = null;
        if (!empty($step2['zone_id'])) {
            $zone = Zone::find($step2['zone_id']);
        }
        if (!empty($step2['command_id'])) {
            $command = Command::find($step2['command_id']);
        }

        return view('forms.establishment.recruit-preview', compact('step1', 'step2', 'step3', 'step4', 'zone', 'command'));
    }

    /**
     * Final Submit Recruit Onboarding
     */
    public function finalSubmit(Request $request)
    {
        $recruitId = session('recruit_onboarding_id');
        $token = session('recruit_onboarding_token');

        if (!$recruitId || !$token) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please use the onboarding link from your email.');
        }

        $recruit = Officer::find($recruitId);
        if (!$recruit || $recruit->onboarding_token !== $token) {
            return redirect()->route('login')
                ->with('error', 'Invalid onboarding session.');
        }

        $step1 = session('recruit_onboarding_step1');
        $step2 = session('recruit_onboarding_step2');
        $step3 = session('recruit_onboarding_step3');
        $step4 = session('recruit_onboarding_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('recruit.onboarding.step1', ['token' => $token])
                ->with('error', 'Please complete all steps before submitting.');
        }

        try {
            DB::beginTransaction();

            // Update recruit with all information
            $recruit->update([
                'initials' => $step1['initials'],
                'surname' => $step1['surname'],
                'email' => $step1['email'],
                'sex' => $step1['sex'],
                'date_of_birth' => $step1['date_of_birth'],
                'state_of_origin' => $step1['state_of_origin'],
                'lga' => $step1['lga'],
                'geopolitical_zone' => $step1['geopolitical_zone'],
                'marital_status' => $step1['marital_status'],
                'phone_number' => $step1['phone_number'],
                'residential_address' => $step1['residential_address'],
                'permanent_home_address' => $step1['permanent_home_address'],
                'date_of_first_appointment' => $step2['date_of_first_appointment'],
                'date_of_present_appointment' => $step2['date_of_present_appointment'],
                'substantive_rank' => $step2['substantive_rank'],
                'salary_grade_level' => $step2['salary_grade_level'],
                'present_station' => $step2['command_id'],
                'date_posted_to_station' => $step2['date_posted_to_station'],
                'unit' => $step2['unit'] ?? null,
                'entry_qualification' => isset($step2['education'][0]) ? $step2['education'][0]['qualification'] : null,
                'discipline' => isset($step2['education'][0]) ? ($step2['education'][0]['discipline'] ?? null) : null,
                'additional_qualification' => json_encode($step2['education']),
                'bank_name' => $step3['bank_name'],
                'bank_account_number' => $step3['bank_account_number'],
                'sort_code' => $step3['sort_code'] ?? null,
                'pfa_name' => $step3['pfa_name'],
                'rsa_number' => $step3['rsa_number'],
                'interdicted' => $step4['interdicted'] ?? false,
                'suspended' => $step4['suspended'] ?? false,
                'quartered' => $step4['quartered'] ?? false,
                'onboarding_status' => 'completed',
                'onboarding_completed_at' => now(),
            ]);

            // Handle profile picture upload
            if (!empty($step4['profile_picture_data'])) {
                try {
                    $base64Image = $step4['profile_picture_data'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));
                        $extension = $matches[1];
                        $filename = 'profile_' . $recruit->id . '_' . time() . '.' . $extension;
                        $path = 'officer-profiles/' . $filename;

                        Storage::disk('public')->put($path, $imageData);
                        $recruit->update(['profile_picture_url' => $path]);
                    }
                } catch (\Exception $e) {
                    Log::error('Profile picture upload error: ' . $e->getMessage());
                }
            }

            // Create next of kin records
            foreach ($step4['next_of_kin'] as $nokData) {
                \App\Models\NextOfKin::create([
                    'officer_id' => $recruit->id,
                    'name' => $nokData['name'],
                    'relationship' => $nokData['relationship'],
                    'phone_number' => $nokData['phone_number'],
                    'email' => $nokData['email'] ?? null,
                    'address' => $nokData['address'],
                    'is_primary' => isset($nokData['is_primary']) && $nokData['is_primary'] == '1',
                ]);
            }

            // Merge documents from step2 and step4 (they save to the same place)
            $allDocuments = [];
            if (isset($step2['documents']) && is_array($step2['documents']) && count($step2['documents']) > 0) {
                $allDocuments = array_merge($allDocuments, $step2['documents']);
            }
            if (isset($step4['documents']) && is_array($step4['documents']) && count($step4['documents']) > 0) {
                $allDocuments = array_merge($allDocuments, $step4['documents']);
            }
            
            // Save uploaded documents to permanent storage and create OfficerDocument records
            Log::info('Recruit Onboarding Final Submit - Checking for documents', [
                'recruit_id' => $recruit->id,
                'step2_documents_count' => isset($step2['documents']) && is_array($step2['documents']) ? count($step2['documents']) : 0,
                'step4_documents_count' => isset($step4['documents']) && is_array($step4['documents']) ? count($step4['documents']) : 0,
                'total_documents_count' => count($allDocuments),
            ]);
            
            if (count($allDocuments) > 0) {
                $savedCount = 0;
                $failedCount = 0;
                
                foreach ($allDocuments as $index => $docData) {
                    Log::info('Processing document', [
                        'index' => $index,
                        'has_temp_path' => isset($docData['temp_path']),
                        'temp_path' => $docData['temp_path'] ?? 'N/A',
                        'name' => $docData['name'] ?? 'N/A',
                    ]);
                    
                    if (isset($docData['temp_path']) && Storage::disk('local')->exists($docData['temp_path'])) {
                        try {
                            // Move file from temp to permanent storage
                            $tempPath = $docData['temp_path'];
                            $fileName = $docData['name'] ?? 'document';
                            // Sanitize filename
                            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                            $permanentPath = 'officer-documents/' . $recruit->id . '/' . time() . '_' . $index . '_' . $fileName;
                            
                            // Ensure directory exists
                            $directory = dirname($permanentPath);
                            if (!Storage::disk('public')->exists($directory)) {
                                Storage::disk('public')->makeDirectory($directory);
                            }
                            
                            // Copy file to permanent storage
                            $fileContent = Storage::disk('local')->get($tempPath);
                            Storage::disk('public')->put($permanentPath, $fileContent);
                            
                            // Create OfficerDocument record
                            \App\Models\OfficerDocument::create([
                                'officer_id' => $recruit->id,
                                'document_type' => 'onboarding',
                                'file_name' => $docData['name'] ?? 'document',
                                'file_path' => $permanentPath,
                                'file_size' => $docData['size'] ?? 0,
                                'mime_type' => $docData['type'] ?? 'application/octet-stream',
                                'uploaded_by' => null, // Recruit uploaded, no user ID
                            ]);
                            
                            $savedCount++;
                            Log::info('Document saved successfully', [
                                'recruit_id' => $recruit->id,
                                'file_name' => $docData['name'] ?? 'document',
                                'permanent_path' => $permanentPath,
                            ]);
                            
                            // Delete temporary file
                            Storage::disk('local')->delete($tempPath);
                        } catch (\Exception $e) {
                            $failedCount++;
                            Log::error('Failed to save recruit document', [
                                'recruit_id' => $recruit->id,
                                'document' => $docData['name'] ?? 'unknown',
                                'temp_path' => $docData['temp_path'] ?? 'N/A',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    } else {
                        $failedCount++;
                        Log::warning('Document temp path not found or file does not exist', [
                            'recruit_id' => $recruit->id,
                            'document' => $docData['name'] ?? 'unknown',
                            'temp_path' => $docData['temp_path'] ?? 'N/A',
                            'file_exists' => isset($docData['temp_path']) ? Storage::disk('local')->exists($docData['temp_path']) : false,
                        ]);
                    }
                }
                
                Log::info('Document saving summary', [
                    'recruit_id' => $recruit->id,
                    'total_documents' => count($allDocuments),
                    'saved_count' => $savedCount,
                    'failed_count' => $failedCount,
                ]);
            } else {
                Log::warning('No documents found in step2 or step4 data', [
                    'recruit_id' => $recruit->id,
                    'step2_keys' => isset($step2) ? array_keys($step2) : [],
                    'step4_keys' => isset($step4) ? array_keys($step4) : [],
                ]);
            }

            DB::commit();

            // Clear session data
            session()->forget([
                'recruit_onboarding_step1',
                'recruit_onboarding_step2',
                'recruit_onboarding_step3',
                'recruit_onboarding_step4',
                'recruit_onboarding_id',
                'recruit_onboarding_token'
            ]);

            // Notify Establishment about completed onboarding
            $notificationService = app(NotificationService::class);
            $notificationService->notifyRecruitOnboardingCompleted($recruit);

            // Send success email to recruit via job
            if ($recruit->email) {
                \App\Jobs\SendRecruitOnboardingSuccessMailJob::dispatch($recruit);
            }

            return view('forms.establishment.recruit-onboarding-complete', compact('recruit'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recruit onboarding submission error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to submit onboarding: ' . $e->getMessage())
                ->withInput();
        }
    }
}

