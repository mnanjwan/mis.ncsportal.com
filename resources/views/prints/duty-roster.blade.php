<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duty Roster - Print</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
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
        .restricted {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 10px 0;
        }
        .restricted-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
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
            font-size: 12pt;
            padding: 5px 0;
            background: white;
            z-index: 1000;
            display: none;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 12pt;
            margin: 5px 0;
        }
        .date-info {
            text-align: right;
            font-size: 11pt;
            margin: 10px 0;
        }
        .roster-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0;
        }
        .page-number {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 10px;
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
        .sn-col { width: 5%; }
        .svc-col { width: 12%; }
        .rank-col { width: 10%; }
        .name-col { width: 28%; }
        .unit-col { width: 45%; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
            font-size: 11pt;
            page-break-inside: avoid;
            min-height: 60px;
        }
        .footer-line {
            margin: 8px 0;
            padding: 5px 0;
            line-height: 1.6;
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
            Print Document
        </button>
    </div>

    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>

    <div class="restricted">RESTRICTED</div>
    
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        @if($roster->command)
            <h2>{{ strtoupper($roster->command->name) }}</h2>
        @endif
    </div>

    <div class="date-info">
        <strong>DATE:</strong> {{ $deploymentDate }}
    </div>

    <div class="roster-title">
        @if($roster->unit)
            {{ strtoupper($roster->unit) }} - 
        @endif
        DUTY ROSTER
    </div>

    <table>
        <thead>
            <tr>
                <th class="sn-col">S/N</th>
                <th class="rank-col">RANK</th>
                <th class="svc-col">SVC. NO.</th>
                <th class="name-col">NAME</th>
                <th class="unit-col">UNIT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deployments as $index => $deployment)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ strtoupper($deployment['rank']) }}</td>
                <td>{{ $deployment['service_number'] }}</td>
                <td>{{ strtoupper($deployment['name']) }}{{ $deployment['role'] ? ' ' . $deployment['role'] : '' }}</td>
                <td>{{ strtoupper($deployment['unit']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-line">
            <strong>Prepared by:</strong> Staff Officer 
            @if($staffOfficer)
                ({{ strtoupper($staffOfficer->initials ?? '') }} {{ strtoupper($staffOfficer->surname ?? '') }})
            @else
                (N/A)
            @endif
        </div>
        <div class="footer-line">
            <strong>Approved by:</strong> 
            @if($approver)
                @if($approverRole === 'DC Admin')
                    Comptroller ({{ strtoupper($approver->initials ?? '') }} {{ strtoupper($approver->surname ?? '') }} - DC Admin)
                @else
                    Comptroller ({{ strtoupper($approver->initials ?? '') }} {{ strtoupper($approver->surname ?? '') }})
                @endif
            @else
                N/A
            @endif
        </div>
    </div>
</body>
</html>

