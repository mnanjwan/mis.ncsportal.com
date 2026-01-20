<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\EducationChangeRequest;
use App\Models\EducationChangeRequestDocument;
use App\Models\Institution;
use App\Models\Officer;
use App\Models\Qualification;
use App\Models\User;
use App\Services\EducationMasterDataSync;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EducationChangeRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Officer: list my education change requests
     */
    public function index()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $requests = EducationChangeRequest::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboards.officer.education-requests', compact('requests'));
    }

    /**
     * Officer: show request form
     */
    public function create()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $institutions = Institution::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $disciplines = Discipline::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $qualifications = Qualification::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return view('forms.education-request.create', compact('officer', 'institutions', 'qualifications', 'disciplines'));
    }

    /**
     * Officer: submit request (PENDING)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $validated = $request->validate([
            'university' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'year_obtained' => 'required|integer|min:1950|max:' . date('Y'),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Prevent identical pending requests
        $duplicatePending = EducationChangeRequest::query()
            ->where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->where('university', $validated['university'])
            ->where('qualification', $validated['qualification'])
            ->where('year_obtained', (int) $validated['year_obtained'])
            ->first();

        if ($duplicatePending) {
            return back()
                ->withInput()
                ->with('error', 'You already have a pending request for this qualification (same institution and year). Please wait for HRD to review it.');
        }

        DB::beginTransaction();
        try {
            $educationRequest = EducationChangeRequest::create([
                'officer_id' => $officer->id,
                'university' => $validated['university'],
                'qualification' => $validated['qualification'],
                'discipline' => $validated['discipline'] ?? null,
                'year_obtained' => (int) $validated['year_obtained'],
                'status' => 'PENDING',
            ]);

            $files = $request->file('documents', []);
            if (is_array($files) && !empty($files)) {
                foreach ($files as $file) {
                    if (!$file) {
                        continue;
                    }

                    // Store privately on local disk (not publicly accessible)
                    $path = $file->store("education_request_docs/{$educationRequest->id}", 'local');

                    EducationChangeRequestDocument::create([
                        'education_change_request_id' => $educationRequest->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                        'uploaded_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            // Notify HRD users
            $notificationService = app(NotificationService::class);
            $notificationService->notifyByRole(
                'HRD',
                'education_change_request_submitted',
                'New Education Qualification Request',
                "Officer {$officer->initials} {$officer->surname} ({$officer->service_number}) submitted an education qualification for approval.",
                'education_change_request',
                $educationRequest->id
            );

            return redirect()->route('officer.education-requests.index')
                ->with('success', 'Education qualification request submitted successfully. It will be reviewed by HRD.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to submit request. Please try again.');
        }
    }

    /**
     * HRD: list pending requests (with counts)
     */
    public function pending()
    {
        $requests = EducationChangeRequest::with(['officer.presentStation', 'verifier'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $pendingCount = EducationChangeRequest::where('status', 'PENDING')->count();
        $approvedCount = EducationChangeRequest::where('status', 'APPROVED')->count();
        $rejectedCount = EducationChangeRequest::where('status', 'REJECTED')->count();

        return view('dashboards.hrd.education-change-requests', compact(
            'requests',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Show request details (HRD can view all; Officer can view own)
     */
    public function show($id)
    {
        $user = Auth::user();
        $educationRequest = EducationChangeRequest::with(['officer.presentStation', 'verifier', 'documents'])->findOrFail($id);

        if ($this->userHasRole($user, 'HRD')) {
            // ok
        } elseif ($this->userHasRole($user, 'Officer')) {
            if (!$user->officer || $educationRequest->officer_id !== $user->officer->id) {
                abort(403, 'Unauthorized access.');
            }
        } else {
            abort(403, 'Unauthorized access.');
        }

        return view('dashboards.hrd.education-change-request-show', [
            'request' => $educationRequest,
        ]);
    }

    /**
     * HRD: approve request and append into officer educational history
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->userHasRole($user, 'HRD')) {
            abort(403, 'Unauthorized access.');
        }

        $educationRequest = EducationChangeRequest::with('officer.user')->findOrFail($id);

        if ($educationRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $officer = $educationRequest->officer;
            if (!$officer) {
                throw new \RuntimeException('Officer record not found for this request.');
            }

            $education = $this->getEducationArrayForOfficer($officer);
            $newEntry = [
                'university' => $educationRequest->university,
                'qualification' => $educationRequest->qualification,
                'discipline' => $educationRequest->discipline,
                'year_obtained' => $educationRequest->year_obtained,
            ];

            // Prevent duplicate write-back (same institution + qualification + year)
            foreach ($education as $existing) {
                if (!is_array($existing)) {
                    continue;
                }
                $sameUniversity = (string) ($existing['university'] ?? '') === (string) $newEntry['university'];
                $sameQualification = (string) ($existing['qualification'] ?? '') === (string) $newEntry['qualification'];
                $sameYear = (string) ($existing['year_obtained'] ?? '') === (string) $newEntry['year_obtained'];
                if ($sameUniversity && $sameQualification && $sameYear) {
                    DB::rollBack();
                    return back()->with('error', 'This qualification already exists in the officer educational history.');
                }
            }
            $education[] = $newEntry;

            $officer->additional_qualification = json_encode($education);
            $officer->save();

            // Keep master lists updated
            app(EducationMasterDataSync::class)->syncFromEducationArray([$newEntry]);

            $educationRequest->status = 'APPROVED';
            $educationRequest->verified_by = $user->id;
            $educationRequest->verified_at = now();
            $educationRequest->save();

            DB::commit();

            // Notify officer
            if ($officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notify(
                    $officer->user,
                    'education_change_approved',
                    'Education Qualification Approved',
                    'Your education qualification request has been approved and added to your educational history.',
                    'education_change_request',
                    $educationRequest->id
                );
            }

            return redirect()->route('hrd.education-requests.pending')
                ->with('success', 'Education qualification request approved and recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve education change request', [
                'request_id' => $educationRequest->id ?? $id,
                'officer_id' => $educationRequest->officer_id ?? null,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * HRD: reject request
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->userHasRole($user, 'HRD')) {
            abort(403, 'Unauthorized access.');
        }

        $educationRequest = EducationChangeRequest::with('officer.user')->findOrFail($id);

        if ($educationRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $educationRequest->status = 'REJECTED';
            $educationRequest->verified_by = $user->id;
            $educationRequest->verified_at = now();
            $educationRequest->rejection_reason = $validated['rejection_reason'];
            $educationRequest->save();

            DB::commit();

            // Notify officer
            $officer = $educationRequest->officer;
            if ($officer && $officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notify(
                    $officer->user,
                    'education_change_rejected',
                    'Education Qualification Rejected',
                    "Your education qualification request has been rejected. Reason: {$validated['rejection_reason']}",
                    'education_change_request',
                    $educationRequest->id
                );
            }

            return redirect()->route('hrd.education-requests.pending')
                ->with('success', 'Education qualification request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject request. Please try again.');
        }
    }

    /**
     * Download an education request document (HRD or the requesting Officer).
     */
    public function downloadDocument(Request $request, int $requestId, int $documentId)
    {
        $user = $request->user();

        $document = EducationChangeRequestDocument::with('request')->findOrFail($documentId);
        if ((int) $document->education_change_request_id !== (int) $requestId) {
            abort(404);
        }

        $educationRequest = $document->request;
        if (!$educationRequest) {
            abort(404);
        }

        if ($this->userHasRole($user, 'HRD')) {
            // ok
        } elseif ($this->userHasRole($user, 'Officer')) {
            if (!$user->officer || (int) $user->officer->id !== (int) $educationRequest->officer_id) {
                abort(403, 'Unauthorized access.');
            }
        } else {
            abort(403, 'Unauthorized access.');
        }

        $path = ltrim((string) $document->file_path, '/');
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File not found.');
        }

        $absolutePath = Storage::disk('local')->path($path);
        return response()->download(
            $absolutePath,
            $document->file_name,
            $document->mime_type ? ['Content-Type' => $document->mime_type] : []
        );
    }

    private function userHasRole(?User $user, string $roleName): bool
    {
        if (!$user) {
            return false;
        }

        $user->loadMissing(['roles' => function ($query) {
            $query->wherePivot('is_active', true);
        }]);

        return in_array($roleName, $user->roles->pluck('name')->toArray(), true);
    }

    /**
     * Build the officer's existing education array safely.
     * This mirrors the legacy/JSON handling used elsewhere in the app.
     */
    private function getEducationArrayForOfficer(Officer $officer): array
    {
        $education = [];

        $raw = $officer->additional_qualification;
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $education = $decoded;
            }
        }

        if (empty($education) && $officer->entry_qualification) {
            $education[] = [
                'university' => '',
                'qualification' => $officer->entry_qualification,
                'discipline' => $officer->discipline ?? '',
                'year_obtained' => null,
            ];
        }

        return array_values($education);
    }
}

