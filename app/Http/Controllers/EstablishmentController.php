<?php

namespace App\Http\Controllers;

use App\Models\TrainingResult;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\NotificationService;
use App\Helpers\AppointmentNumberHelper;
use App\Helpers\ServiceNumberHelper;

class EstablishmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Establishment');
    }

    /**
     * Display training results ready for service number assignment
     */
    public function trainingResults()
    {
        $results = TrainingResult::sortedByPerformance()
            ->whereNull('service_number')
            ->where('status', 'PASS')
            ->with(['officer', 'uploadedBy'])
            ->get();

        // Group by rank for display
        $resultsByRank = $results->groupBy('rank');
        
        // Get last service number per rank
        $lastServiceNumbersByRank = [];
        foreach ($resultsByRank->keys() as $rank) {
            $lastServiceNumbersByRank[$rank] = ServiceNumberHelper::getLastServiceNumberForRank($rank);
        }

        // Get global last service number (for reference)
        $lastServiceNumber = Officer::whereNotNull('service_number')
            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
            ->value('service_number');

        return view('dashboards.establishment.training-results', compact(
            'results', 
            'lastServiceNumber',
            'resultsByRank',
            'lastServiceNumbersByRank'
        ));
    }

    /**
     * Show form to enter last service number and assign service numbers
     * Now groups by rank and assigns service numbers per rank based on performance
     */
    public function assignServiceNumbers(Request $request)
    {
        try {
            $request->validate([
                'rank_based' => 'nullable|boolean', // If true, assign by rank
            ]);

            $rankBased = $request->input('rank_based', true); // Default to rank-based

            DB::beginTransaction();
            
            // Get sorted training results without service numbers
            $results = TrainingResult::sortedByPerformance()
                ->whereNull('service_number')
                ->where('status', 'PASS') // Only assign to those who passed
                ->get();

            if ($results->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'No training results available for service number assignment. All results may already have service numbers assigned.')->withInput();
            }

            $assigned = 0;
            $rankStats = [];

            if ($rankBased) {
                // Group results by rank
                $resultsByRank = $results->groupBy('rank');

                foreach ($resultsByRank as $rank => $rankResults) {
                    // Get last service number for this rank
                    $lastServiceNumber = ServiceNumberHelper::getLastServiceNumberForRank($rank);
                    
                    $startNumber = 1;
                    if ($lastServiceNumber) {
                        preg_match('/(\d+)$/', $lastServiceNumber, $matches);
                        if (!empty($matches[1])) {
                            $startNumber = (int) $matches[1] + 1;
                        }
                    } else {
                        // If no service numbers exist for this rank, check global last
                        $globalLast = Officer::whereNotNull('service_number')
                            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
                            ->value('service_number');
                        
                        if ($globalLast) {
                            preg_match('/(\d+)$/', $globalLast, $matches);
                            $startNumber = !empty($matches[1]) ? (int) $matches[1] + 1 : 1;
                        }
                    }

                    $currentNumber = $startNumber;
                    $rankAssigned = 0;

                    // Assign service numbers to this rank's results (already sorted by performance)
                    foreach ($rankResults as $result) {
                // Generate service number: NCS + next number
                $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);

                // Check if service number already exists
                if (Officer::where('service_number', $serviceNumber)->exists()) {
                    $currentNumber++;
                            $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);
                }

                // Update training result with service number
                $result->update([
                    'service_number' => $serviceNumber,
                ]);

                // Update officer if exists
                if ($result->officer_id) {
                    $officer = Officer::find($result->officer_id);
                    if ($officer) {
                        $officer->update([
                            'service_number' => $serviceNumber,
                        ]);
                    }
                } else {
                    // Try to find officer by appointment number
                    $officer = Officer::where('appointment_number', $result->appointment_number)->first();
                    if ($officer) {
                        $officer->update([
                            'service_number' => $serviceNumber,
                        ]);
                        $result->update([
                            'officer_id' => $officer->id,
                        ]);
                    }
                }

                $currentNumber++;
                        $rankAssigned++;
                $assigned++;
                    }

                    $rankStats[$rank] = [
                        'count' => $rankAssigned,
                        'start' => $startNumber,
                        'end' => $currentNumber - 1
                    ];
                }
            } else {
                // Legacy: Global assignment (for backward compatibility)
                $lastServiceNumber = Officer::whereNotNull('service_number')
                    ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
                    ->value('service_number');

                $startNumber = 1;
                if ($lastServiceNumber) {
                    preg_match('/(\d+)$/', $lastServiceNumber, $matches);
                    if (!empty($matches[1])) {
                        $startNumber = (int) $matches[1] + 1;
                    }
                }

                $currentNumber = $startNumber;

                foreach ($results as $result) {
                    $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);

                    if (Officer::where('service_number', $serviceNumber)->exists()) {
                        $currentNumber++;
                        $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);
                    }

                    $result->update(['service_number' => $serviceNumber]);

                    if ($result->officer_id) {
                        $officer = Officer::find($result->officer_id);
                        if ($officer) {
                            $officer->update(['service_number' => $serviceNumber]);
                        }
                    } else {
                        $officer = Officer::where('appointment_number', $result->appointment_number)->first();
                        if ($officer) {
                            $officer->update(['service_number' => $serviceNumber]);
                            $result->update(['officer_id' => $officer->id]);
                        }
                    }

                    $currentNumber++;
                    $assigned++;
                }
            }

            DB::commit();

            // Notify ICT and officers about service number assignment
            if ($assigned > 0) {
                $assignedOfficers = [];
                foreach ($results as $result) {
                    if ($result->service_number) {
                        $officer = Officer::where('service_number', $result->service_number)->first();
                        if ($officer) {
                            $assignedOfficers[] = $officer;
                            
                            // Notify officer if they have a user account
                            $user = User::where('email', $officer->email)->first();
                            if ($user && $officer->user_id) {
                                $notificationService = app(NotificationService::class);
                                $notificationService->notifyServiceNumberAssignedToOfficer($officer, $officer->service_number);
                            }
                        }
                    }
                }
                
                // Notify ICT about service numbers ready for email creation
                if (!empty($assignedOfficers)) {
                    $notificationService = app(NotificationService::class);
                    $notificationService->notifyServiceNumbersForEmail($assignedOfficers);
                }
            }

            Log::info('Service numbers assigned successfully', [
                'count' => $assigned,
                'rank_based' => $rankBased,
                'rank_stats' => $rankStats
            ]);

            $message = "Successfully assigned {$assigned} service number(s)";
            if ($rankBased && !empty($rankStats)) {
                $message .= " grouped by rank: " . implode(', ', array_map(function($rank, $stats) {
                    return "{$rank} ({$stats['count']})";
                }, array_keys($rankStats), $rankStats));
            } else {
                $message .= " based on training performance.";
            }

            return redirect()->route('establishment.training-results')
                ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Validation error in service number assignment', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Establishment service number assignment error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to assign service numbers: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show form to add new recruit
     */
    /**
     * Get ranks and grade levels (shared helper)
     */
    private function getRanksAndGradeLevels()
    {
        $ranks = [
            'CGC',
            'DCG',
            'ACG',
            'CC',
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
            'GL 03',
            'GL 04',
            'GL 05',
            'GL 06',
            'GL 07',
            'GL 08',
            'GL 09',
            'GL 10',
            'GL 11',
            'GL 12',
            'GL 13',
            'GL 14',
            'GL 15',
            'GL 16',
            'GL 17',
            'GL 18',
        ];
        
        // Rank to Grade Level mapping
        $rankToGradeMap = [
            'CGC' => 'GL 18',
            'DCG' => 'GL 17',
            'ACG' => 'GL 16',
            'CC' => 'GL 15',
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
            'CA III' => 'GL 03',
        ];
        
        return compact('ranks', 'gradeLevels', 'rankToGradeMap');
    }

    /**
     * Step 1: Personal Information
     */
    public function createRecruitStep1()
    {
        // Clear any existing session data
        session()->forget(['recruit_step1', 'recruit_step2', 'recruit_step3', 'recruit_step4']);
        
        extract($this->getRanksAndGradeLevels());
        return view('forms.establishment.recruit-step1', compact('ranks', 'gradeLevels'));
    }

    /**
     * Save Step 1: Personal Information
     */
    public function saveRecruitStep1(Request $request)
    {
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

        // Check if email already exists
        if (User::where('email', $validated['email'])->exists()) {
            return back()->with('error', "Email '{$validated['email']}' already exists in the system.")->withInput();
        }

        if (Officer::where('email', $validated['email'])->exists()) {
            return back()->with('error', "Email '{$validated['email']}' already exists for an officer.")->withInput();
        }

        session(['recruit_step1' => $validated]);
        return redirect()->route('establishment.new-recruits.step2');
    }

    /**
     * Step 2: Employment Details
     */
    public function createRecruitStep2()
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete Step 1 first.');
        }

        extract($this->getRanksAndGradeLevels());
        $savedData = session('recruit_step2', []);
        return view('forms.establishment.recruit-step2', compact('ranks', 'gradeLevels', 'rankToGradeMap', 'savedData'));
    }

    /**
     * Save Step 2: Employment Details
     */
    public function saveRecruitStep2(Request $request)
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
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
            'education.*.discipline' => 'nullable|string|max:255',
        ]);

        session(['recruit_step2' => $validated]);
        return redirect()->route('establishment.new-recruits.step3');
    }

    /**
     * Step 3: Banking Information
     */
    public function createRecruitStep3()
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete Step 1 first.');
        }

        $savedData = session('recruit_step3', []);
        return view('forms.establishment.recruit-step3', compact('savedData'));
    }

    /**
     * Save Step 3: Banking Information
     */
    public function saveRecruitStep3(Request $request)
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete Step 1 first.');
        }

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:10|regex:/^\d{10}$/',
            'sort_code' => 'nullable|string|max:20',
            'pfa_name' => 'required|string|max:255',
            'rsa_number' => 'required|string|max:50|regex:/^PEN\d{12}$/',
        ]);

        session(['recruit_step3' => $validated]);
        return redirect()->route('establishment.new-recruits.step4');
    }

    /**
     * Step 4: Next of Kin
     */
    public function createRecruitStep4()
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete Step 1 first.');
        }

        $savedData = session('recruit_step4', []);
        return view('forms.establishment.recruit-step4', compact('savedData'));
    }

    /**
     * Save Step 4: Next of Kin
     */
    public function saveRecruitStep4(Request $request)
    {
        if (!session('recruit_step1')) {
            return redirect()->route('establishment.new-recruits.create')
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

        session(['recruit_step4' => $validated]);
        return redirect()->route('establishment.new-recruits.preview');
    }

    /**
     * Preview all steps before final submission
     */
    public function previewRecruit()
    {
        $step1 = session('recruit_step1');
        $step2 = session('recruit_step2');
        $step3 = session('recruit_step3');
        $step4 = session('recruit_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete all steps before preview.');
        }

        return view('forms.establishment.recruit-preview', compact('step1', 'step2', 'step3', 'step4'));
    }

    /**
     * Final submission - Create the recruit
     */
    public function finalSubmitRecruit(Request $request)
    {
        $step1 = session('recruit_step1');
        $step2 = session('recruit_step2');
        $step3 = session('recruit_step3');
        $step4 = session('recruit_step4');

        if (!$step1 || !$step2 || !$step3 || !$step4) {
            return redirect()->route('establishment.new-recruits.create')
                ->with('error', 'Please complete all steps before submitting.');
        }

        try {
            DB::beginTransaction();

            // Generate initials from first_name if needed
            $initials = $step1['initials'];
            if (empty($initials) && !empty($step1['first_name'])) {
                $initials = strtoupper(substr($step1['first_name'], 0, 2));
            }

            // Generate appointment number based on rank and GL level
            $prefix = AppointmentNumberHelper::getPrefix(
                $step2['substantive_rank'] ?? '',
                $step2['salary_grade_level'] ?? null
            );
            $appointmentNumber = AppointmentNumberHelper::generateNext($prefix);

            // Create officer record
            $recruit = Officer::create([
                'initials' => $initials,
                'surname' => $step1['surname'],
                'email' => $step1['email'],
                'personal_email' => $step1['email'],
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
                'appointment_number' => $appointmentNumber, // Auto-assigned based on rank and GL
                'service_number' => null, // Will be assigned after training
                'email_status' => 'personal',
                'is_active' => true,
                'created_by' => Auth::id(),
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
                        
                        \Storage::disk('public')->put($path, $imageData);
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

            DB::commit();

            // Clear session data
            session()->forget(['recruit_step1', 'recruit_step2', 'recruit_step3', 'recruit_step4']);

            // Notify TRADOC about new recruit
            $notificationService = app(NotificationService::class);
            $notificationService->notifyNewRecruit($recruit);

            return redirect()->route('establishment.new-recruits')
                ->with('success', "Recruit '{$initials} {$recruit->surname}' created successfully with appointment number {$appointmentNumber}. Service number will be assigned after training completion.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recruit creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create recruit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store new recruit(s) - single, bulk, or CSV
     */
    public function storeRecruit(Request $request)
    {
        $entryType = $request->input('entry_type', 'single');

        if ($entryType === 'csv') {
            // CSV upload
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            ]);

            DB::beginTransaction();
            try {
                $file = $request->file('csv_file');
                $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                if (empty($lines)) {
                    DB::rollBack();
                    return back()->with('error', 'CSV file is empty')->withInput();
                }
                
                // Parse CSV lines - handle quoted fields properly
                $data = [];
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    
                    // Handle case where entire line is wrapped in quotes (e.g., "col1,col2,col3")
                    if (substr($trimmed, 0, 1) === '"' && substr($trimmed, -1) === '"') {
                        // Remove outer quotes and parse the inner content as CSV
                        $unquoted = substr($trimmed, 1, -1);
                        $parsed = str_getcsv($unquoted);
                    } else {
                        // Normal CSV parsing
                        $parsed = str_getcsv($line);
                    }
                    
                    // Clean up each field (trim whitespace and remove any remaining quotes)
                    $parsed = array_map(function($field) {
                        $cleaned = trim($field);
                        // Remove surrounding quotes if present
                        if (substr($cleaned, 0, 1) === '"' && substr($cleaned, -1) === '"') {
                            $cleaned = substr($cleaned, 1, -1);
                        }
                        return trim($cleaned);
                    }, $parsed);
                    
                    // Filter out empty rows
                    if (!empty(array_filter($parsed))) {
                        $data[] = $parsed;
                    }
                }
                
                $header = array_shift($data); // Remove header row

                // Normalize header (case-insensitive, trim whitespace, remove quotes)
                $header = array_map(function($h) {
                    return strtolower(trim(trim($h), '"'));
                }, $header);

                // Find column indices
                $initialsIndex = array_search('initials', $header);
                $surnameIndex = array_search('surname', $header);
                $emailIndex = array_search('email', $header);
                $rankIndex = array_search('substantive_rank', $header);

                if ($initialsIndex === false || $surnameIndex === false || $emailIndex === false || $rankIndex === false) {
                    DB::rollBack();
                    $foundColumns = implode(', ', array_filter($header));
                    return back()->with('error', "CSV must contain columns: initials, surname, email, substantive_rank. Found columns: {$foundColumns}")->withInput();
                }

                $created = 0;
                $errors = [];
                $maxEntries = 50;
                $createdRecruitIds = [];

                foreach ($data as $index => $row) {
                    $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed

                    if ($created >= $maxEntries) {
                        $errors[] = "Maximum {$maxEntries} entries allowed. Stopped at row {$rowNumber}";
                        break;
                    }

                    if (count($row) < 4) {
                        $errors[] = "Row {$rowNumber}: Insufficient columns";
                        continue;
                    }

                    $initials = trim($row[$initialsIndex] ?? '');
                    $surname = trim($row[$surnameIndex] ?? '');
                    $email = trim($row[$emailIndex] ?? '');
                    $rank = trim($row[$rankIndex] ?? '');

                    // Validate data
                    if (empty($initials)) {
                        $errors[] = "Row {$rowNumber}: Initials is required";
                        continue;
                    }

                    if (empty($surname)) {
                        $errors[] = "Row {$rowNumber}: Surname is required";
                        continue;
                    }

                    // Strict email validation
                    $email = trim($email);
                    if (empty($email)) {
                        $errors[] = "Row {$rowNumber}: Email is required";
                        continue;
                    }
                    
                    // Validate email format strictly
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row {$rowNumber}: Invalid email format '{$email}'. Email must be in a valid format (e.g., user@example.com)";
                        continue;
                    }
                    
                    // Additional validation: check for basic email structure
                    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
                        $errors[] = "Row {$rowNumber}: Invalid email format '{$email}'. Email must contain @ symbol and a valid domain";
                        continue;
                    }

                    if (empty($rank)) {
                        $errors[] = "Row {$rowNumber}: Entry rank is required";
                        continue;
                    }

                    // Check if email already exists in users or officers table
                    if (User::where('email', $email)->exists()) {
                        $errors[] = "Row {$rowNumber}: Email '{$email}' already exists in the system (user account)";
                        continue;
                    }

                    if (Officer::where('email', $email)->exists()) {
                        $existingOfficer = Officer::where('email', $email)->first();
                        $errors[] = "Row {$rowNumber}: Email '{$email}' already exists for officer {$existingOfficer->initials} {$existingOfficer->surname} (ID: {$existingOfficer->id})";
                        continue;
                    }

                    $recruit = Officer::create([
                        'initials' => $initials,
                        'surname' => $surname,
                        'email' => $email,
                        'personal_email' => $email,
                        'substantive_rank' => $rank,
                        'email_status' => 'personal',
                        'is_active' => true,
                        'created_by' => Auth::id(),
                    ]);

                    $createdRecruitIds[] = $recruit->id;
                    $created++;
                }

                DB::commit();

                // Notify TRADOC about new recruits ready for training
                if ($created > 0 && !empty($createdRecruitIds)) {
                    $newRecruits = Officer::whereIn('id', $createdRecruitIds)->get();
                    if ($newRecruits->isNotEmpty()) {
                        $notificationService = app(NotificationService::class);
                        $notificationService->notifyRecruitsReadyForTraining($newRecruits->toArray());
                    }
                }

                $message = "Successfully created {$created} recruit(s) from CSV.";
                if (!empty($errors)) {
                    $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $message .= " and " . (count($errors) - 5) . " more.";
                    }
                }

                return redirect()->route('establishment.new-recruits')
                    ->with('success', $message)
                    ->with('csv_errors', $errors);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                Log::error('Establishment CSV recruit creation database error', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A'
                ]);
                
                // Check for unique constraint violations
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                    $errorMessage = 'One or more recruits in the CSV file have duplicate email addresses. ';
                    if (preg_match("/email/", $e->getMessage()) && preg_match("/values \(([^)]+)\)/", $e->getMessage(), $matches)) {
                        $errorMessage = "A recruit with a duplicate email address was found in the CSV file. Please check that all email addresses are unique.";
                    }
                    return back()->with('error', $errorMessage)->withInput();
                }
                
                return back()->with('error', 'Failed to process CSV file: ' . $e->getMessage())->withInput();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Establishment CSV recruit creation error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('error', 'Failed to process CSV file: ' . $e->getMessage())->withInput();
            }
        } elseif ($entryType === 'bulk') {
            $request->validate([
                'entries' => 'required|array|min:1|max:10',
                'entries.*.initials' => 'required|string|max:50',
                'entries.*.surname' => 'required|string|max:100',
                'entries.*.email' => ['required', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
                'entries.*.substantive_rank' => 'required|string|max:100',
            ], [
                'entries.*.email.email' => 'Each email must be a valid email address.',
                'entries.*.email.regex' => 'The email format is invalid. Please use a valid email address (e.g., user@example.com).',
            ]);

            DB::beginTransaction();
            try {
                $created = 0;
                $errors = [];
                $createdRecruitIds = [];

                foreach ($request->entries as $index => $entry) {
                    // Validate email format for bulk entries
                    $email = trim($entry['email'] ?? '');
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
                        $errors[] = "Entry " . ($index + 1) . ": Invalid email format '{$email}'. Email must be in a valid format (e.g., user@example.com)";
                        continue;
                    }
                    
                    // Check if email already exists in users or officers table
                    if (User::where('email', $email)->exists()) {
                        $errors[] = "Entry " . ($index + 1) . ": Email '{$email}' already exists in the system (user account)";
                        continue;
                    }

                    if (Officer::where('email', $email)->exists()) {
                        $existingOfficer = Officer::where('email', $email)->first();
                        $errors[] = "Entry " . ($index + 1) . ": Email '{$email}' already exists for officer {$existingOfficer->initials} {$existingOfficer->surname} (ID: {$existingOfficer->id})";
                        continue;
                    }

                    $recruit = Officer::create([
                        'initials' => $entry['initials'],
                        'surname' => $entry['surname'],
                        'email' => $entry['email'],
                        'personal_email' => $entry['email'],
                        'substantive_rank' => $entry['substantive_rank'],
                        'email_status' => 'personal',
                        'is_active' => true,
                        'created_by' => Auth::id(),
                    ]);

                    $createdRecruitIds[] = $recruit->id;
                    $created++;
                }

                DB::commit();

                // Notify TRADOC about new recruits ready for training
                if ($created > 0 && !empty($createdRecruitIds)) {
                    $newRecruits = Officer::whereIn('id', $createdRecruitIds)->get();
                    if ($newRecruits->isNotEmpty()) {
                        $notificationService = app(NotificationService::class);
                        $notificationService->notifyRecruitsReadyForTraining($newRecruits->toArray());
                    }
                }

                $message = "Successfully created {$created} recruit(s).";
                if (!empty($errors)) {
                    $message .= " Errors: " . implode(', ', $errors);
                }

                return redirect()->route('establishment.new-recruits')
                    ->with('success', $message);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                Log::error('Establishment bulk recruit creation database error', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A'
                ]);
                
                // Check for unique constraint violations
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                    $errorMessage = 'One or more recruits have duplicate email addresses. ';
                    if (preg_match("/email/", $e->getMessage())) {
                        $errorMessage = "A recruit with a duplicate email address was found. Please ensure all email addresses are unique and not already registered in the system.";
                    }
                    return back()->with('error', $errorMessage)->withInput();
                }
                
                return back()->with('error', 'Failed to create recruits: ' . $e->getMessage())->withInput();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Establishment bulk recruit creation error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('error', 'Failed to create recruits: ' . $e->getMessage())->withInput();
            }
        } else {
            // Single entry - matches onboarding Step 1: Personal Information
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
                'date_of_first_appointment' => 'required|date',
                'substantive_rank' => 'required|string|max:100',
                'salary_grade_level' => 'required|string|max:10',
            ], [
                'email.email' => 'The email must be a valid email address.',
                'email.regex' => 'The email format is invalid. Please use a valid email address (e.g., user@example.com).',
            ]);

            // Check if email already exists in users or officers table
            if (User::where('email', $validated['email'])->exists()) {
                return back()->with('error', "Email '{$validated['email']}' already exists in the system (user account).")->withInput();
            }

            if (Officer::where('email', $validated['email'])->exists()) {
                $existingOfficer = Officer::where('email', $validated['email'])->first();
                return back()->with('error', "Email '{$validated['email']}' already exists for officer {$existingOfficer->initials} {$existingOfficer->surname} (ID: {$existingOfficer->id}).")->withInput();
            }

            DB::beginTransaction();
            try {
                // Create recruit with all Step 1: Personal Information fields
                // Note: first_name and middle_name are collected but Officer model uses initials and surname
                // If initials not provided properly, generate from first_name
                $initials = $validated['initials'];
                if (empty($initials) && !empty($validated['first_name'])) {
                    // Generate initials from first name (first 2 letters)
                    $initials = strtoupper(substr($validated['first_name'], 0, 2));
                }
                
                $recruit = Officer::create([
                    'initials' => $initials,
                    'surname' => $validated['surname'],
                    'email' => $validated['email'],
                    'personal_email' => $validated['email'],
                    'sex' => $validated['sex'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'state_of_origin' => $validated['state_of_origin'],
                    'lga' => $validated['lga'],
                    'geopolitical_zone' => $validated['geopolitical_zone'],
                    'marital_status' => $validated['marital_status'],
                    'phone_number' => $validated['phone_number'],
                    'residential_address' => $validated['residential_address'],
                    'permanent_home_address' => $validated['permanent_home_address'],
                    'date_of_first_appointment' => $validated['date_of_first_appointment'],
                    'date_of_present_appointment' => $validated['date_of_first_appointment'],
                    'substantive_rank' => $validated['substantive_rank'],
                    'salary_grade_level' => $validated['salary_grade_level'],
                    'email_status' => 'personal',
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    // Fields that will be filled during onboarding Step 2 (Employment Details)
                    'entry_qualification' => null, // Will be provided during onboarding Step 2
                    'present_station' => null, // Will be assigned during onboarding Step 2
                    'date_posted_to_station' => null,
                    // Service number will be assigned after training completion
                    'service_number' => null,
                ]);

                DB::commit();

                // Notify TRADOC about new recruit ready for training
                $notificationService = app(NotificationService::class);
                $notificationService->notifyRecruitsReadyForTraining([$recruit]);

                return redirect()->route('establishment.new-recruits')
                    ->with('success', 'New recruit added successfully. You can now assign an appointment number.');
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                Log::error('Establishment new recruit creation database error', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A'
                ]);
                
                // Check for unique constraint violations
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                    $errorMessage = 'A recruit with this email address already exists in the system.';
                    if (preg_match("/email/", $e->getMessage())) {
                        $errorMessage = "Email '{$validated['email']}' is already registered to another officer. Please use a different email address.";
                    }
                    return back()->with('error', $errorMessage)->withInput();
                }
                
                return back()->with('error', 'Failed to add new recruit: ' . $e->getMessage())->withInput();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Establishment new recruit creation error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('error', 'Failed to add new recruit: ' . $e->getMessage())->withInput();
            }
        }
    }

    /**
     * Show allocate batch form
     */
    public function allocateBatch()
    {
        // Get officers without service numbers but with appointment numbers
        $officers = Officer::whereNull('service_number')
            ->whereNotNull('appointment_number')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->orderBy('appointment_number')
            ->get();

        // Get last service number
        $lastServiceNumber = Officer::whereNotNull('service_number')
            ->orderByRaw("CAST(SUBSTRING(service_number, 4) AS UNSIGNED) DESC")
            ->value('service_number');

        return view('forms.establishment.allocate-batch', compact('officers', 'lastServiceNumber'));
    }

    /**
     * Process batch allocation
     */
    public function processBatchAllocation(Request $request)
    {
        $request->validate([
            'last_service_number' => 'required|string|max:50',
            'allocation_type' => 'required|in:sequential,performance',
        ]);

        $lastServiceNumber = $request->last_service_number;
        $allocationType = $request->allocation_type;

        // Extract numeric part
        preg_match('/(\d+)$/', $lastServiceNumber, $matches);
        if (empty($matches[1])) {
            return back()->with('error', 'Invalid service number format. Must end with numbers.');
        }

        $lastNumber = (int) $matches[1];

        DB::beginTransaction();
        try {
            if ($allocationType === 'performance') {
                // Use training results for performance-based allocation
                $results = TrainingResult::sortedByPerformance()
                    ->whereNull('service_number')
                    ->where('status', 'PASS')
                    ->get();

                if ($results->isEmpty()) {
                    return back()->with('error', 'No training results available for performance-based allocation.');
                }

                $assigned = 0;
                $currentNumber = $lastNumber + 1;

                foreach ($results as $result) {
                    $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);

                    if (Officer::where('service_number', $serviceNumber)->exists()) {
                        $currentNumber++;
                        continue;
                    }

                    $result->update(['service_number' => $serviceNumber]);

                    if ($result->officer_id) {
                        $officer = Officer::find($result->officer_id);
                        if ($officer) {
                            $officer->update(['service_number' => $serviceNumber]);
                            // Notify officer about service number assignment
                            try {
                                $notificationService = app(NotificationService::class);
                                $notificationService->notifyServiceNumberAssignedToOfficer($officer, $serviceNumber);
                            } catch (\Exception $e) {
                                \Log::warning("Failed to send service number notification: " . $e->getMessage());
                            }
                        }
                    } else {
                        $officer = Officer::where('appointment_number', $result->appointment_number)->first();
                        if ($officer) {
                            $officer->update(['service_number' => $serviceNumber]);
                            $result->update(['officer_id' => $officer->id]);
                            // Notify officer about service number assignment
                            try {
                                $notificationService = app(NotificationService::class);
                                $notificationService->notifyServiceNumberAssignedToOfficer($officer, $serviceNumber);
                            } catch (\Exception $e) {
                                \Log::warning("Failed to send service number notification: " . $e->getMessage());
                            }
                        }
                    }

                    $currentNumber++;
                    $assigned++;
                }

                $message = "Successfully assigned {$assigned} service number(s) based on training performance.";
            } else {
                // Sequential allocation for officers with appointment numbers
                $officers = Officer::whereNull('service_number')
                    ->whereNotNull('appointment_number')
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->orderBy('appointment_number')
                    ->get();

                if ($officers->isEmpty()) {
                    return back()->with('error', 'No officers available for sequential allocation.');
                }

                $assigned = 0;
                $currentNumber = $lastNumber + 1;

                foreach ($officers as $officer) {
                    $serviceNumber = 'NCS' . str_pad($currentNumber, 5, '0', STR_PAD_LEFT);

                    if (Officer::where('service_number', $serviceNumber)->exists()) {
                        $currentNumber++;
                        continue;
                    }

                    $officer->update(['service_number' => $serviceNumber]);
                    // Notify officer about service number assignment
                    try {
                        $notificationService = app(NotificationService::class);
                        $notificationService->notifyServiceNumberAssignedToOfficer($officer, $serviceNumber);
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send service number notification: " . $e->getMessage());
                    }
                    $currentNumber++;
                    $assigned++;
                }

                $message = "Successfully assigned {$assigned} service number(s) sequentially.";
            }

            DB::commit();

            return redirect()->route('establishment.service-numbers')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Establishment batch allocation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to allocate service numbers: ' . $e->getMessage());
        }
    }

    /**
     * Assign appointment numbers to new recruits
     * Automatically assigns CDT or RCT prefix based on rank and GL level
     */
    public function assignAppointmentNumbers(Request $request)
    {
        $request->validate([
            'officer_ids' => 'required|array',
            'officer_ids.*' => 'exists:officers,id',
            'auto_prefix' => 'nullable|boolean', // If true, auto-determine prefix based on rank
            'appointment_number_prefix' => 'nullable|string|max:20', // Manual override prefix
        ]);

        DB::beginTransaction();
        try {
            $officers = Officer::whereIn('id', $request->officer_ids)
                ->whereNull('appointment_number')
                ->where(function($q) {
                    $q->whereNull('service_number')
                      ->orWhere('service_number', '')
                      ->orWhere('service_number', 'NCS'); // Handle edge case where mutator set it to "NCS"
                })
                ->get();

            if ($officers->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'No eligible recruits found for appointment number assignment.');
            }

            $autoPrefix = $request->input('auto_prefix', true); // Default to auto
            $manualPrefix = $request->input('appointment_number_prefix');
            
            $assigned = 0;
            $prefixCounters = []; // Track counters per prefix

            foreach ($officers as $officer) {
                // Determine prefix
                if ($autoPrefix && empty($manualPrefix)) {
                    // Auto-determine prefix based on rank and GL level
                    $prefix = AppointmentNumberHelper::getPrefixForOfficer($officer);
                } else {
                    // Use manual prefix or fallback to auto if manual not provided
                    $prefix = $manualPrefix ?? AppointmentNumberHelper::getPrefixForOfficer($officer);
                }

                // Initialize counter for this prefix if not exists
                if (!isset($prefixCounters[$prefix])) {
            // Get last appointment number with this prefix
            $lastAppointment = Officer::where('appointment_number', 'like', $prefix . '%')
                ->orderByRaw("CAST(SUBSTRING(appointment_number, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
                ->value('appointment_number');

                    $prefixCounters[$prefix] = 1;
            if ($lastAppointment) {
                preg_match('/(\d+)$/', $lastAppointment, $matches);
                if (!empty($matches[1])) {
                            $prefixCounters[$prefix] = (int) $matches[1] + 1;
                        }
                }
            }

                // Generate appointment number
                $appointmentNumber = $prefix . str_pad($prefixCounters[$prefix], 5, '0', STR_PAD_LEFT);

                // Check if appointment number already exists
                if (Officer::where('appointment_number', $appointmentNumber)->exists()) {
                    $prefixCounters[$prefix]++;
                    $appointmentNumber = $prefix . str_pad($prefixCounters[$prefix], 5, '0', STR_PAD_LEFT);
                }

                $officer->update([
                    'appointment_number' => $appointmentNumber,
                ]);

                $prefixCounters[$prefix]++;
                $assigned++;
            }

            DB::commit();

            // Notify TRADOC about recruits with appointment numbers ready for training
            if ($assigned > 0) {
                $assignedOfficers = Officer::whereIn('id', $request->officer_ids)
                    ->whereNotNull('appointment_number')
                    ->whereNull('service_number')
                    ->get();
                
                if ($assignedOfficers->isNotEmpty()) {
                    $notificationService = app(NotificationService::class);
                    $notificationService->notifyRecruitsReadyForTraining($assignedOfficers->toArray());
                }
            }

            $message = "Successfully assigned {$assigned} appointment number(s).";
            if ($autoPrefix && empty($manualPrefix)) {
                $message .= " Prefixes were automatically determined based on rank and GL level.";
            }

            return redirect()->route('establishment.new-recruits')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Establishment appointment number assignment error: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign appointment numbers: ' . $e->getMessage());
        }
    }

    /**
     * Delete a new recruit (only if no appointment number or service number assigned)
     */
    public function deleteRecruit($id)
    {
        try {
            $officer = Officer::findOrFail($id);

            // Only allow deletion if recruit has no appointment number or service number
            if ($officer->appointment_number || $officer->service_number) {
                $message = 'Cannot delete recruit. ';
                if ($officer->service_number) {
                    $message .= 'Service number has already been assigned.';
                } else {
                    $message .= 'Appointment number has already been assigned.';
                }
                return back()->with('error', $message);
            }

            $officerName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
            $officer->delete();

            Log::info('New recruit deleted', [
                'id' => $id,
                'name' => $officerName,
                'email' => $officer->email,
                'deleted_by' => Auth::id()
            ]);

            return back()->with('success', "Recruit '{$officerName}' has been deleted successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Attempted to delete non-existent recruit', ['id' => $id]);
            return back()->with('error', 'Recruit not found.');
        } catch (\Exception $e) {
            Log::error('Establishment recruit deletion error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to delete recruit: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete recruits (only those without service numbers)
     */
    public function bulkDeleteRecruits(Request $request)
    {
        $request->validate([
            'officer_ids' => 'required|array|min:1',
            'officer_ids.*' => 'exists:officers,id',
        ]);

        DB::beginTransaction();
        try {
            // Get officers that can be deleted (no service number)
            $officers = Officer::whereIn('id', $request->officer_ids)
                ->whereNull('service_number')
                ->get();

            if ($officers->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'No recruits can be deleted. All selected recruits have service numbers assigned.');
            }

            $deletedCount = 0;
            $deletedNames = [];

            foreach ($officers as $officer) {
                $officerName = trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? ''));
                $deletedNames[] = $officerName;
                $officer->delete();
                $deletedCount++;
            }

            DB::commit();

            Log::info('Bulk delete recruits', [
                'count' => $deletedCount,
                'officer_ids' => $request->officer_ids,
                'deleted_by' => Auth::id()
            ]);

            $message = "Successfully deleted {$deletedCount} recruit(s).";
            if (count($request->officer_ids) > $deletedCount) {
                $excluded = count($request->officer_ids) - $deletedCount;
                $message .= " {$excluded} recruit(s) were excluded because they have service numbers assigned.";
            }

            return redirect()->route('establishment.new-recruits')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Establishment bulk delete recruits error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'officer_ids' => $request->officer_ids ?? []
            ]);
            return back()->with('error', 'Failed to delete recruits: ' . $e->getMessage());
        }
    }
}
