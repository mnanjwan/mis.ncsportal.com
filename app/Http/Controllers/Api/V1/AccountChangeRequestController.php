<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AccountChangeRequest;
use App\Models\Bank;
use App\Models\Officer;
use App\Models\Pfa;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountChangeRequestController extends BaseController
{
    /**
     * List current officer's account change requests
     */
    public function index(Request $request): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $requests = AccountChangeRequest::where('officer_id', $officer->id)
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
     * Get options for account change form (banks, PFAs)
     */
    public function options(Request $request): JsonResponse
    {
        $banks = Bank::where('is_active', true)->orderBy('name')->get(['name', 'account_number_digits']);
        $pfas = Pfa::where('is_active', true)->orderBy('name')->get(['name', 'rsa_prefix', 'rsa_digits']);

        return $this->successResponse([
            'banks' => $banks,
            'pfas' => $pfas,
        ]);
    }

    /**
     * Submit account change request (same validation as web)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $changeAccount = $request->filled('new_account_number');
        $changeRsaPin = $request->filled('new_rsa_pin');
        $changeBankName = $request->filled('new_bank_name');
        $changeSortCode = $request->filled('new_sort_code');
        $changePfaName = $request->filled('new_pfa_name');
        $hasAnyChange = $changeAccount || $changeRsaPin || $changeBankName || $changeSortCode || $changePfaName;

        if (!$hasAnyChange) {
            return $this->errorResponse(
                'Provide at least one change (new_account_number, new_rsa_pin, new_bank_name, new_sort_code, or new_pfa_name).',
                null,
                422,
                'VALIDATION_ERROR'
            );
        }

        $changeType = ($changeAccount && $changeRsaPin) ? 'both' : ($changeAccount ? 'account_number' : 'rsa_pin');

        $rules = [
            'reason' => 'nullable|string|max:500',
        ];
        if ($changeBankName) {
            $rules['new_bank_name'] = ['required', 'string', 'max:255', Rule::exists('banks', 'name')->where('is_active', true)];
        }
        if ($changePfaName) {
            $rules['new_pfa_name'] = ['required', 'string', 'max:255', Rule::exists('pfas', 'name')->where('is_active', true)];
        }
        if ($changeSortCode) {
            $rules['new_sort_code'] = 'nullable|string|max:50';
        }
        if ($changeAccount) {
            $rules['new_account_number'] = 'required|string|max:50';
        }
        if ($changeRsaPin) {
            $rules['new_rsa_pin'] = 'required|string|max:50';
        }

        $request->validate($rules);

        $pending = AccountChangeRequest::where('officer_id', $officer->id)->where('status', 'PENDING')->first();
        if ($pending) {
            return $this->errorResponse('You have a pending change request. Wait for it to be processed.', null, 422, 'PENDING_EXISTS');
        }

        DB::beginTransaction();
        try {
            $accountChangeRequest = AccountChangeRequest::create([
                'officer_id' => $officer->id,
                'change_type' => $changeType,
                'new_account_number' => $request->new_account_number ?? null,
                'new_rsa_pin' => $request->new_rsa_pin ?? null,
                'new_bank_name' => $request->new_bank_name ?? null,
                'new_sort_code' => $request->new_sort_code ?? null,
                'new_pfa_name' => $request->new_pfa_name ?? null,
                'current_account_number' => $officer->bank_account_number,
                'current_rsa_pin' => $officer->rsa_number,
                'current_bank_name' => $officer->bank_name,
                'current_sort_code' => $officer->sort_code,
                'current_pfa_name' => $officer->pfa_name,
                'status' => 'PENDING',
                'reason' => $request->reason,
            ]);

            app(NotificationService::class)->notifyByRole(
                'Accounts',
                'account_change_request_submitted',
                'New Account Change Request',
                "Officer {$officer->initials} {$officer->surname} ({$officer->service_number}) has submitted an account change request.",
                'account_change_request',
                $accountChangeRequest->id
            );

            DB::commit();

            return $this->successResponse([
                'id' => $accountChangeRequest->id,
                'status' => $accountChangeRequest->status,
            ], 'Account change request submitted successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return $this->errorResponse('Failed to submit request.', null, 500);
        }
    }

    /**
     * Show single account change request
     */
    public function show(Request $request, $id): JsonResponse
    {
        $officer = $request->user()->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $req = AccountChangeRequest::where('officer_id', $officer->id)->findOrFail($id);

        return $this->successResponse($req);
    }
}
