<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyDrug;
use App\Models\PharmacyReturn;
use App\Models\PharmacyReturnItem;
use App\Models\PharmacyStock;
use App\Services\Pharmacy\PharmacyWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PharmacyReturnController extends Controller
{
    protected PharmacyWorkflowService $workflowService;

    public function __construct(PharmacyWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = PharmacyReturn::with(['command', 'createdBy.officer', 'items.drug']);

        // Role-based filtering
        if ($user->hasRole('Command Pharmacist')) {
            $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
            $query->where('command_id', $commandId);
        } elseif ($user->hasRole('Controller Pharmacy') || $user->hasRole('Central Medical Store') || $user->hasRole('Controller Procurement')) {
            // Can see all
        } else {
            abort(403);
        }

        $returns = $query->latest()->paginate(20);

        return view('pharmacy.returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
        
        if (!$commandId) {
            return back()->with('error', 'You must be assigned to a command to initiate a return.');
        }

        // Get available drugs for this command
        $availableStocks = PharmacyStock::where('command_id', $commandId)
            ->where('location_type', 'COMMAND_PHARMACY')
            ->where('quantity', '>', 0)
            ->with('drug')
            ->get()
            ->groupBy('pharmacy_drug_id');

        $drugs = [];
        foreach ($availableStocks as $drugId => $stocks) {
            $drug = $stocks->first()->drug;
            $totalQty = $stocks->sum('quantity');
            $drugs[] = [
                'id' => $drugId,
                'name' => $drug->name,
                'available_quantity' => $totalQty,
                'unit' => $drug->unit_of_measure,
            ];
        }

        return view('pharmacy.returns.create', compact('drugs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pharmacy_drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $return = $this->workflowService->createReturn($request->user(), $request->only(['notes']));

            foreach ($request->input('items') as $itemData) {
                PharmacyReturnItem::create([
                    'pharmacy_return_id' => $return->id,
                    'pharmacy_drug_id' => $itemData['drug_id'],
                    'quantity' => $itemData['quantity'],
                    // Logic for batch/expiry selection could be added here if needed, 
                    // otherwise it will be picked during submission (FEFO)
                ]);
            }

            DB::commit();

            return redirect()->route('pharmacy.returns.show', $return->id)
                ->with('success', 'Return draft created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock Return Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $return = PharmacyReturn::with(['items.drug', 'steps.actedBy.officer', 'command', 'createdBy.officer'])->findOrFail($id);
        return view('pharmacy.returns.show', compact('return'));
    }

    public function edit($id)
    {
        $return = PharmacyReturn::with('items')->findOrFail($id);
        
        if ($return->status !== 'DRAFT') {
            return redirect()->route('pharmacy.returns.show', $id)->with('error', 'Only drafts can be edited.');
        }

        // Similar to create, get available drugs
        $user = auth()->user();
        $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
        
        $availableStocks = PharmacyStock::where('command_id', $commandId)
            ->where('location_type', 'COMMAND_PHARMACY')
            ->where('quantity', '>', 0)
            ->with('drug')
            ->get()
            ->groupBy('pharmacy_drug_id');

        $drugs = [];
        foreach ($availableStocks as $drugId => $stocks) {
            $drug = $stocks->first()->drug;
            $totalQty = $stocks->sum('quantity');
            $drugs[] = [
                'id' => $drugId,
                'name' => $drug->name,
                'available_quantity' => $totalQty,
                'unit' => $drug->unit_of_measure,
            ];
        }

        return view('pharmacy.returns.edit', compact('return', 'drugs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.drug_id' => 'required|exists:pharmacy_drugs,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $return = PharmacyReturn::findOrFail($id);
        if ($return->status !== 'DRAFT') {
            return back()->with('error', 'Only drafts can be updated.');
        }

        try {
            DB::beginTransaction();

            $return->update($request->only(['notes']));

            // Replace items
            $return->items()->delete();
            foreach ($request->input('items') as $itemData) {
                PharmacyReturnItem::create([
                    'pharmacy_return_id' => $return->id,
                    'pharmacy_drug_id' => $itemData['drug_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('pharmacy.returns.show', $return->id)
                ->with('success', 'Return draft updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating return: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $return = PharmacyReturn::findOrFail($id);
        
        try {
            $this->workflowService->submitReturn($return, auth()->user());
            return redirect()->route('pharmacy.returns.show', $id)->with('success', 'Return submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function act(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:APPROVED,REJECTED',
            'comment' => 'nullable|string|max:1000',
        ]);

        $return = PharmacyReturn::findOrFail($id);

        try {
            $this->workflowService->actOnReturn($return, auth()->user(), $request->input('decision'), $request->input('comment'));
            return redirect()->route('pharmacy.returns.show', $id)
                ->with('success', 'Action recorded: ' . $request->input('decision'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(Request $request, $id)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $return = PharmacyReturn::findOrFail($id);

        try {
            $this->workflowService->receiveReturn($return, auth()->user(), $request->input('comment'));
            return redirect()->route('pharmacy.returns.show', $id)
                ->with('success', 'Return items received successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
