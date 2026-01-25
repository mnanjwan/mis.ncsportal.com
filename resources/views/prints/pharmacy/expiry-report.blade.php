<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Expiry Report - Print</title>
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
        .expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        .expiring-soon {
            background-color: #fff3cd;
            color: #856404;
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
        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            display: inline-block;
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
        <h2>DRUG EXPIRY REPORT</h2>
        <p style="margin-top: 5px; font-size: 12pt;">
            Items expiring within {{ $days }} days
            @if($locationType)
                | Location: {{ $locationType === 'CENTRAL_STORE' ? 'Central Medical Store' : 'Command Pharmacies' }}
            @endif
            @if($command)
                - {{ $command->name }}
            @endif
        </p>
    </div>

    <div class="report-info">
        <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
        <p><strong>Generated By:</strong> {{ $generatedBy }}</p>
    </div>

    <div style="margin-bottom: 15px;">
        <div class="summary-box" style="background-color: #f8d7da; border-color: #721c24;">
            <strong>Expired Items:</strong> {{ $expired->count() }}
        </div>
        <div class="summary-box" style="background-color: #fff3cd; border-color: #856404; margin-left: 15px;">
            <strong>Expiring Soon:</strong> {{ $expiringSoon->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Drug Name</th>
                <th style="width: 20%;">Location</th>
                <th style="width: 10%;">Batch</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 12%;">Expiry Date</th>
                <th style="width: 10%;">Days Left</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; @endphp
            @forelse($stocks as $stock)
                @php
                    $isExpired = $stock->isExpired();
                    $daysLeft = $stock->getDaysUntilExpiry();
                @endphp
                <tr class="{{ $isExpired ? 'expired' : ($daysLeft <= 30 ? 'expiring-soon' : '') }}">
                    <td>{{ $sn++ }}</td>
                    <td>{{ $stock->drug->name ?? 'Unknown' }}</td>
                    <td>{{ $stock->getLocationName() }}</td>
                    <td>{{ $stock->batch_number ?? '-' }}</td>
                    <td class="text-center">{{ number_format($stock->quantity) }}</td>
                    <td>{{ $stock->expiry_date->format('d M Y') }}</td>
                    <td class="text-center">
                        @if($daysLeft !== null)
                            @if($daysLeft < 0)
                                {{ abs($daysLeft) }} ago
                            @elseif($daysLeft === 0)
                                Today
                            @else
                                {{ $daysLeft }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center font-bold">
                        @if($isExpired)
                            EXPIRED
                        @elseif($daysLeft <= 30)
                            CRITICAL
                        @elseif($daysLeft <= 90)
                            SOON
                        @else
                            OK
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">No items found matching criteria</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Summary:</strong></p>
        <ul style="list-style-type: none; padding-left: 0;">
            <li>Total Items in Report: {{ $stocks->count() }}</li>
            <li>Expired Items: {{ $expired->count() }}</li>
            <li>Items Expiring Within {{ $days }} Days: {{ $expiringSoon->count() }}</li>
            <li>Total Quantity at Risk: {{ number_format($stocks->sum('quantity')) }} units</li>
        </ul>
    </div>

    <div style="margin-top: 20px; border-top: 1px solid #000; padding-top: 10px;">
        <p><strong>Legend:</strong></p>
        <ul>
            <li><span style="background-color: #f8d7da; padding: 2px 5px;">Red Background</span> = Expired items requiring immediate disposal</li>
            <li><span style="background-color: #fff3cd; padding: 2px 5px;">Yellow Background</span> = Items expiring soon (within 30 days), prioritize for use</li>
        </ul>
    </div>

    <div class="footer">
        <p>Generated by NCS Employee Portal - Pharmacy Management System</p>
    </div>
</body>
</html>
