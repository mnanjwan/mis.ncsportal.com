<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProcurement;
use App\Models\PharmacyRequisition;
use App\Models\PharmacyStock;
use App\Services\Pharmacy\PharmacyWorkflowService;
use Illuminate\Http\Request;

class PharmacyDashboardController extends Controller
{
    protected PharmacyWorkflowService $workflowService;

    public function __construct(PharmacyWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Controller Procurement Dashboard
     */
    public function controllerProcurement(Request $request)
    {
        $user = $request->user();

        $myProcurements = PharmacyProcurement::where('created_by', $user->id)
            ->with(['items.drug', 'steps'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'draft' => PharmacyProcurement::where('created_by', $user->id)->draft()->count(),
            'submitted' => PharmacyProcurement::where('created_by', $user->id)->submitted()->count(),
            'approved' => PharmacyProcurement::where('created_by', $user->id)->approved()->count(),
            'received' => PharmacyProcurement::where('created_by', $user->id)->where('status', 'RECEIVED')->count(),
        ];

        return view('dashboards.pharmacy.controller-procurement', compact('myProcurements', 'stats'));
    }

    /**
     * OC Pharmacy Dashboard
     */
    public function ocPharmacy(Request $request)
    {
        $user = $request->user();

        // Pending procurements for approval
        $pendingProcurements = PharmacyProcurement::whereHas('steps', function ($q) {
            $q->where('role_name', 'OC Pharmacy')
                ->whereColumn('step_order', 'pharmacy_procurements.current_step_order')
                ->whereNull('acted_at');
        })
            ->with(['items.drug', 'createdBy'])
            ->latest()
            ->take(10)
            ->get();

        // Pending requisitions for approval
        $pendingRequisitions = PharmacyRequisition::whereHas('steps', function ($q) {
            $q->where('role_name', 'OC Pharmacy')
                ->whereColumn('step_order', 'pharmacy_requisitions.current_step_order')
                ->whereNull('acted_at');
        })
            ->with(['items.drug', 'createdBy', 'command'])
            ->latest()
            ->take(10)
            ->get();

        // Stock alerts
        $lowStock = PharmacyStock::where('quantity', '<', 10)
            ->where('quantity', '>', 0)
            ->with('drug')
            ->take(10)
            ->get();

        $expiringSoon = PharmacyStock::expiringSoon(90)
            ->withStock()
            ->with('drug')
            ->take(10)
            ->get();

        $stats = [
            'pending_procurements' => $pendingProcurements->count(),
            'pending_requisitions' => $pendingRequisitions->count(),
            'low_stock_items' => $lowStock->count(),
            'expiring_soon' => $expiringSoon->count(),
        ];

        return view('dashboards.pharmacy.oc-pharmacy', compact(
            'pendingProcurements',
            'pendingRequisitions',
            'lowStock',
            'expiringSoon',
            'stats'
        ));
    }

    /**
     * Central Medical Store Dashboard
     */
    public function centralMedicalStore(Request $request)
    {
        $user = $request->user();

        // Approved procurements awaiting receipt
        $pendingReceipt = PharmacyProcurement::where('status', 'APPROVED')
            ->whereHas('steps', function ($q) {
                $q->where('role_name', 'Central Medical Store')
                    ->whereColumn('step_order', 'pharmacy_procurements.current_step_order')
                    ->whereNull('acted_at');
            })
            ->with(['items.drug', 'createdBy'])
            ->latest()
            ->take(10)
            ->get();

        // Approved requisitions awaiting issue
        $pendingIssue = PharmacyRequisition::where('status', 'APPROVED')
            ->whereHas('steps', function ($q) {
                $q->where('role_name', 'Central Medical Store')
                    ->whereColumn('step_order', 'pharmacy_requisitions.current_step_order')
                    ->whereNull('acted_at');
            })
            ->with(['items.drug', 'createdBy', 'command'])
            ->latest()
            ->take(10)
            ->get();

        // Central store stock overview
        $stockOverview = PharmacyStock::centralStore()
            ->withStock()
            ->with('drug')
            ->orderBy('quantity', 'asc')
            ->take(20)
            ->get();

        $stats = [
            'pending_receipt' => $pendingReceipt->count(),
            'pending_issue' => $pendingIssue->count(),
            'total_stock_items' => PharmacyStock::centralStore()->withStock()->count(),
        ];

        return view('dashboards.pharmacy.central-medical-store', compact(
            'pendingReceipt',
            'pendingIssue',
            'stockOverview',
            'stats'
        ));
    }

    /**
     * Command Pharmacist Dashboard
     */
    public function commandPharmacist(Request $request)
    {
        $user = $request->user();
        $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');

        if (!$commandId) {
            return view('dashboards.pharmacy.command-pharmacist', [
                'myRequisitions' => collect(),
                'commandStock' => collect(),
                'readyToDispense' => collect(),
                'stats' => [
                    'draft' => 0,
                    'submitted' => 0,
                    'issued' => 0,
                    'command_stock_items' => 0,
                ],
                'commandId' => null,
                'commandName' => null,
            ])->with('error', 'You are not assigned to any command. Please contact administration.');
        }

        // Get command name for display
        $command = \App\Models\Command::find($commandId);
        $commandName = $command ? $command->name : 'Unknown Command';

        // Only show requisitions from this user AND this command (extra safety)
        $myRequisitions = PharmacyRequisition::where('created_by', $user->id)
            ->where('command_id', $commandId)
            ->with(['items.drug', 'steps', 'command'])
            ->latest()
            ->take(10)
            ->get();

        // Command pharmacy stock - strictly for this command
        $commandStock = PharmacyStock::commandPharmacy()
            ->byCommand($commandId)
            ->withStock()
            ->with('drug')
            ->orderBy('quantity', 'asc')
            ->take(20)
            ->get();

        // Issued requisitions ready for dispensing - strictly for this command
        $readyToDispense = PharmacyRequisition::where('command_id', $commandId)
            ->where('status', 'ISSUED')
            ->with(['items.drug'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'draft' => PharmacyRequisition::where('created_by', $user->id)
                ->where('command_id', $commandId)
                ->draft()
                ->count(),
            'submitted' => PharmacyRequisition::where('created_by', $user->id)
                ->where('command_id', $commandId)
                ->submitted()
                ->count(),
            'issued' => PharmacyRequisition::where('command_id', $commandId)
                ->where('status', 'ISSUED')
                ->count(),
            'command_stock_items' => $commandStock->count(),
        ];

        return view('dashboards.pharmacy.command-pharmacist', compact(
            'myRequisitions',
            'commandStock',
            'readyToDispense',
            'stats',
            'commandId',
            'commandName'
        ));
    }

    /**
     * Command Pharmacist: dedicated Ready to Dispense page (issued requisitions with drug names).
     */
    public function readyToDispense(Request $request)
    {
        $user = $request->user();
        $commandId = $this->workflowService->getActiveCommandIdForRole($user, 'Command Pharmacist');

        if (!$commandId) {
            return redirect()
                ->route('pharmacy.command-pharmacist.dashboard')
                ->with('error', 'You are not assigned to any command. Please contact administration.');
        }

        $command = \App\Models\Command::find($commandId);
        $commandName = $command ? $command->name : 'Unknown Command';

        $requisitions = PharmacyRequisition::where('command_id', $commandId)
            ->where('status', 'ISSUED')
            ->with(['items.drug'])
            ->latest()
            ->get();

        return view('pharmacy.ready-to-dispense', compact('requisitions', 'commandName'));
    }
}
