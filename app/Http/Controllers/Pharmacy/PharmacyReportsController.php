<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\PharmacyDrug;
use App\Models\PharmacyStock;
use App\Models\PharmacyStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyReportsController extends Controller
{
    /**
     * Stock Balance Report
     */
    public function stockBalance(Request $request)
    {
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');

        $query = PharmacyStock::with(['drug', 'command'])
            ->withStock();

        if ($locationType === 'CENTRAL_STORE') {
            $query->centralStore();
        } elseif ($locationType === 'COMMAND_PHARMACY') {
            $query->commandPharmacy();
            if ($commandId) {
                $query->byCommand($commandId);
            }
        }

        $stocks = $query->get();

        // Group by drug for summary
        $summary = $stocks->groupBy('pharmacy_drug_id')->map(function ($items) {
            $drug = $items->first()->drug;
            return [
                'drug' => $drug,
                'central_store' => $items->where('location_type', 'CENTRAL_STORE')->sum('quantity'),
                'command_pharmacies' => $items->where('location_type', 'COMMAND_PHARMACY')->sum('quantity'),
                'total' => $items->sum('quantity'),
                'items' => $items,
            ];
        })->sortBy('drug.name');

        $commands = Command::where('is_active', true)->orderBy('name')->get();

        return view('pharmacy.reports.stock-balance', compact('stocks', 'summary', 'locationType', 'commandId', 'commands'));
    }

    /**
     * Print Stock Balance Report
     */
    public function printStockBalance(Request $request)
    {
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');

        $query = PharmacyStock::with(['drug', 'command'])
            ->withStock();

        if ($locationType === 'CENTRAL_STORE') {
            $query->centralStore();
        } elseif ($locationType === 'COMMAND_PHARMACY') {
            $query->commandPharmacy();
            if ($commandId) {
                $query->byCommand($commandId);
            }
        }

        $stocks = $query->get();

        $summary = $stocks->groupBy('pharmacy_drug_id')->map(function ($items) {
            $drug = $items->first()->drug;
            return [
                'drug' => $drug,
                'central_store' => $items->where('location_type', 'CENTRAL_STORE')->sum('quantity'),
                'command_pharmacies' => $items->where('location_type', 'COMMAND_PHARMACY')->sum('quantity'),
                'total' => $items->sum('quantity'),
                'items' => $items,
            ];
        })->sortBy('drug.name');

        $command = $commandId ? Command::find($commandId) : null;
        $generatedBy = Auth::user()->officer->full_name ?? Auth::user()->email;

        return view('prints.pharmacy.stock-balance', compact('stocks', 'summary', 'locationType', 'command', 'generatedBy'));
    }

    /**
     * Expiry Date Report
     */
    public function expiryReport(Request $request)
    {
        $days = (int) $request->get('days', 90);
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');
        $includeExpired = $request->boolean('include_expired', true);

        $query = PharmacyStock::with(['drug', 'command'])
            ->withStock()
            ->whereNotNull('expiry_date');

        if ($locationType === 'CENTRAL_STORE') {
            $query->centralStore();
        } elseif ($locationType === 'COMMAND_PHARMACY') {
            $query->commandPharmacy();
            if ($commandId) {
                $query->byCommand($commandId);
            }
        }

        if ($includeExpired) {
            $query->where('expiry_date', '<=', now()->addDays($days));
        } else {
            $query->expiringSoon($days);
        }

        $stocks = $query->orderBy('expiry_date')->get();

        // Categorize by expiry status
        $expired = $stocks->filter(fn ($s) => $s->isExpired());
        $expiringSoon = $stocks->filter(fn ($s) => !$s->isExpired() && $s->isExpiringSoon($days));

        $commands = Command::where('is_active', true)->orderBy('name')->get();

        return view('pharmacy.reports.expiry', compact('stocks', 'expired', 'expiringSoon', 'days', 'locationType', 'commandId', 'commands', 'includeExpired'));
    }

    /**
     * Print Expiry Report
     */
    public function printExpiryReport(Request $request)
    {
        $days = (int) $request->get('days', 90);
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');
        $includeExpired = $request->boolean('include_expired', true);

        $query = PharmacyStock::with(['drug', 'command'])
            ->withStock()
            ->whereNotNull('expiry_date');

        if ($locationType === 'CENTRAL_STORE') {
            $query->centralStore();
        } elseif ($locationType === 'COMMAND_PHARMACY') {
            $query->commandPharmacy();
            if ($commandId) {
                $query->byCommand($commandId);
            }
        }

        if ($includeExpired) {
            $query->where('expiry_date', '<=', now()->addDays($days));
        } else {
            $query->expiringSoon($days);
        }

        $stocks = $query->orderBy('expiry_date')->get();

        $expired = $stocks->filter(fn ($s) => $s->isExpired());
        $expiringSoon = $stocks->filter(fn ($s) => !$s->isExpired() && $s->isExpiringSoon($days));

        $command = $commandId ? Command::find($commandId) : null;
        $generatedBy = Auth::user()->officer->full_name ?? Auth::user()->email;

        return view('prints.pharmacy.expiry-report', compact('stocks', 'expired', 'expiringSoon', 'days', 'locationType', 'command', 'generatedBy', 'includeExpired'));
    }

    /**
     * Custom Report - Searchable by any criteria
     */
    public function customReport(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');
        $minQuantity = $request->get('min_quantity');
        $maxQuantity = $request->get('max_quantity');
        $expiryFrom = $request->get('expiry_from');
        $expiryTo = $request->get('expiry_to');
        $movementType = $request->get('movement_type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $reportType = $request->get('report_type', 'stock'); // stock or movements

        $results = collect();
        $movements = collect();

        if ($reportType === 'stock') {
            $query = PharmacyStock::with(['drug', 'command']);

            if ($search) {
                $query->whereHas('drug', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($category) {
                $query->whereHas('drug', function ($q) use ($category) {
                    $q->where('category', $category);
                });
            }

            if ($locationType) {
                $query->where('location_type', $locationType);
            }

            if ($commandId) {
                $query->byCommand($commandId);
            }

            if ($minQuantity !== null) {
                $query->where('quantity', '>=', $minQuantity);
            }

            if ($maxQuantity !== null) {
                $query->where('quantity', '<=', $maxQuantity);
            }

            if ($expiryFrom) {
                $query->where('expiry_date', '>=', $expiryFrom);
            }

            if ($expiryTo) {
                $query->where('expiry_date', '<=', $expiryTo);
            }

            $results = $query->orderBy('updated_at', 'desc')->paginate(50);
        } elseif ($reportType === 'movements') {
            $query = PharmacyStockMovement::with(['drug', 'command', 'createdBy.officer']);

            if ($search) {
                $query->whereHas('drug', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($movementType) {
                $query->byMovementType($movementType);
            }

            if ($locationType) {
                $query->where('location_type', $locationType);
            }

            if ($commandId) {
                $query->byCommand($commandId);
            }

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

            $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        }

        // Get filter options
        $commands = Command::where('is_active', true)->orderBy('name')->get();
        $categories = PharmacyDrug::distinct()->pluck('category')->filter()->sort();
        $movementTypes = ['PROCUREMENT_RECEIPT', 'REQUISITION_ISSUE', 'ADJUSTMENT', 'DISPENSED'];

        return view('pharmacy.reports.custom', compact(
            'results',
            'movements',
            'reportType',
            'search',
            'category',
            'locationType',
            'commandId',
            'minQuantity',
            'maxQuantity',
            'expiryFrom',
            'expiryTo',
            'movementType',
            'dateFrom',
            'dateTo',
            'commands',
            'categories',
            'movementTypes'
        ));
    }

    /**
     * Print Custom Report
     */
    public function printCustomReport(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $locationType = $request->get('location_type');
        $commandId = $request->get('command_id');
        $minQuantity = $request->get('min_quantity');
        $maxQuantity = $request->get('max_quantity');
        $expiryFrom = $request->get('expiry_from');
        $expiryTo = $request->get('expiry_to');
        $movementType = $request->get('movement_type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $reportType = $request->get('report_type', 'stock');

        $results = collect();
        $movements = collect();

        if ($reportType === 'stock') {
            $query = PharmacyStock::with(['drug', 'command']);

            if ($search) {
                $query->whereHas('drug', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($category) {
                $query->whereHas('drug', function ($q) use ($category) {
                    $q->where('category', $category);
                });
            }

            if ($locationType) {
                $query->where('location_type', $locationType);
            }

            if ($commandId) {
                $query->byCommand($commandId);
            }

            if ($minQuantity !== null) {
                $query->where('quantity', '>=', $minQuantity);
            }

            if ($maxQuantity !== null) {
                $query->where('quantity', '<=', $maxQuantity);
            }

            if ($expiryFrom) {
                $query->where('expiry_date', '>=', $expiryFrom);
            }

            if ($expiryTo) {
                $query->where('expiry_date', '<=', $expiryTo);
            }

            $results = $query->orderBy('updated_at', 'desc')->get();
        } elseif ($reportType === 'movements') {
            $query = PharmacyStockMovement::with(['drug', 'command', 'createdBy.officer']);

            if ($search) {
                $query->whereHas('drug', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($movementType) {
                $query->byMovementType($movementType);
            }

            if ($locationType) {
                $query->where('location_type', $locationType);
            }

            if ($commandId) {
                $query->byCommand($commandId);
            }

            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

            $movements = $query->orderBy('created_at', 'desc')->get();
        }

        $command = $commandId ? Command::find($commandId) : null;
        $generatedBy = Auth::user()->officer->full_name ?? Auth::user()->email;

        // Build filters description
        $filters = [];
        if ($search) $filters['Search'] = $search;
        if ($category) $filters['Category'] = $category;
        if ($locationType) $filters['Location'] = $locationType === 'CENTRAL_STORE' ? 'Central Store' : 'Command Pharmacy';
        if ($command) $filters['Command'] = $command->name;
        if ($expiryFrom) $filters['Expiry From'] = $expiryFrom;
        if ($expiryTo) $filters['Expiry To'] = $expiryTo;
        if ($movementType) $filters['Movement Type'] = $movementType;
        if ($dateFrom) $filters['Date From'] = $dateFrom;
        if ($dateTo) $filters['Date To'] = $dateTo;

        return view('prints.pharmacy.custom-report', compact(
            'results',
            'movements',
            'reportType',
            'filters',
            'generatedBy'
        ));
    }
}
