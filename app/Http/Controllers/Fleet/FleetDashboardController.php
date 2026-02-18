<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\DutyRoster;
use App\Models\FleetRequest;
use App\Models\FleetVehicle;
use App\Models\FleetVehicleAssignment;
use App\Services\Fleet\FleetWorkflowService;
use Illuminate\Http\Request;

class FleetDashboardController extends Controller
{
    public function ccTl(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'CC T&L', 'CC T&L Dashboard');
    }

    public function dcgFats(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'DCG FATS', 'DCG FATS Dashboard');
    }

    public function acgTs(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'ACG TS', 'ACG TS Dashboard');
    }

    public function cd(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'CD', 'CD Dashboard');
    }

    public function ocTl(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'O/C T&L', 'O/C T&L Dashboard');
    }

    public function storeReceiver(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'Transport Store/Receiver', 'Transport Store/Receiver Dashboard');
    }

    public function cgc(Request $request, FleetWorkflowService $workflow)
    {
        return $this->renderDashboard($request, $workflow, 'CGC', 'CGC Fleet');
    }

    private function renderDashboard(Request $request, FleetWorkflowService $workflow, string $roleName, string $title)
    {
        $user = $request->user();
        $commandId = $workflow->getActiveCommandIdForRole($user, $roleName);

        $inboxQuery = FleetRequest::query()
            ->whereNotNull('current_step_order')
            ->whereHas('steps', function ($q) use ($roleName) {
                $q->whereColumn('fleet_request_steps.step_order', 'fleet_requests.current_step_order')
                    ->where('fleet_request_steps.role_name', $roleName);
            });

        $commandScopedRoles = ['CD', 'O/C T&L', 'Transport Store/Receiver', 'Area Controller', 'OC Workshop', 'Staff Officer T&L'];
        if (in_array($roleName, $commandScopedRoles, true) && $commandId) {
            $inboxQuery->where('fleet_requests.origin_command_id', $commandId);
        }

        $inboxCount = $inboxQuery->count();
        $inboxItems = $inboxQuery
            ->with(['originCommand', 'createdBy'])
            ->orderByDesc('updated_at')
            ->take(8)
            ->get();

        $cards = [];
        $quickLinks = [];

        switch ($roleName) {
            case 'CD':
                $myRequests = FleetRequest::where('created_by', $user->id);
                $draftCount = (clone $myRequests)->where('status', 'DRAFT')->count();
                $submittedCount = (clone $myRequests)->where('status', 'SUBMITTED')->count();
                $kivCount = (clone $myRequests)->where('status', 'KIV')->count();
                $releasedCount = (clone $myRequests)->where('status', 'RELEASED')->count();

                $commandPoolCount = FleetVehicle::where('current_command_id', $commandId)
                    ->where('lifecycle_status', 'AT_COMMAND_POOL')
                    ->count();
                $assignedCount = FleetVehicleAssignment::active()
                    ->where('assigned_to_command_id', $commandId)
                    ->whereNotNull('assigned_to_officer_id')
                    ->count();
                $pendingReturns = FleetVehicleAssignment::active()
                    ->where('assigned_to_command_id', $commandId)
                    ->whereNotNull('assigned_to_officer_id')
                    ->whereDoesntHave('returnRecord')
                    ->count();

                $commandVehicles = FleetVehicle::where('current_command_id', $commandId);
                $totalVehicles = (clone $commandVehicles)->count();
                $serviceableCount = (clone $commandVehicles)->where('service_status', 'SERVICEABLE')->count();
                $unserviceableCount = (clone $commandVehicles)->where('service_status', 'UNSERVICEABLE')->count();

                $cards = [
                    ['label' => 'Total Vehicles (Command)', 'value' => $totalVehicles, 'icon' => 'ki-car', 'tone' => 'primary'],
                    ['label' => 'Serviceable', 'value' => $serviceableCount, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                    ['label' => 'Unserviceable', 'value' => $unserviceableCount, 'icon' => 'ki-information', 'tone' => 'warning'],
                    ['label' => 'My Draft Requests', 'value' => $draftCount, 'icon' => 'ki-file-up', 'tone' => 'primary'],
                    ['label' => 'Submitted Requests', 'value' => $submittedCount, 'icon' => 'ki-send', 'tone' => 'info'],
                    ['label' => 'KIV Requests', 'value' => $kivCount, 'icon' => 'ki-timer', 'tone' => 'warning'],
                    ['label' => 'Released Requests', 'value' => $releasedCount, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                    ['label' => 'Command Pool Vehicles', 'value' => $commandPoolCount, 'icon' => 'ki-car', 'tone' => 'primary'],
                    ['label' => 'Active Assignments', 'value' => $assignedCount, 'icon' => 'ki-user', 'tone' => 'info'],
                    ['label' => 'Pending Returns', 'value' => $pendingReturns, 'icon' => 'ki-refresh', 'tone' => 'warning'],
                ];

                $rosterApprovalsCount = $commandId ? DutyRoster::where('status', 'SUBMITTED')
                    ->where('command_id', $commandId)
                    ->whereNull('cd_approved_at')
                    ->where(function ($q) {
                        $q->whereIn('unit', ['Transport', 'Transport and Logistics'])
                            ->orWhereHas('assignments.officer', fn ($oq) => $oq->where('unit', 'Transport'));
                    })
                    ->count() : 0;

                $cards[] = ['label' => 'Roster Approvals (Transport)', 'value' => $rosterApprovalsCount, 'icon' => 'ki-calendar-tick', 'tone' => $rosterApprovalsCount > 0 ? 'warning' : 'secondary'];

                $quickLinks = [
                    ['label' => 'Create Request', 'href' => route('fleet.requests.create'), 'icon' => 'ki-file-up', 'tone' => 'primary'],
                    ['label' => 'View Requests', 'href' => route('fleet.requests.index'), 'icon' => 'ki-eye', 'tone' => 'info'],
                    ['label' => 'Command Vehicles', 'href' => route('fleet.vehicles.index'), 'icon' => 'ki-car', 'tone' => 'secondary'],
                    ['label' => 'Returns Report', 'href' => route('fleet.reports.returns'), 'icon' => 'ki-chart-simple', 'tone' => 'success'],
                ];
                if ($commandId) {
                    $quickLinks[] = [
                        'label' => 'Roster Approvals' . ($rosterApprovalsCount > 0 ? " ({$rosterApprovalsCount})" : ''),
                        'href' => route('fleet.roster.cd-index'),
                        'icon' => 'ki-calendar-tick',
                        'tone' => $rosterApprovalsCount > 0 ? 'warning' : 'secondary',
                    ];
                    $quickLinks[] = [
                        'label' => 'Internal Staff Orders (Transport)',
                        'href' => route('fleet.cd.internal-staff-orders.index'),
                        'icon' => 'ki-document',
                        'tone' => 'secondary',
                    ];
                }
                break;

            case 'O/C T&L':
            case 'Transport Store/Receiver':
                $commandVehicles = FleetVehicle::where('current_command_id', $commandId);
                $inStockCount = (clone $commandVehicles)->where('lifecycle_status', 'IN_STOCK')->count();
                $commandPoolCount = (clone $commandVehicles)->where('lifecycle_status', 'AT_COMMAND_POOL')->count();
                $reservedCount = (clone $commandVehicles)->whereNotNull('reserved_fleet_request_id')->count();

                $cards = [
                    ['label' => 'In Stock', 'value' => $inStockCount, 'icon' => 'ki-archive', 'tone' => 'primary'],
                    ['label' => 'Command Pool', 'value' => $commandPoolCount, 'icon' => 'ki-car', 'tone' => 'info'],
                    ['label' => 'Reserved Vehicles', 'value' => $reservedCount, 'icon' => 'ki-lock', 'tone' => 'warning'],
                    ['label' => 'Inbox Requests', 'value' => $inboxCount, 'icon' => 'ki-inbox', 'tone' => 'success'],
                ];

                $quickLinks = [
                    ['label' => 'Fleet Requests', 'href' => route('fleet.requests.index'), 'icon' => 'ki-file-up', 'tone' => 'primary'],
                    ['label' => 'Fleet Vehicles', 'href' => route('fleet.vehicles.index'), 'icon' => 'ki-car', 'tone' => 'secondary'],
                ];

                if ($roleName === 'Transport Store/Receiver') {
                    $quickLinks[] = ['label' => 'Intake Vehicle', 'href' => route('fleet.vehicles.intake.create'), 'icon' => 'ki-plus', 'tone' => 'success'];
                }
                break;

            case 'CC T&L':
                $totalVehicles = FleetVehicle::count();
                $serviceableCount = FleetVehicle::where('service_status', 'SERVICEABLE')->count();
                $unserviceableCount = FleetVehicle::where('service_status', 'UNSERVICEABLE')->count();
                $inStockCount = FleetVehicle::where('lifecycle_status', 'IN_STOCK')->whereNull('reserved_fleet_request_id')->count();
                $reservedCount = FleetVehicle::whereNotNull('reserved_fleet_request_id')->count();
                $kivCount = FleetRequest::where('status', 'KIV')->count();
                $pendingInventory = FleetRequest::where('current_step_order', 1)->where('request_type', 'FLEET_NEW_VEHICLE')->count();
                $pendingRelease = FleetRequest::where('current_step_order', 5)->count();

                $cards = [
                    ['label' => 'Total Vehicles', 'value' => $totalVehicles, 'icon' => 'ki-car', 'tone' => 'primary'],
                    ['label' => 'Serviceable', 'value' => $serviceableCount, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                    ['label' => 'Unserviceable', 'value' => $unserviceableCount, 'icon' => 'ki-information', 'tone' => 'warning'],
                    ['label' => 'In Stock', 'value' => $inStockCount, 'icon' => 'ki-archive', 'tone' => 'primary'],
                    ['label' => 'Reserved', 'value' => $reservedCount, 'icon' => 'ki-lock', 'tone' => 'warning'],
                    ['label' => 'KIV Requests', 'value' => $kivCount, 'icon' => 'ki-timer', 'tone' => 'info'],
                    ['label' => 'Inventory Checks', 'value' => $pendingInventory, 'icon' => 'ki-search', 'tone' => 'primary'],
                    ['label' => 'Release Pending', 'value' => $pendingRelease, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                ];

                $quickLinks = [
                    ['label' => 'Fleet Requests', 'href' => route('fleet.requests.index'), 'icon' => 'ki-file-up', 'tone' => 'primary'],
                    ['label' => 'Fleet Vehicles', 'href' => route('fleet.vehicles.index'), 'icon' => 'ki-car', 'tone' => 'secondary'],
                    ['label' => 'Returns Report', 'href' => route('fleet.reports.returns'), 'icon' => 'ki-chart-simple', 'tone' => 'success'],
                ];
                break;

            case 'ACG TS':
            case 'DCG FATS':
            case 'CGC':
                $pendingApproval = $inboxCount;
                $totalVehicles = FleetVehicle::count();
                $serviceableCount = FleetVehicle::where('service_status', 'SERVICEABLE')->count();
                $unserviceableCount = FleetVehicle::where('service_status', 'UNSERVICEABLE')->count();

                $cards = [
                    ['label' => 'Total Vehicles', 'value' => $totalVehicles, 'icon' => 'ki-car', 'tone' => 'primary'],
                    ['label' => 'Serviceable', 'value' => $serviceableCount, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                    ['label' => 'Unserviceable', 'value' => $unserviceableCount, 'icon' => 'ki-information', 'tone' => 'warning'],
                    ['label' => 'Inbox Requests', 'value' => $inboxCount, 'icon' => 'ki-inbox', 'tone' => 'primary'],
                    ['label' => 'Pending Approval', 'value' => $pendingApproval, 'icon' => 'ki-check-circle', 'tone' => 'success'],
                    ['label' => 'KIV Requests', 'value' => FleetRequest::where('status', 'KIV')->count(), 'icon' => 'ki-timer', 'tone' => 'warning'],
                ];

                $quickLinks = [
                    ['label' => 'Fleet Requests', 'href' => route('fleet.requests.index'), 'icon' => 'ki-file-up', 'tone' => 'primary'],
                    ['label' => 'Fleet Vehicles', 'href' => route('fleet.vehicles.index'), 'icon' => 'ki-car', 'tone' => 'secondary'],
                ];
                break;
        }

        return view('dashboards.fleet.dashboard', [
            'title' => $title,
            'roleName' => $roleName,
            'cards' => $cards,
            'quickLinks' => $quickLinks,
            'inboxCount' => $inboxCount,
            'inboxItems' => $inboxItems,
            'commandId' => $commandId,
        ]);
    }
}

