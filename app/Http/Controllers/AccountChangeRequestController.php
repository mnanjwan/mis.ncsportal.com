<?php

namespace App\Http\Controllers;

use App\Models\AccountChangeRequest;
use App\Models\Bank;
use App\Models\Officer;
use App\Models\Pfa;
use App\Rules\RsaPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\NotificationService;

class AccountChangeRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display form for officer to submit account/RSA PIN change request
     */
    public function create()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        // Get current values
        $currentAccountNumber = $officer->bank_account_number;
        $currentRsaPin = $officer->rsa_number;
        $currentBankName = $officer->bank_name;
        $currentSortCode = $officer->sort_code;
        $currentPfaName = $officer->pfa_name;

        $banks = Bank::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'account_number_digits']);

        $pfas = Pfa::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['name', 'rsa_prefix', 'rsa_digits']);

        return view('forms.account-change.create', compact(
            'officer',
            'currentAccountNumber',
            'currentRsaPin',
            'currentBankName',
            'currentSortCode',
            'currentPfaName',
            'banks',
            'pfas'
        ));
    }

    /**
     * Store account/RSA PIN change request from officer
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        // Determine change type - check if any bank account field is being changed
        $changeAccount = $request->filled('new_account_number');
        $changeRsaPin = $request->filled('new_rsa_pin');
        $changeBankName = $request->filled('new_bank_name');
        $changeSortCode = $request->filled('new_sort_code');
        $changePfaName = $request->filled('new_pfa_name');
        
        $hasAnyChange = $changeAccount || $changeRsaPin || $changeBankName || $changeSortCode || $changePfaName;
        
        if (!$hasAnyChange) {
            return back()->withErrors(['error' => 'Please provide at least one change (Account Number, RSA PIN, Bank Name, Sort Code, or PFA Name).'])
                ->withInput();
        }

        // Determine change type for backward compatibility
        $changeType = 'both';
        if ($changeAccount && $changeRsaPin) {
            $changeType = 'both';
        } elseif ($changeAccount) {
            $changeType = 'account_number';
        } elseif ($changeRsaPin) {
            $changeType = 'rsa_pin';
        }

        // Validation rules
        $rules = [];
        $messages = [];

        if ($changeAccount) {
            $rules['new_account_number'] = [
                'required',
                'string',
                'max:50',
                'different:current_account_number',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $officer) {
                    $effectiveBankName = $request->input('new_bank_name') ?: $officer->bank_name;
                    if (!$effectiveBankName) {
                        $fail('Please select a bank name first.');
                        return;
                    }

                    $bank = Bank::query()->where('name', $effectiveBankName)->first();
                    if (!$bank) {
                        $fail('Selected bank is not configured in the system.');
                        return;
                    }

                    $digits = max(1, (int) $bank->account_number_digits);
                    if (!preg_match('/^\d{' . $digits . '}$/', (string) $value)) {
                        $fail("Account Number must be exactly {$digits} digits for {$effectiveBankName}.");
                    }
                },
            ];
        }

        if ($changeRsaPin) {
            $rules['new_rsa_pin'] = [
                'required',
                'string',
                'max:50',
                'different:current_rsa_pin',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $officer) {
                    $effectivePfaName = $request->input('new_pfa_name') ?: $officer->pfa_name;
                    if (!$effectivePfaName) {
                        $fail('Please select a PFA name first.');
                        return;
                    }

                    $pfa = Pfa::query()->where('name', $effectivePfaName)->first();
                    if (!$pfa) {
                        $fail('Selected PFA is not configured in the system.');
                        return;
                    }

                    $prefix = strtoupper((string) $pfa->rsa_prefix);
                    $digits = max(1, (int) $pfa->rsa_digits);
                    $pattern = '/^' . preg_quote($prefix, '/') . '\d{' . $digits . '}$/';
                    if (!preg_match($pattern, (string) $value)) {
                        $example = $prefix . str_repeat('0', $digits);
                        $fail("RSA PIN must be {$prefix} followed by {$digits} digits (e.g., {$example}).");
                    }
                },
            ];
        }

        if ($changeBankName) {
            $rules['new_bank_name'] = [
                'required',
                'string',
                'max:255',
                'different:current_bank_name',
                Rule::exists('banks', 'name')->where('is_active', true),
            ];
        }

        if ($changeSortCode) {
            $rules['new_sort_code'] = 'required|string|max:50|different:current_sort_code';
        }

        if ($changePfaName) {
            $rules['new_pfa_name'] = [
                'required',
                'string',
                'max:255',
                'different:current_pfa_name',
                Rule::exists('pfas', 'name')->where('is_active', true),
            ];
        }

        $rules['reason'] = 'nullable|string|max:500';

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for pending request
        $pendingRequest = AccountChangeRequest::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingRequest) {
            return back()->withErrors(['error' => 'You have a pending change request. Please wait for it to be processed.'])
                ->withInput();
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

            // Notify Accounts role users about the new request
            $notificationService = app(NotificationService::class);
            $notificationService->notifyByRole(
                'Accounts',
                'account_change_request_submitted',
                'New Account Change Request',
                "Officer {$officer->initials} {$officer->surname} ({$officer->service_number}) has submitted an account change request.",
                'account_change_request',
                $accountChangeRequest->id
            );

            DB::commit();

            return redirect()->route('officer.account-change.index')
                ->with('success', 'Account change request submitted successfully. It will be reviewed by the Accounts Section.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to submit request. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display list of officer's change requests
     */
    public function index()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $requests = AccountChangeRequest::where('officer_id', $officer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboards.officer.account-change-requests', compact('requests'));
    }

    /**
     * Display list of pending change requests for Accounts role
     */
    public function pending()
    {
        $requests = AccountChangeRequest::with(['officer.presentStation', 'verifier'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        $pendingCount = AccountChangeRequest::where('status', 'PENDING')->count();
        $approvedCount = AccountChangeRequest::where('status', 'APPROVED')->count();
        $rejectedCount = AccountChangeRequest::where('status', 'REJECTED')->count();

        return view('dashboards.accounts.account-change-requests', compact(
            'requests',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Show details of a change request
     */
    public function show($id)
    {
        $user = Auth::user();
        $request = AccountChangeRequest::with(['officer.presentStation', 'verifier'])->findOrFail($id);

        // Check authorization
        // Accounts role can view any request
        if ($user->hasRole('Accounts')) {
            // Allow access
        } elseif ($user->hasRole('Officer')) {
            // Officers can only view their own requests
            if (!$user->officer || $request->officer_id !== $user->officer->id) {
                abort(403, 'Unauthorized access.');
            }
        } else {
            // User doesn't have required roles
            abort(403, 'Unauthorized access.');
        }

        return view('dashboards.accounts.account-change-request-show', compact('request'));
    }

    /**
     * Approve a change request (Accounts role)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Accounts')) {
            abort(403, 'Unauthorized access.');
        }

        $accountChangeRequest = AccountChangeRequest::with('officer')->findOrFail($id);

        if ($accountChangeRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            // Update officer's bank account information
            $officer = $accountChangeRequest->officer;
            
            if ($accountChangeRequest->new_account_number) {
                $officer->bank_account_number = $accountChangeRequest->new_account_number;
            }
            
            if ($accountChangeRequest->new_rsa_pin) {
                $officer->rsa_number = $accountChangeRequest->new_rsa_pin;
            }

            if ($accountChangeRequest->new_bank_name) {
                $officer->bank_name = $accountChangeRequest->new_bank_name;
            }

            if ($accountChangeRequest->new_sort_code) {
                $officer->sort_code = $accountChangeRequest->new_sort_code;
            }

            if ($accountChangeRequest->new_pfa_name) {
                $officer->pfa_name = $accountChangeRequest->new_pfa_name;
            }
            
            $officer->save();

            // Update request status
            $accountChangeRequest->status = 'APPROVED';
            $accountChangeRequest->verified_by = $user->id;
            $accountChangeRequest->verified_at = now();
            $accountChangeRequest->save();

            DB::commit();

            // Notify officer about approval
            $officer = $accountChangeRequest->officer;
            if ($officer && $officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notify(
                    $officer->user,
                    'account_change_approved',
                    'Account Change Request Approved',
                    "Your account change request has been approved. Your bank account and RSA PIN have been updated.",
                    'account_change_request',
                    $accountChangeRequest->id
                );
            }

            return redirect()->route('accounts.account-change.pending')
                ->with('success', 'Account change request approved and officer records updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * Reject a change request (Accounts role)
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Accounts')) {
            abort(403, 'Unauthorized access.');
        }

        $accountChangeRequest = AccountChangeRequest::findOrFail($id);

        if ($accountChangeRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $accountChangeRequest->status = 'REJECTED';
            $accountChangeRequest->verified_by = $user->id;
            $accountChangeRequest->verified_at = now();
            $accountChangeRequest->rejection_reason = $validated['rejection_reason'];
            $accountChangeRequest->save();

            DB::commit();

            // Notify officer about rejection
            $officer = $accountChangeRequest->officer;
            if ($officer && $officer->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notify(
                    $officer->user,
                    'account_change_rejected',
                    'Account Change Request Rejected',
                    "Your account change request has been rejected. Reason: {$validated['rejection_reason']}",
                    'account_change_request',
                    $accountChangeRequest->id
                );
            }

            return redirect()->route('accounts.account-change.pending')
                ->with('success', 'Account change request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject request. Please try again.');
        }
    }
}
