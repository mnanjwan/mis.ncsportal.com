<?php

namespace App\Http\Controllers;

use App\Models\NextOfKinChangeRequest;
use App\Models\NextOfKin;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

class NextOfKinChangeRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of officer's next of kin change requests
     */
    public function index()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $requests = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->with('nextOfKin')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $nextOfKins = NextOfKin::where('officer_id', $officer->id)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('dashboards.officer.next-of-kin-requests', compact('requests', 'nextOfKins'));
    }

    /**
     * Show form to add new next of kin
     */
    public function create()
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        return view('forms.next-of-kin.create');
    }

    /**
     * Store add next of kin request
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
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        // Check for pending request
        $pendingRequest = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->where('status', 'PENDING')
            ->where('action_type', 'add')
            ->where('name', $validated['name'])
            ->first();

        if ($pendingRequest) {
            return back()->withErrors(['error' => 'You have a pending add request for this Next of KIN. Please wait for it to be processed.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $changeRequest = NextOfKinChangeRequest::create([
                'officer_id' => $officer->id,
                'action_type' => 'add',
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
                'status' => 'PENDING',
            ]);

            DB::commit();

            // Notify Welfare role users about the new request
            $notificationService = app(NotificationService::class);
            $notificationService->notifyNextOfKinChangeRequestSubmitted($changeRequest);

            return redirect()->route('officer.next-of-kin.index')
                ->with('success', 'Next of KIN add request submitted successfully. It will be reviewed by the Welfare Section.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to submit request. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show form to edit next of kin
     */
    public function edit($id)
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $nextOfKin = NextOfKin::where('id', $id)
            ->where('officer_id', $officer->id)
            ->firstOrFail();

        return view('forms.next-of-kin.edit', compact('nextOfKin'));
    }

    /**
     * Store edit next of kin request
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $nextOfKin = NextOfKin::where('id', $id)
            ->where('officer_id', $officer->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        // Check for pending request for this next of kin
        $pendingRequest = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->where('next_of_kin_id', $nextOfKin->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingRequest) {
            return back()->withErrors(['error' => 'You have a pending change request for this Next of KIN. Please wait for it to be processed.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $changeRequest = NextOfKinChangeRequest::create([
                'officer_id' => $officer->id,
                'action_type' => 'edit',
                'next_of_kin_id' => $nextOfKin->id,
                'name' => $validated['name'],
                'relationship' => $validated['relationship'],
                'phone_number' => $validated['phone_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
                'status' => 'PENDING',
            ]);

            DB::commit();

            // Notify Welfare role users about the new request
            $notificationService = app(NotificationService::class);
            $notificationService->notifyNextOfKinChangeRequestSubmitted($changeRequest);

            return redirect()->route('officer.next-of-kin.index')
                ->with('success', 'Next of KIN edit request submitted successfully. It will be reviewed by the Welfare Section.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to submit request. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Store delete next of kin request
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $officer = $user->officer;

        if (!$officer) {
            return redirect()->route('officer.dashboard')
                ->with('error', 'Officer record not found.');
        }

        $nextOfKin = NextOfKin::where('id', $id)
            ->where('officer_id', $officer->id)
            ->firstOrFail();

        // Check for pending request for this next of kin
        $pendingRequest = NextOfKinChangeRequest::where('officer_id', $officer->id)
            ->where('next_of_kin_id', $nextOfKin->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingRequest) {
            return redirect()->route('officer.next-of-kin.index')
                ->with('error', 'You have a pending change request for this Next of KIN. Please wait for it to be processed.');
        }

        DB::beginTransaction();
        try {
            $changeRequest = NextOfKinChangeRequest::create([
                'officer_id' => $officer->id,
                'action_type' => 'delete',
                'next_of_kin_id' => $nextOfKin->id,
                'name' => $nextOfKin->name,
                'relationship' => $nextOfKin->relationship,
                'phone_number' => $nextOfKin->phone_number,
                'address' => $nextOfKin->address,
                'email' => $nextOfKin->email,
                'status' => 'PENDING',
            ]);

            DB::commit();

            // Notify Welfare role users about the new request
            $notificationService = app(NotificationService::class);
            $notificationService->notifyNextOfKinChangeRequestSubmitted($changeRequest);

            return redirect()->route('officer.next-of-kin.index')
                ->with('success', 'Next of KIN delete request submitted successfully. It will be reviewed by the Welfare Section.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('officer.next-of-kin.index')
                ->with('error', 'Failed to submit request. Please try again.');
        }
    }

    /**
     * Display list of pending change requests for Welfare role
     */
    public function pending()
    {
        $requests = NextOfKinChangeRequest::with(['officer.presentStation', 'nextOfKin', 'verifier'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $pendingCount = NextOfKinChangeRequest::where('status', 'PENDING')->count();
        $approvedCount = NextOfKinChangeRequest::where('status', 'APPROVED')->count();
        $rejectedCount = NextOfKinChangeRequest::where('status', 'REJECTED')->count();

        return view('dashboards.welfare.next-of-kin-change-requests', compact(
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
        $request = NextOfKinChangeRequest::with(['officer.presentStation', 'nextOfKin', 'verifier'])->findOrFail($id);

        // Check authorization
        if ($user->hasRole('Officer')) {
            if ($request->officer_id !== $user->officer->id) {
                abort(403, 'Unauthorized access.');
            }
        } elseif (!$user->hasRole('Welfare')) {
            abort(403, 'Unauthorized access.');
        }

        return view('dashboards.welfare.next-of-kin-change-request-show', compact('request'));
    }

    /**
     * Approve a change request (Welfare role)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Welfare')) {
            abort(403, 'Unauthorized access.');
        }

        $changeRequest = NextOfKinChangeRequest::with(['officer', 'nextOfKin'])->findOrFail($id);

        if ($changeRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            switch ($changeRequest->action_type) {
                case 'add':
                    NextOfKin::create([
                        'officer_id' => $changeRequest->officer_id,
                        'name' => $changeRequest->name,
                        'relationship' => $changeRequest->relationship,
                        'phone_number' => $changeRequest->phone_number,
                        'address' => $changeRequest->address,
                        'is_primary' => $changeRequest->is_primary,
                    ]);
                    break;

                case 'edit':
                    if ($changeRequest->nextOfKin) {
                        $changeRequest->nextOfKin->update([
                            'name' => $changeRequest->name,
                            'relationship' => $changeRequest->relationship,
                            'phone_number' => $changeRequest->phone_number,
                            'address' => $changeRequest->address,
                            'email' => $changeRequest->email,
                            'is_primary' => $changeRequest->is_primary,
                        ]);
                    }
                    break;

                case 'delete':
                    if ($changeRequest->nextOfKin) {
                        $changeRequest->nextOfKin->delete();
                    }
                    break;
            }

            // Update request status
            $changeRequest->status = 'APPROVED';
            $changeRequest->verified_by = $user->id;
            $changeRequest->verified_at = now();
            $changeRequest->save();

            DB::commit();

            return redirect()->route('welfare.next-of-kin.pending')
                ->with('success', 'Next of KIN change request approved and records updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve request. Please try again.');
        }
    }

    /**
     * Reject a change request (Welfare role)
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Welfare')) {
            abort(403, 'Unauthorized access.');
        }

        $changeRequest = NextOfKinChangeRequest::findOrFail($id);

        if ($changeRequest->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $changeRequest->status = 'REJECTED';
            $changeRequest->verified_by = $user->id;
            $changeRequest->verified_at = now();
            $changeRequest->rejection_reason = $validated['rejection_reason'];
            $changeRequest->save();

            DB::commit();

            return redirect()->route('welfare.next-of-kin.pending')
                ->with('success', 'Next of KIN change request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject request. Please try again.');
        }
    }
}
