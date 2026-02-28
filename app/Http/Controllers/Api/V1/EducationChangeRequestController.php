<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Discipline;
use App\Models\EducationChangeRequest;
use App\Models\EducationChangeRequestDocument;
use App\Models\Institution;
use App\Models\Officer;
use App\Models\Qualification;
use App\Services\EducationMasterDataSync;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EducationChangeRequestController extends BaseController
{
    /**
     * List current officer's education change requests
     */
    public function index(Request $request): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $requests = EducationChangeRequest::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginatedResponse(
            $requests->items(),
            [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage(),
            ]
        );
    }

    /**
     * Get options for education form (institutions, disciplines, qualifications)
     */
    public function options(Request $request): JsonResponse
    {
        $institutions = Institution::query()->active()->orderBy('name')->pluck('name')->values();
        $disciplines = Discipline::query()->active()->orderBy('name')->pluck('name')->values();
        $qualifications = Qualification::query()->active()->orderBy('name')->pluck('name')->values();

        return $this->successResponse([
            'institutions' => $institutions,
            'disciplines' => $disciplines,
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Submit education change request
     */
    public function store(Request $request): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $validated = $request->validate([
            'university' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'year_obtained' => 'required|integer|min:1950|max:' . date('Y'),
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $duplicatePending = EducationChangeRequest::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->where('university', $validated['university'])
            ->where('qualification', $validated['qualification'])
            ->where('year_obtained', (int) $validated['year_obtained'])
            ->first();

        if ($duplicatePending) {
            return $this->errorResponse(
                'You already have a pending request for this qualification. Please wait for HRD to review it.',
                null,
                422,
                'PENDING_EXISTS'
            );
        }

        DB::beginTransaction();
        try {
            app(EducationMasterDataSync::class)->syncFromEducationArray([
                [
                    'university' => $validated['university'],
                    'qualification' => $validated['qualification'],
                    'discipline' => $validated['discipline'] ?? null,
                    'year_obtained' => $validated['year_obtained'],
                ],
            ]);

            $educationChangeRequest = EducationChangeRequest::create([
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
                    $path = $file->store("education_request_docs/{$educationChangeRequest->id}", 'local');
                    EducationChangeRequestDocument::create([
                        'education_change_request_id' => $educationChangeRequest->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getClientMimeType(),
                        'uploaded_by' => $request->user()->id,
                    ]);
                }
            }

            app(NotificationService::class)->notifyByRole(
                'HRD',
                'education_change_request_submitted',
                'New Education Qualification Request',
                "Officer {$officer->initials} {$officer->surname} ({$officer->service_number}) has submitted an education change request.",
                'education_change_request',
                $educationChangeRequest->id
            );

            DB::commit();

            return $this->successResponse([
                'id' => $educationChangeRequest->id,
                'status' => $educationChangeRequest->status,
            ], 'Education change request submitted successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse('Failed to submit request: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Show single education change request
     */
    public function show(Request $request, $id): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $req = EducationChangeRequest::where('officer_id', $officer->id)->with('documents')->findOrFail($id);

        return $this->successResponse($req);
    }
}
