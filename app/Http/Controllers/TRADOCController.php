<?php

namespace App\Http\Controllers;

use App\Models\TrainingResult;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class TRADOCController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:TRADOC');
    }

    /**
     * Display dashboard with training results
     * Simplified: Results sorted by performance (highest to lowest), filterable by rank
     */
    public function index(Request $request)
    {
        $query = TrainingResult::with(['officer', 'uploadedBy'])
            ->sortedByPerformance();

        // Filter by rank if provided - check both stored rank and officer's substantive_rank
        $selectedRank = $request->input('rank');
        if ($selectedRank) {
            $query->where(function($q) use ($selectedRank) {
                $q->where('rank', $selectedRank)
                  ->orWhereHas('officer', function($officerQuery) use ($selectedRank) {
                      $officerQuery->where('substantive_rank', $selectedRank);
                  });
            });
        }

        $results = $query->paginate(50)->withQueryString();

        // Get available ranks from both stored rank and officer's substantive_rank
        $storedRanks = TrainingResult::whereNotNull('rank')
            ->distinct()
            ->pluck('rank')
            ->toArray();
        
        $officerRanks = TrainingResult::whereHas('officer', function($q) {
                $q->whereNotNull('substantive_rank');
            })
            ->with('officer')
            ->get()
            ->pluck('officer.substantive_rank')
            ->filter()
            ->unique()
            ->toArray();
        
        $availableRanks = array_unique(array_merge($storedRanks, $officerRanks));
        sort($availableRanks);

        // Calculate stats (respecting rank filter if selected)
        $statsQuery = TrainingResult::query();
        if ($selectedRank) {
            $statsQuery->where(function($q) use ($selectedRank) {
                $q->where('rank', $selectedRank)
                  ->orWhereHas('officer', function($officerQuery) use ($selectedRank) {
                      $officerQuery->where('substantive_rank', $selectedRank);
                  });
            });
        }

        $stats = [
            'total' => $statsQuery->count(),
            'pending_service_number' => (clone $statsQuery)->whereNull('service_number')->count(),
        ];

        return view('dashboards.tradoc.index', compact('results', 'stats', 'availableRanks', 'selectedRank'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        // Get available ranks from new recruits
        $availableRanks = Officer::whereNotNull('appointment_number')
            ->whereNull('service_number')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->whereNotNull('substantive_rank')
            ->distinct()
            ->orderBy('substantive_rank')
            ->pluck('substantive_rank')
            ->toArray();

        return view('forms.tradoc.upload', compact('availableRanks'));
    }

    /**
     * Handle CSV upload and process training results
     */
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'rank' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            
            // Check if first row looks like a header (contains text like "Appointment Number" or "Officer Name")
            // If it does, remove it. Otherwise, treat all rows as data.
            if (!empty($data)) {
                $firstRow = $data[0];
                $isHeader = false;
                if (count($firstRow) >= 2) {
                    $firstRowLower = strtolower(implode(' ', $firstRow));
                    if (strpos($firstRowLower, 'appointment') !== false || 
                        strpos($firstRowLower, 'officer') !== false || 
                        strpos($firstRowLower, 'name') !== false ||
                        strpos($firstRowLower, 'score') !== false) {
                        $isHeader = true;
                    }
                }
                
                if ($isHeader) {
                    array_shift($data); // Remove header row
                }
            }

            // Expected CSV format: Appointment Number, Officer Name, Training Score
            // Status (PASS/FAIL) is automatically determined: Score >= 50 = PASS, Score < 50 = FAIL
            $selectedRank = trim($request->input('rank'));
            
            if (empty($selectedRank)) {
                DB::rollBack();
                return back()->with('error', 'Rank selection is required. Please select a rank before uploading.')->withInput();
            }
            
            // Log for debugging
            Log::info('TRADOC CSV upload started', [
                'selected_rank' => $selectedRank,
                'rank_length' => strlen($selectedRank),
                'uploaded_by' => Auth::id()
            ]);
            
            $results = [];
            $errors = [];

            Log::info('TRADOC CSV data rows count', [
                'total_rows' => count($data),
                'selected_rank' => $selectedRank
            ]);
            
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
                
                Log::debug('TRADOC CSV processing row', [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'column_count' => count($row)
                ]);

                if (count($row) < 3) {
                    $errors[] = "Row {$rowNumber}: Insufficient columns. Expected: Appointment Number, Officer Name, Training Score";
                    continue;
                }

                $appointmentNumber = trim($row[0]);
                $officerName = trim($row[1]);
                $trainingScore = trim($row[2]);

                // Validate data
                if (empty($appointmentNumber)) {
                    $errors[] = "Row {$rowNumber}: Appointment Number is required";
                    continue;
                }

                if (empty($officerName)) {
                    $errors[] = "Row {$rowNumber}: Officer Name is required";
                    continue;
                }

                if (!is_numeric($trainingScore) || $trainingScore < 0 || $trainingScore > 100) {
                    $errors[] = "Row {$rowNumber}: Training Score must be a number between 0 and 100";
                    continue;
                }

                // Auto-determine status based on score: >= 50 = PASS, < 50 = FAIL
                $status = (float)$trainingScore >= 50 ? 'PASS' : 'FAIL';

                // Check if appointment number exists in new recruits list (officers without service number)
                // Also validate that the recruit belongs to the selected rank (behind the scenes)
                $newRecruit = Officer::where('appointment_number', $appointmentNumber)
                    ->whereNull('service_number')
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->whereRaw('TRIM(substantive_rank) = ?', [trim($selectedRank)]) // Validate rank behind the scenes (with trim for safety)
                    ->first();

                if (!$newRecruit) {
                    // Check if officer exists at all (might have service number already)
                    $existingOfficer = Officer::where('appointment_number', $appointmentNumber)->first();
                    
                    // Debug logging
                    Log::info('TRADOC CSV validation failed for row', [
                        'row_number' => $rowNumber,
                        'appointment_number' => $appointmentNumber,
                        'selected_rank' => $selectedRank,
                        'officer_found' => $existingOfficer ? 'yes' : 'no',
                        'officer_rank' => $existingOfficer ? $existingOfficer->substantive_rank : 'N/A',
                        'officer_service_number' => $existingOfficer ? ($existingOfficer->service_number ?? 'NULL') : 'N/A',
                        'officer_active' => $existingOfficer ? ($existingOfficer->is_active ? 'yes' : 'no') : 'N/A',
                    ]);
                    
                    if ($existingOfficer) {
                        if ($existingOfficer->service_number) {
                            $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' already has a service number assigned ({$existingOfficer->service_number}). Only new recruits without service numbers can have training results uploaded.";
                        } elseif (trim($existingOfficer->substantive_rank) !== trim($selectedRank)) {
                            $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' belongs to rank '{$existingOfficer->substantive_rank}', but you selected rank '{$selectedRank}'. Please ensure all recruits in your CSV belong to the selected rank.";
                        } else {
                            $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' is not in the active new recruits list.";
                        }
                    } else {
                        $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' not found in the new recruits list for rank '{$selectedRank}'. Please ensure the recruit has been added and assigned an appointment number first.";
                    }
                    continue;
                }

                $officer = $newRecruit;

                $results[] = [
                    'appointment_number' => $appointmentNumber,
                    'officer_id' => $officer?->id,
                    'officer_name' => $officerName,
                    'training_score' => $trainingScore,
                    'status' => $status,
                    'rank' => $officer->substantive_rank ?? 'Unknown', // Store officer's substantive rank
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($errors)) {
                DB::rollBack();
                Log::warning('TRADOC CSV upload validation errors', [
                    'errors' => $errors,
                    'uploaded_by' => Auth::id(),
                    'selected_rank' => $selectedRank,
                    'total_errors' => count($errors)
                ]);
                
                $errorMessage = count($errors) . ' error(s) found in CSV. Please fix them and try again.';
                return back()
                    ->with('error', $errorMessage)
                    ->withErrors(['csv_errors' => $errors])
                    ->withInput();
            }

            // Check if we have any valid results to process
            if (empty($results)) {
                DB::rollBack();
                $errorMessage = 'No valid training results to process. All rows had validation errors.';
                if (!empty($errors)) {
                    $errorMessage = count($errors) . ' error(s) found in CSV. Please fix them and try again.';
                    return back()
                        ->with('error', $errorMessage)
                        ->withErrors(['csv_errors' => $errors])
                        ->withInput();
                }
                return back()->with('error', $errorMessage)->withInput();
            }

            // Sort by performance (highest to lowest score, then by appointment number)
            // Note: rank field already contains officer's substantive_rank
            usort($results, function ($a, $b) {
                if ($b['training_score'] == $a['training_score']) {
                    return strcmp($a['appointment_number'], $b['appointment_number']);
                }
                return $b['training_score'] <=> $a['training_score'];
            });

            // Insert all results
            TrainingResult::insert($results);

            DB::commit();

            // Notify Establishment about training results upload
            $notificationService = app(NotificationService::class);
            $establishmentUsers = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'Establishment')->where('is_active', true);
            })->where('is_active', true)->get();
            
            foreach ($establishmentUsers as $user) {
                $notificationService->notifyTrainingResultsUploaded($user, count($results));
            }

            Log::info('TRADOC CSV upload successful', [
                'count' => count($results),
                'uploaded_by' => Auth::id()
            ]);

            return redirect()->route('tradoc.dashboard')
                ->with('success', count($results) . ' training results uploaded and sorted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('TRADOC CSV upload validation exception', [
                'errors' => $e->errors(),
                'uploaded_by' => Auth::id()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TRADOC CSV upload error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'uploaded_by' => Auth::id()
            ]);
            return back()->with('error', 'Failed to process CSV file: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show sorted results ready for Establishment
     * DEPRECATED: Merged into dashboard. Kept for backward compatibility.
     */
    public function sortedResults()
    {
        return redirect()->route('tradoc.dashboard');
    }

    /**
     * Show details of a training result
     */
    public function show($id)
    {
        $result = TrainingResult::with(['officer', 'uploadedBy'])->findOrFail($id);
        return view('dashboards.tradoc.show', compact('result'));
    }

    /**
     * Delete a training result
     */
    public function destroy($id)
    {
        try {
            $result = TrainingResult::findOrFail($id);
            
            // Check if service number has been assigned - warn if yes
            if ($result->service_number) {
                return back()->with('error', 'Cannot delete training result. Service number has already been assigned to this officer.');
            }

            $appointmentNumber = $result->appointment_number;
            $result->delete();

            Log::info('Training result deleted', [
                'id' => $id,
                'appointment_number' => $appointmentNumber,
                'deleted_by' => Auth::id()
            ]);

            return back()->with('success', 'Training result deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Training result deletion error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'deleted_by' => Auth::id()
            ]);
            return back()->with('error', 'Failed to delete training result: ' . $e->getMessage());
        }
    }

    /**
     * Download new recruits template CSV for training results entry
     */
    public function downloadNewRecruitsTemplate(Request $request)
    {
        // Get new recruits (with appointment numbers but no service numbers)
        $query = Officer::whereNotNull('appointment_number')
            ->whereNull('service_number')
            ->where('is_active', true)
            ->where('is_deceased', false);

        // Filter by rank if provided
        $selectedRank = $request->input('rank');
        if ($selectedRank) {
            $query->where('substantive_rank', $selectedRank);
        }

        $newRecruits = $query->orderBy('appointment_number')->get();

        if ($newRecruits->isEmpty()) {
            $message = $selectedRank 
                ? "No new recruits available for rank '{$selectedRank}'. All recruits may already have service numbers assigned."
                : 'No new recruits available for download. All recruits may already have service numbers assigned.';
            return back()->with('error', $message);
        }

        // Include rank in filename if filtered
        $rankSuffix = $selectedRank ? '_' . str_replace(' ', '_', $selectedRank) : '';
        $filename = 'new_recruits_training_template' . $rankSuffix . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($newRecruits) {
            $file = fopen('php://output', 'w');
            
            // Write headers (Status column removed - auto-determined from score)
            fputcsv($file, ['Appointment Number', 'Officer Name', 'Training Score']);
            
            // Write data with score pre-filled as 0
            foreach ($newRecruits as $recruit) {
                $officerName = trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
                fputcsv($file, [
                    $recruit->appointment_number,
                    $officerName,
                    '0', // Pre-filled score (Status will be auto-determined: >= 50 = PASS, < 50 = FAIL)
                ]);
            }
            
            fclose($file);
        };

        Log::info('TRADOC downloaded new recruits template', [
            'count' => $newRecruits->count(),
            'downloaded_by' => Auth::id()
        ]);

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sorted results to CSV for Establishment
     * Simplified: Results sorted by performance (highest to lowest), filterable by rank
     */
    public function exportSortedResults(Request $request)
    {
        $query = TrainingResult::sortedByPerformance()
            ->whereNull('service_number');

        // Filter by rank if provided - check both stored rank and officer's substantive_rank
        $selectedRank = $request->input('rank');
        if ($selectedRank) {
            $query->where(function($q) use ($selectedRank) {
                $q->where('rank', $selectedRank)
                  ->orWhereHas('officer', function($officerQuery) use ($selectedRank) {
                      $officerQuery->where('substantive_rank', $selectedRank);
                  });
            });
        }

        $results = $query->get();

        // Include rank in filename if filtered
        $rankSuffix = $selectedRank ? '_' . str_replace(' ', '_', $selectedRank) : '';
        $filename = 'training_results' . $rankSuffix . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            
            // Write headers (Status removed - not needed in export)
            fputcsv($file, ['Rank', 'Appointment Number', 'Officer Name', 'Training Score']);
            
            // Write data
            foreach ($results as $result) {
                // Use officer's substantive_rank if available, otherwise stored rank
                $rank = $result->officer->substantive_rank ?? $result->rank ?? 'N/A';
                
                fputcsv($file, [
                    $rank,
                    $result->appointment_number,
                    $result->officer_name,
                    $result->training_score,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
