<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Balance Report - Print</title>
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
        .low-stock {
            color: #dc3545;
            font-weight: bold;
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
        <h2>PHARMACY STOCK BALANCE REPORT</h2>
        @if($locationType)
            <p style="margin-top: 5px; font-size: 12pt;">
                Location: {{ $locationType === 'CENTRAL_STORE' ? 'Central Medical Store' : 'Command Pharmacies' }}
                @if($command)
                    - {{ $command->name }}
                @endif
            </p>
        @endif
    </div>

    <div class="report-info">
        <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
        <p><strong>Generated By:</strong> {{ $generatedBy }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Drug Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 10%;">Unit</th>
                <th class="text-center" style="width: 12%;">Central Store</th>
                <th class="text-center" style="width: 12%;">Commands</th>
                <th class="text-center" style="width: 11%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $sn = 1; @endphp
            @forelse($summary as $item)
                <tr>
                    <td>{{ $sn++ }}</td>
                    <td>{{ $item['drug']->name ?? 'Unknown' }}</td>
                    <td>{{ $item['drug']->category ?? '-' }}</td>
                    <td>{{ $item['drug']->unit_of_measure ?? 'units' }}</td>
                    <td class="text-center {{ $item['central_store'] < 10 ? 'low-stock' : '' }}">
                        {{ number_format($item['central_store']) }}
                    </td>
                    <td class="text-center">{{ number_format($item['command_pharmacies']) }}</td>
                    <td class="text-center font-bold">{{ number_format($item['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No stock data available</td>
                </tr>
            @endforelse
        </tbody>
        @if($summary->count() > 0)
            <tfoot>
                <tr style="background-color: #f0f0f0;">
                    <td colspan="4" class="text-right font-bold">TOTALS:</td>
                    <td class="text-center font-bold">{{ number_format($summary->sum('central_store')) }}</td>
                    <td class="text-center font-bold">{{ number_format($summary->sum('command_pharmacies')) }}</td>
                    <td class="text-center font-bold">{{ number_format($summary->sum('total')) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div style="margin-top: 20px;">
        <p><strong>Summary:</strong></p>
        <ul style="list-style-type: none; padding-left: 0;">
            <li>Total Drug Types: {{ $summary->count() }}</li>
            <li>Total Units in Central Store: {{ number_format($summary->sum('central_store')) }}</li>
            <li>Total Units in Command Pharmacies: {{ number_format($summary->sum('command_pharmacies')) }}</li>
            <li>Grand Total Units: {{ number_format($summary->sum('total')) }}</li>
        </ul>
    </div>

    <div class="footer">
        <p>Generated by NCS Employee Portal - Pharmacy Management System</p>
    </div>
</body>
</html>
