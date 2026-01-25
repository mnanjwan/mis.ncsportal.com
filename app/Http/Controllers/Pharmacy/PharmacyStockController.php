<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\PharmacyDrug;
use App\Models\PharmacyStock;
use App\Models\PharmacyStockMovement;
use App\Services\Pharmacy\PharmacyWorkflowService;
use Illuminate\Http\Request;

class PharmacyStockController extends Controller
{
    protected PharmacyWorkflowService $workflowService;

    public function __construct(PharmacyWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of stock.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $locationType = $request->get('location_type', 'CENTRAL_STORE');
        $commandId = $request->get('command_id');
        $search = $request->get('search');

        $isCommandPharmacistOnly = $user->hasRole('Command Pharmacist') 
            && !$user->hasRole('OC Pharmacy') 
            && !$user->hasRole('Central Medical Store');

        $userCommandId = null;
        if ($isCommandPharmacistOnly) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
        }

        $query = PharmacyStock::with(['drug', 'command']);

        // Filter by location type
        if ($locationType === 'CENTRAL_STORE') {
            $query->centralStore();
        } elseif ($locationType === 'COMMAND_PHARMACY') {
            $query->commandPharmacy();

            // If user is Command Pharmacist only, force their command (ignore URL parameter)
            if ($isCommandPharmacistOnly && $userCommandId) {
                $query->byCommand($userCommandId);
                $commandId = $userCommandId; // Override any URL parameter
            } elseif ($commandId) {
                $query->byCommand($commandId);
            }
        }

        // Search by drug name
        if ($search) {
            $query->whereHas('drug', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Only show items with stock
        $query->withStock();

        $stocks = $query->orderBy('quantity', 'asc')->paginate(30);

        // Get commands for filter dropdown - only show if user has broader access
        $commands = collect();
        if (!$isCommandPharmacistOnly) {
            $commands = Command::where('is_active', true)->orderBy('name')->get();
        }

        return view('pharmacy.stocks.index', compact('stocks', 'locationType', 'commandId', 'commands', 'search'));
    }

    /**
     * Display the specified drug's stock details.
     */
    public function show(Request $request, $drugId)
    {
        $drug = PharmacyDrug::findOrFail($drugId);
        $user = $request->user();

        $isCommandPharmacistOnly = $user->hasRole('Command Pharmacist') 
            && !$user->hasRole('OC Pharmacy') 
            && !$user->hasRole('Central Medical Store');

        $userCommandId = null;
        if ($isCommandPharmacistOnly) {
            $userCommandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');
        }

        // Central store stock (visible to all pharmacy roles)
        $centralStock = PharmacyStock::where('pharmacy_drug_id', $drugId)
            ->centralStore()
            ->withStock()
            ->get();

        // Command pharmacy stocks
        $commandStocksQuery = PharmacyStock::where('pharmacy_drug_id', $drugId)
            ->commandPharmacy()
            ->withStock()
            ->with('command');

        // If Command Pharmacist only, show only their command
        if ($isCommandPharmacistOnly && $userCommandId) {
            $commandStocksQuery->byCommand($userCommandId);
        }

        $commandStocks = $commandStocksQuery->get();

        // Recent movements - filter by command for Command Pharmacist
        $movementsQuery = PharmacyStockMovement::where('pharmacy_drug_id', $drugId)
            ->with(['createdBy.officer', 'command'])
            ->latest();

        // Command Pharmacist can only see movements related to Central Store or their command
        if ($isCommandPharmacistOnly && $userCommandId) {
            $movementsQuery->where(function ($q) use ($userCommandId) {
                $q->where('location_type', 'CENTRAL_STORE')
                    ->orWhere('command_id', $userCommandId);
            });
        }

        $movements = $movementsQuery->take(50)->get();

        return view('pharmacy.stocks.show', compact('drug', 'centralStock', 'commandStocks', 'movements'));
    }

    /**
     * Stock adjustment (for Central Medical Store).
     */
    public function adjust(Request $request, $stockId)
    {
        $user = $request->user();

        if (!$user->hasRole('Central Medical Store') && !$user->hasRole('OC Pharmacy')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        $stock = PharmacyStock::findOrFail($stockId);

        $adjustment = (int) $request->input('adjustment');
        $newQuantity = $stock->quantity + $adjustment;

        if ($newQuantity < 0) {
            return back()->with('error', 'Adjustment would result in negative stock.');
        }

        // Record movement
        PharmacyStockMovement::create([
            'pharmacy_drug_id' => $stock->pharmacy_drug_id,
            'movement_type' => 'ADJUSTMENT',
            'location_type' => $stock->location_type,
            'command_id' => $stock->command_id,
            'quantity' => $adjustment,
            'expiry_date' => $stock->expiry_date,
            'batch_number' => $stock->batch_number,
            'notes' => $request->input('reason'),
            'created_by' => $user->id,
        ]);

        $stock->update(['quantity' => $newQuantity]);

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
