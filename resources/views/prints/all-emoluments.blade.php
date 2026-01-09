<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Emoluments Report - Print</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
            position: relative;
        }
        /* Watermark */
        body::after {
            content: "NCS Management Information System (MIS)";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 24pt;
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
                font-size: 20pt;
                width: 70%;
                max-width: 70%;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .report-info {
            margin: 10px 0;
            text-align: right;
            font-size: 9pt;
        }
        .filters-info {
            margin: 10px 0;
            padding: 8px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        .filters-info p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }
        td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8pt;
        }
        .status-raised { background-color: #e3f2fd; }
        .status-assessed { background-color: #fff3e0; }
        .status-validated { background-color: #fff9c4; }
        .status-audited { background-color: #e8f5e9; }
        .status-processed { background-color: #c8e6c9; }
        .status-rejected { background-color: #ffcdd2; }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8pt;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Report
        </button>
    </div>

    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h2>ALL EMOLUMENTS REPORT</h2>
    </div>

    <div class="report-info">
        <p><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
        <p><strong>Total Records:</strong> {{ $emoluments->count() }}</p>
    </div>

    @if(!empty(array_filter($filters)))
    <div class="filters-info">
        <p><strong>Filters Applied:</strong></p>
        @if($filters['status'] && $filters['status'] !== 'ALL')
            <p><strong>Status:</strong> {{ $filters['status'] }}</p>
        @endif
        @if($filters['year'])
            <p><strong>Year:</strong> {{ $filters['year'] }}</p>
        @endif
        @if($filters['date_from'] || $filters['date_to'])
            <p><strong>Date Range:</strong> 
                {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Start' }} - 
                {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'End' }}
            </p>
        @endif
        @if($filters['zone'])
            <p><strong>Zone:</strong> {{ $filters['zone'] }}</p>
        @endif
        @if($filters['command'])
            <p><strong>Command:</strong> {{ $filters['command'] }}</p>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>S/N</th>
                <th>Service No</th>
                <th>Officer Name</th>
                <th>Rank</th>
                <th>Year</th>
                <th>Status</th>
                <th>Zone</th>
                <th>Command</th>
                <th>Submitted</th>
                <th>Bank Name</th>
                <th>Account No</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emoluments as $index => $emolument)
                <tr class="status-{{ strtolower($emolument->status) }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $emolument->officer->service_number ?? 'N/A' }}</td>
                    <td>{{ ($emolument->officer->initials ?? '') . ' ' . ($emolument->officer->surname ?? '') }}</td>
                    <td>{{ $emolument->officer->substantive_rank ?? 'N/A' }}</td>
                    <td>{{ $emolument->year }}</td>
                    <td><strong>{{ $emolument->status }}</strong></td>
                    <td>{{ $emolument->officer->presentStation->zone->name ?? 'N/A' }}</td>
                    <td>{{ $emolument->officer->presentStation->name ?? 'N/A' }}</td>
                    <td>{{ $emolument->submitted_at ? $emolument->submitted_at->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $emolument->bank_name ?? 'N/A' }}</td>
                    <td>{{ $emolument->bank_account_number ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px;">
                        No emoluments found matching the selected filters
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by NCS Employee Portal System</p>
    </div>
</body>
</html>

