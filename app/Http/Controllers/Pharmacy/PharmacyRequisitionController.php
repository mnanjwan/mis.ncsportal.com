<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyDrug;
use App\Models\PharmacyRequisition;
use App\Models\PharmacyRequisitionItem;
use App\Models\PharmacyStock;
use App\Services\Pharmacy\PharmacyWorkflowService;
use Illuminate\Http\Request;

class PharmacyRequisitionController extends Controller
{
    protected PharmacyWorkflowService $workflowService;

    public function __construct(PharmacyWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of requisitions.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');

        $query = PharmacyRequisition::with(['items.drug', 'createdBy', 'command', 'steps']);

        // For Command Pharmacist, show their command's requisitions
        if ($user->hasRole('Command Pharmacist')) {
            $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($commandId) {
                $query->where('command_id', $commandId);
            }
        }

        // For OC Pharmacy, show all requisitions
        if ($user->hasRole('OC Pharmacy')) {
            // No filter needed, show all
        }

        // For Central Medical Store, show approved and issued requisitions
        if ($user->hasRole('Central Medical Store')) {
            $query->whereIn('status', ['APPROVED', 'ISSUED']);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $requisitions = $query->latest()->paginate(20);

        return view('pharmacy.requisitions.index', compact('requisitions', 'status'));
    }

    /**
     * Show the form for creating a new requisition.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');

        if (!$commandId) {
            return redirect()
                ->route('pharmacy.requisitions.index')
                ->with('error', 'You must be assigned to a command to create requisitions.');
        }

        $drugs = PharmacyDrug::active()->orderBy('name')->get();
        
        // Get available stock at central store for reference
        $centralStock = PharmacyStock::centralStore()
            ->withStock()
            ->with('drug')
            ->get()
            ->groupBy('pharmacy_drug_id');

        return view('pharmacy.requisitions.create', compact('drugs', 'centralStock'));
    }

    /**
     * Store a newly created requisition.
     */
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pharmacy_drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $requisition = $this->workflowService->createRequisition($request->user(), [
            'notes' => $request->input('notes'),
        ]);

        // Add items
        foreach ($request->input('items') as $item) {
            PharmacyRequisitionItem::create([
                'pharmacy_requisition_id' => $requisition->id,
                'pharmacy_drug_id' => $item['drug_id'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', 'Requisition draft created successfully.');
    }

    /**
     * Display the specified requisition.
     */
    public function show(Request $request, $id)
    {
        $requisition = PharmacyRequisition::with([
            'items.drug',
            'createdBy.officer',
            'command',
            'steps.actedBy.officer',
        ])->findOrFail($id);

        $user = $request->user();

        // Command Pharmacist can only view requisitions from their command
        if ($user->hasRole('Command Pharmacist') && !$user->hasRole('OC Pharmacy') && !$user->hasRole('Central Medical Store')) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($requisition->command_id !== $userCommandId) {
                abort(403, 'You can only view requisitions from your own command.');
            }
        }

        // Get available stock at central store for issuance
        $centralStock = [];
        if ($requisition->status === 'APPROVED') {
            $centralStock = PharmacyStock::centralStore()
                ->withStock()
                ->with('drug')
                ->get()
                ->groupBy('pharmacy_drug_id');
        }

        return view('pharmacy.requisitions.show', compact('requisition', 'centralStock'));
    }

    /**
     * Show the form for editing the requisition.
     */
    public function edit(Request $request, $id)
    {
        $requisition = PharmacyRequisition::with(['items.drug'])->findOrFail($id);
        $user = $request->user();

        // Command Pharmacist can only edit requisitions from their command
        if ($user->hasRole('Command Pharmacist')) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($requisition->command_id !== $userCommandId) {
                abort(403, 'You can only edit requisitions from your own command.');
            }
        }

        if (!$requisition->isDraft()) {
            return redirect()
                ->route('pharmacy.requisitions.show', $id)
                ->with('error', 'Only draft requisitions can be edited.');
        }

        // Only the creator can edit
        if ($requisition->created_by !== $user->id) {
            return redirect()
                ->route('pharmacy.requisitions.show', $id)
                ->with('error', 'Only the creator can edit this requisition.');
        }

        $drugs = PharmacyDrug::active()->orderBy('name')->get();

        $centralStock = PharmacyStock::centralStore()
            ->withStock()
            ->with('drug')
            ->get()
            ->groupBy('pharmacy_drug_id');

        return view('pharmacy.requisitions.edit', compact('requisition', 'drugs', 'centralStock'));
    }

    /**
     * Update the specified requisition.
     */
    public function update(Request $request, $id)
    {
        $requisition = PharmacyRequisition::findOrFail($id);
        $user = $request->user();

        // Command Pharmacist can only update requisitions from their command
        if ($user->hasRole('Command Pharmacist')) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($requisition->command_id !== $userCommandId) {
                abort(403, 'You can only update requisitions from your own command.');
            }
        }

        // Only the creator can update
        if ($requisition->created_by !== $user->id) {
            return redirect()
                ->route('pharmacy.requisitions.show', $id)
                ->with('error', 'Only the creator can update this requisition.');
        }

        if (!$requisition->isDraft()) {
            return redirect()
                ->route('pharmacy.requisitions.show', $id)
                ->with('error', 'Only draft requisitions can be updated.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pharmacy_drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $requisition->update([
            'notes' => $request->input('notes'),
        ]);

        // Update items
        $requisition->items()->delete();
        foreach ($request->input('items') as $item) {
            PharmacyRequisitionItem::create([
                'pharmacy_requisition_id' => $requisition->id,
                'pharmacy_drug_id' => $item['drug_id'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', 'Requisition updated successfully.');
    }

    /**
     * Submit the requisition for approval.
     */
    public function submit(Request $request, $id)
    {
        $requisition = PharmacyRequisition::findOrFail($id);
        $user = $request->user();

        // Command Pharmacist can only submit requisitions from their command
        if ($user->hasRole('Command Pharmacist')) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($requisition->command_id !== $userCommandId) {
                abort(403, 'You can only submit requisitions from your own command.');
            }
        }

        // Only the creator can submit
        if ($requisition->created_by !== $user->id) {
            return redirect()
                ->route('pharmacy.requisitions.show', $id)
                ->with('error', 'Only the creator can submit this requisition.');
        }

        $requisition = $this->workflowService->submitRequisition($requisition, $user);

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', 'Requisition submitted for approval.');
    }

    /**
     * Act on the requisition (approve/reject).
     */
    public function act(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:APPROVED,REJECTED',
            'comment' => 'nullable|string|max:1000',
        ]);

        $requisition = PharmacyRequisition::findOrFail($id);

        $requisition = $this->workflowService->actOnRequisition(
            $requisition,
            $request->user(),
            $request->input('decision'),
            $request->input('comment')
        );

        $action = $request->input('decision') === 'APPROVED' ? 'approved' : 'rejected';

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', "Requisition {$action} successfully.");
    }

    /**
     * Issue the requisition items from Central Medical Store.
     */
    public function issue(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:pharmacy_requisition_items,id',
            'items.*.quantity_issued' => 'required|integer|min:0',
            'comment' => 'nullable|string|max:1000',
        ]);

        $requisition = PharmacyRequisition::findOrFail($id);

        $requisition = $this->workflowService->issueRequisition(
            $requisition,
            $request->user(),
            $request->input('items'),
            $request->input('comment')
        );

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', 'Requisition items issued to command pharmacy.');
    }

    /**
     * Mark items as dispensed.
     */
    public function dispense(Request $request, $id)
    {
        $user = $request->user();
        $requisition = PharmacyRequisition::findOrFail($id);

        // Command Pharmacist can only dispense requisitions from their command
        if ($user->hasRole('Command Pharmacist')) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            if ($requisition->command_id !== $userCommandId) {
                abort(403, 'You can only dispense requisitions from your own command.');
            }
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:pharmacy_requisition_items,id',
            'items.*.quantity_dispensed' => 'required|integer|min:0',
            'comment' => 'nullable|string|max:1000',
        ]);

        $requisition = $this->workflowService->dispenseFromRequisition(
            $requisition,
            $user,
            $request->input('items'),
            $request->input('comment')
        );

        return redirect()
            ->route('pharmacy.requisitions.show', $requisition->id)
            ->with('success', 'Items dispensed successfully.');
    }
}
