<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retirement List - Print</title>
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
        .rank-col { width: 6%; }
        .initials-col { width: 6%; }
        .surname-col { width: 12%; }
        .cond-col { width: 6%; }
        .dob-col { width: 8%; }
        .dofa-col { width: 8%; }
        .dopr-col { width: 8%; }
        .ret-col { width: 8%; }
        .remarks-col { width: 27%; }
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
        <h1>FINAL STATUTORY RETIREMENT LIST FOR {{ $retirementYear ?? now()->addYear()->format('Y') }}</h1>
    </div>

    <div class="annex-info">
        <strong>ANNEX A TO NCS/ADM/EST/098/S.I DATED {{ now()->format('d M Y') }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th class="sn-col">S/N</th>
                <th class="svc-col">SVC-NO</th>
                <th class="rank-col">RANK</th>
                <th class="initials-col">INITIALS</th>
                <th class="surname-col">SURNAME</th>
                <th class="cond-col">COND FOR RET</th>
                <th class="dob-col">DOB</th>
                <th class="dofa-col">DOFA</th>
                <th class="dopr-col">DOPR</th>
                <th class="ret-col">RET</th>
                <th class="remarks-col">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retirements as $index => $retirement)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $retirement['service_number'] ?? 'N/A' }}</td>
                <td>{{ strtoupper($retirement['rank'] ?? 'N/A') }}</td>
                <td>{{ strtoupper($retirement['initials'] ?? '') }}</td>
                <td>{{ strtoupper($retirement['surname'] ?? '') }}</td>
                <td>{{ strtoupper($retirement['retirement_type'] ?? 'N/A') }}</td>
                <td>{{ $retirement['date_of_birth'] ? \Carbon\Carbon::parse($retirement['date_of_birth'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $retirement['date_of_first_appointment'] ? \Carbon\Carbon::parse($retirement['date_of_first_appointment'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $retirement['date_of_promotion'] ? \Carbon\Carbon::parse($retirement['date_of_promotion'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $retirement['retirement_date'] ? \Carbon\Carbon::parse($retirement['retirement_date'])->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $retirement['remarks'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

