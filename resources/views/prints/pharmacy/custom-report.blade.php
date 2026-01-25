<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Custom Report - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            position: relative;
        }
        body::after {
            content: "NCS Management Information System (MIS)";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 28pt;
            font-weight: bold;
            color: #228B22;
            opacity: 0.25;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
            font-family: 'Times New Roman', serif;
            display: block;
            width: 80%;
            max-width: 80%;
            text-align: center;
        }
        @media print {
            body::after {
                opacity: 0.20;
                z-index: -1;
                font-size: 24pt;
                width: 70%;
                max-width: 70%;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .report-info {
            margin: 15px 0;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }
        td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 10pt;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .restricted {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 5px 0;
        }
        .restricted-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            padding: 5px 0;
            background: white;
            z-index: 1000;
            display: none;
        }
        .restricted-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            padding: 5px 0;
            background: white;
            z-index: 1000;
            display: none;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9pt;
        }
        .filters-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        @media print {
            @page {
                margin-top: 20mm;
                margin-bottom: 20mm;
            }
            body {
                margin: 0;
                padding-top: 15mm;
                padding-bottom: 15mm;
            }
            .no-print {
                display: none;
            }
            .restricted-header,
            .restricted-footer {
                display: block;
            }
            .restricted:not(.restricted-header):not(.restricted-footer) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>

    <div class="restricted">RESTRICTED</div>

    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h2>PHARMACY {{ $reportType === 'stock' ? 'STOCK' : 'MOVEMENTS' }} REPORT</h2>
    </div>

    <div class="report-info">
        <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
        <p><strong>Generated By:</strong> {{ $generatedBy }}</p>
    </div>

    @if(count($filters) > 0)
        <div class="filters-box">
            <strong>Applied Filters:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($filters as $key => $value)
                    <li>{{ $key }}: {{ $value }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($reportType === 'stock')
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Drug</th>
                    <th style="width: 25%;">Location</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 10%;">Batch</th>
                    <th style="width: 10%;">Expiry</th>
                    <th style="width: 10%;">Updated</th>
                </tr>
            </thead>
            <tbody>
                @php $sn = 1; @endphp
                @forelse($results as $stock)
                    <tr>
                        <td>{{ $sn++ }}</td>
                        <td>{{ $stock->drug->name ?? 'Unknown' }}</td>
                        <td>{{ $stock->getLocationName() }}</td>
                        <td class="text-center">{{ number_format($stock->quantity) }}</td>
                        <td>{{ $stock->batch_number ?? '-' }}</td>
                        <td>{{ $stock->expiry_date ? $stock->expiry_date->format('d M Y') : '-' }}</td>
                        <td>{{ $stock->updated_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">No stock data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <p><strong>Total Records:</strong> {{ $results->count() }}</p>
            <p><strong>Total Quantity:</strong> {{ number_format($results->sum('quantity')) }} units</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 25%;">Drug</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 20%;">Location</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">By</th>
                </tr>
            </thead>
            <tbody>
                @php $sn = 1; @endphp
                @forelse($movements as $movement)
                    <tr>
                        <td>{{ $sn++ }}</td>
                        <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $movement->drug->name ?? 'Unknown' }}</td>
                        <td>{{ $movement->getMovementTypeLabel() }}</td>
                        <td>{{ $movement->getLocationName() }}</td>
                        <td class="text-center {{ $movement->isAddition() ? 'positive' : 'negative' }} font-bold">
                            {{ $movement->isAddition() ? '+' : '' }}{{ number_format($movement->quantity) }}
                        </td>
                        <td>{{ $movement->createdBy->officer->full_name ?? $movement->createdBy->email ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">No movement records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            <p><strong>Total Records:</strong> {{ $movements->count() }}</p>
            <p><strong>Net Movement:</strong> 
                @php
                    $net = $movements->sum('quantity');
                @endphp
                <span class="{{ $net >= 0 ? 'positive' : 'negative' }} font-bold">
                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }} units
                </span>
            </p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by NCS Employee Portal - Pharmacy Management System</p>
    </div>
</body>
</html>
