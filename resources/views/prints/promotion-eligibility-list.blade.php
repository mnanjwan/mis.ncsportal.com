<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Eligibility List - Print</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 9pt;
            line-height: 1.3;
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
            font-size: 28pt;
            font-weight: bold;
            color: #228B22; /* Forest Green color */
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
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .annex-info {
            text-align: right;
            font-size: 9pt;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 8pt;
        }
        .sn-col { width: 3%; }
        .svc-col { width: 8%; }
        .rank-col { width: 7%; }
        .initials-col { width: 5%; }
        .name-col { width: 10%; }
        .unit-col { width: 9%; }
        .state-col { width: 7%; }
        .dob-col { width: 9%; }
        .dofa-col { width: 9%; }
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
            Print Document
        </button>
    </div>

    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>

    <div class="restricted">RESTRICTED</div>
    
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h1>PROMOTION ELIGIBILITY LIST</h1>
        <h1>YEAR {{ $list->year ?? 'N/A' }}</h1>
    </div>

    <div class="annex-info">
        <div>Generated: {{ $list->created_at->format('d/m/Y') }}</div>
        @if($list->generatedBy)
            <div>By: {{ $list->generatedBy->email ?? 'N/A' }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="sn-col">S/N</th>
                <th class="rank-col">Rank</th>
                <th class="svc-col">Service Number</th>
                <th class="initials-col">Initial</th>
                <th class="name-col">Name</th>
                <th class="unit-col">Unit</th>
                <th class="state-col">State</th>
                <th class="dob-col">Date of Birth (DOB)</th>
                <th class="dofa-col">Date of First Appointment (DOFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['serial_number'] }}</td>
                    <td>{{ $item['rank'] }}</td>
                    <td style="font-family: monospace;">{{ $item['service_number'] ?? 'N/A' }}</td>
                    <td>{{ $item['initials'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['unit'] ?? '' }}</td>
                    <td>{{ $item['state'] }}</td>
                    <td>{{ $item['date_of_birth'] ? \Carbon\Carbon::parse($item['date_of_birth'])->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $item['date_of_first_appointment'] ? \Carbon\Carbon::parse($item['date_of_first_appointment'])->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

