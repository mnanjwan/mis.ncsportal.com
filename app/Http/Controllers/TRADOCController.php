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
     */
    public function index()
    {
        $results = TrainingResult::with(['officer', 'uploadedBy'])
            ->orderBy('uploaded_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => TrainingResult::count(),
            'passed' => TrainingResult::where('status', 'PASS')->count(),
            'failed' => TrainingResult::where('status', 'FAIL')->count(),
            'pending_service_number' => TrainingResult::whereNull('service_number')->count(),
        ];

        return view('dashboards.tradoc.index', compact('results', 'stats'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return view('forms.tradoc.upload');
    }

    /**
     * Handle CSV upload and process training results
     */
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($data); // Remove header row

            // Expected CSV format: Appointment Number, Officer Name, Training Score, Status
            $results = [];
            $errors = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed

                if (count($row) < 3) {
                    $errors[] = "Row {$rowNumber}: Insufficient columns. Expected: Appointment Number, Officer Name, Training Score, Status (optional)";
                    continue;
                }

                $appointmentNumber = trim($row[0]);
                $officerName = trim($row[1]);
                $trainingScore = trim($row[2]);
                $status = isset($row[3]) ? strtoupper(trim($row[3])) : 'PASS';

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

                if (!in_array($status, ['PASS', 'FAIL'])) {
                    $status = $trainingScore >= 50 ? 'PASS' : 'FAIL';
                }

                // Check if appointment number exists in new recruits list (officers without service number)
                $newRecruit = Officer::where('appointment_number', $appointmentNumber)
                    ->whereNull('service_number')
                    ->where('is_active', true)
                    ->where('is_deceased', false)
                    ->first();

                if (!$newRecruit) {
                    // Check if officer exists at all (might have service number already)
                    $existingOfficer = Officer::where('appointment_number', $appointmentNumber)->first();
                    if ($existingOfficer) {
                        if ($existingOfficer->service_number) {
                            $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' already has a service number assigned ({$existingOfficer->service_number}). Only new recruits without service numbers can have training results uploaded.";
                        } else {
                            $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' is not in the active new recruits list.";
                        }
                    } else {
                        $errors[] = "Row {$rowNumber}: Appointment Number '{$appointmentNumber}' not found in the new recruits list. Please ensure the recruit has been added and assigned an appointment number first.";
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
                return back()->with('error', 'No valid training results to process. All rows had validation errors.')->withInput();
            }

            // Sort by performance (highest to lowest score, then by appointment number)
            usort($results, function ($a, $b) {
                if ($b['training_score'] == $a['training_score']) {
                    return strcmp($a['appointment_number'], $b['appointment_number']);
                }
                return $b['training_score'] <=> $a['training_score'];
            });

            // Assign ranks
            foreach ($results as $index => &$result) {
                $result['rank'] = $index + 1;
            }

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
     */
    public function sortedResults()
    {
        $results = TrainingResult::sortedByPerformance()
            ->whereNull('service_number')
            ->get();

        return view('dashboards.tradoc.sorted-results', compact('results'));
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
    public function downloadNewRecruitsTemplate()
    {
        // Get new recruits (with appointment numbers but no service numbers)
        $newRecruits = Officer::whereNotNull('appointment_number')
            ->whereNull('service_number')
            ->where('is_active', true)
            ->where('is_deceased', false)
            ->orderBy('appointment_number')
            ->get();

        if ($newRecruits->isEmpty()) {
            return back()->with('error', 'No new recruits available for download. All recruits may already have service numbers assigned.');
        }

        $filename = 'new_recruits_training_template_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($newRecruits) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, ['Appointment Number', 'Officer Name', 'Training Score', 'Status']);
            
            // Write data with score pre-filled as 0
            foreach ($newRecruits as $recruit) {
                $officerName = trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? ''));
                fputcsv($file, [
                    $recruit->appointment_number,
                    $officerName,
                    '0', // Pre-filled score
                    'PASS', // Default status
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
     */
    public function exportSortedResults()
    {
        $results = TrainingResult::sortedByPerformance()
            ->whereNull('service_number')
            ->get();

        $filename = 'training_results_sorted_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, ['Rank', 'Appointment Number', 'Officer Name', 'Training Score', 'Status']);
            
            // Write data
            foreach ($results as $result) {
                fputcsv($file, [
                    $result->rank,
                    $result->appointment_number,
                    $result->officer_name,
                    $result->training_score,
                    $result->status,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
