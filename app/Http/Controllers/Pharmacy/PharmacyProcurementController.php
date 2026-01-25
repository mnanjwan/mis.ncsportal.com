<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyDrug;
use App\Models\PharmacyProcurement;
use App\Models\PharmacyProcurementItem;
use App\Services\Pharmacy\PharmacyWorkflowService;
use Illuminate\Http\Request;

class PharmacyProcurementController extends Controller
{
    protected PharmacyWorkflowService $workflowService;

    public function __construct(PharmacyWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of procurements.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');

        $query = PharmacyProcurement::with(['items.drug', 'createdBy', 'steps']);

        // For Controller Procurement, show their own procurements
        if ($user->hasRole('Controller Procurement')) {
            $query->where('created_by', $user->id);
        }

        // For OC Pharmacy, show procurements pending their action
        if ($user->hasRole('OC Pharmacy')) {
            $query->orWhereHas('steps', function ($q) {
                $q->where('role_name', 'OC Pharmacy');
            });
        }

        // For Central Medical Store, show approved procurements
        if ($user->hasRole('Central Medical Store')) {
            $query->orWhereIn('status', ['APPROVED', 'RECEIVED']);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $procurements = $query->latest()->paginate(20);

        return view('pharmacy.procurements.index', compact('procurements', 'status'));
    }

    /**
     * Show the form for creating a new procurement.
     */
    public function create()
    {
        return view('pharmacy.procurements.create');
    }

    /**
     * Store a newly created procurement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
        ]);

        $procurement = $this->workflowService->createProcurement($request->user(), [
            'notes' => $request->input('notes'),
        ]);

        // Add items with drug name as text input
        foreach ($request->input('items') as $item) {
            PharmacyProcurementItem::create([
                'pharmacy_procurement_id' => $procurement->id,
                'drug_name' => $item['drug_name'],
                'unit_of_measure' => $item['unit'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        return redirect()
            ->route('pharmacy.procurements.show', $procurement->id)
            ->with('success', 'Procurement draft created successfully.');
    }

    /**
     * Display the specified procurement.
     */
    public function show($id)
    {
        $procurement = PharmacyProcurement::with([
            'items.drug',
            'createdBy.officer',
            'steps.actedBy.officer',
        ])->findOrFail($id);

        return view('pharmacy.procurements.show', compact('procurement'));
    }

    /**
     * Show the form for editing the procurement.
     */
    public function edit($id)
    {
        $procurement = PharmacyProcurement::with(['items'])->findOrFail($id);

        if (!$procurement->isDraft()) {
            return redirect()
                ->route('pharmacy.procurements.show', $id)
                ->with('error', 'Only draft procurements can be edited.');
        }

        return view('pharmacy.procurements.edit', compact('procurement'));
    }

    /**
     * Update the specified procurement.
     */
    public function update(Request $request, $id)
    {
        $procurement = PharmacyProcurement::findOrFail($id);

        if (!$procurement->isDraft()) {
            return redirect()
                ->route('pharmacy.procurements.show', $id)
                ->with('error', 'Only draft procurements can be updated.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
        ]);

        $procurement->update([
            'notes' => $request->input('notes'),
        ]);

        // Update items
        $procurement->items()->delete();
        foreach ($request->input('items') as $item) {
            PharmacyProcurementItem::create([
                'pharmacy_procurement_id' => $procurement->id,
                'drug_name' => $item['drug_name'],
                'unit_of_measure' => $item['unit'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        return redirect()
            ->route('pharmacy.procurements.show', $procurement->id)
            ->with('success', 'Procurement updated successfully.');
    }

    /**
     * Submit the procurement for approval.
     */
    public function submit(Request $request, $id)
    {
        $procurement = PharmacyProcurement::findOrFail($id);

        $procurement = $this->workflowService->submitProcurement($procurement, $request->user());

        return redirect()
            ->route('pharmacy.procurements.show', $procurement->id)
            ->with('success', 'Procurement submitted for approval.');
    }

    /**
     * Act on the procurement (approve/reject).
     */
    public function act(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:APPROVED,REJECTED',
            'comment' => 'nullable|string|max:1000',
        ]);

        $procurement = PharmacyProcurement::findOrFail($id);

        $procurement = $this->workflowService->actOnProcurement(
            $procurement,
            $request->user(),
            $request->input('decision'),
            $request->input('comment')
        );

        $action = $request->input('decision') === 'APPROVED' ? 'approved' : 'rejected';

        return redirect()
            ->route('pharmacy.procurements.show', $procurement->id)
            ->with('success', "Procurement {$action} successfully.");
    }

    /**
     * Receive the procurement items.
     */
    public function receive(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:pharmacy_procurement_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.batch_number' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $procurement = PharmacyProcurement::findOrFail($id);

        $procurement = $this->workflowService->receiveProcurement(
            $procurement,
            $request->user(),
            $request->input('items'),
            $request->input('comment')
        );

        return redirect()
            ->route('pharmacy.procurements.show', $procurement->id)
            ->with('success', 'Procurement items received and stock updated.');
    }
}
