<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Document - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Times New Roman', serif !important;
            font-size: 11pt !important;
            line-height: 1.6 !important;
            color: #000 !important;
            background: #fff !important;
        }
        body {
            position: relative;
            padding: 20px;
        }
        .document-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
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
            text-align: center !important;
            font-weight: bold !important;
            font-size: 12pt !important;
            margin: 10px 0 !important;
        }
        .header {
            text-align: center !important;
            margin-bottom: 20px !important;
        }
        .header h1 {
            font-size: 16pt !important;
            font-weight: bold !important;
            text-decoration: underline !important;
            margin: 5px 0 !important;
            text-align: center !important;
        }
        .header-info {
            margin: 15px 0;
        }
        .header-info p {
            margin: 3px 0;
        }
        .leave-type-section {
            margin: 15px 0;
            font-size: 11pt;
        }
        .leave-type-section strong {
            background-color: #ffff00;
            padding: 2px 5px;
        }
        .officer-info {
            margin: 15px 0;
        }
        .officer-info p {
            margin: 5px 0;
        }
        .leave-details {
            margin: 15px 0;
        }
        .leave-details p {
            margin: 5px 0;
        }
        .instructions {
            margin: 20px 0;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .instructions .blank-line {
            border-bottom: 1px solid #000;
            min-height: 20px;
            margin: 5px 0;
        }
        .signature-section {
            margin-top: 30px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 30px 0 5px 0;
        }
        @media print {
            * {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }
            .document-container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
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

    <div class="document-container">
        <div class="restricted">RESTRICTED</div>
    
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
    </div>

    <div class="header-info">
        <p><strong>Headquarters, {{ strtoupper($command->name ?? 'COMMAND') }}</strong></p>
        <p><strong>File ref</strong> {{ $leaveApplication->id ? 'NCS/OPS/ZA/P. ' . str_pad($leaveApplication->officer->service_number ?? '00000', 5, '0', STR_PAD_LEFT) : 'NCS/OPS/ZA/P. XXXXX' }}</p>
        <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>No:</strong> {{ now()->format('m') }}/{{ now()->format('y') }}</p>
    </div>

    <div class="leave-type-section">
        <strong>SICK LEAVE/MATERNITY LEAVE / ANNUAL LEAVE</strong>
        @if($leaveApplication->leaveType)
            <span style="background-color: #ffff00; padding: 2px 5px; font-weight: bold;">
                {{ strtoupper($leaveApplication->leaveType->name) }}
            </span>
        @endif
    </div>

    <div class="officer-info">
        <p><strong>NAME:</strong> {{ strtoupper($leaveApplication->officer->initials ?? '') }} {{ strtoupper($leaveApplication->officer->surname ?? '') }}</p>
        <p><strong>SVC NO:</strong> {{ $leaveApplication->officer->service_number ?? 'N/A' }}</p>
        <p><strong>RANK:</strong> {{ strtoupper($leaveApplication->officer->substantive_rank ?? 'N/A') }}</p>
    </div>

    <div class="leave-details">
        <p><strong>LEAVE:</strong> you will proceed on <strong>{{ $leaveApplication->number_of_days ?? 'XX' }}</strong> Working days' vacation on <strong>{{ $leaveApplication->start_date ? \Carbon\Carbon::parse($leaveApplication->start_date)->format('d/m/Y') : 'DD/MM/YYYY' }}</strong></p>
        <p>The leave expires on <strong>{{ $leaveApplication->end_date ? \Carbon\Carbon::parse($leaveApplication->end_date)->format('d/m/Y') : 'DD/MM/YYYY' }}</strong> ... and you will resume duty on <strong>{{ $leaveApplication->end_date ? \Carbon\Carbon::parse($leaveApplication->end_date)->addDays(3)->format('d/m/Y') : 'DD/MM/YYYY' }}</strong></p>
    </div>

    <div class="instructions">
        <ol>
            <li>
                <strong>Transfer / First Posting:</strong> you will report to
                <div class="blank-line"></div>
                and assume Duty as
                <div class="blank-line"></div>
            </li>
            <li>
                <strong>Report on correct position of travelling files:</strong>
                <ul style="list-style-type: lower-alpha; margin-left: 20px;">
                    <li>Open:</li>
                    <li>Confidential</li>
                </ul>
            </li>
        </ol>
        <p style="margin-top: 15px;"><strong>7.</strong> Your {{ now()->format('Y') }} annual leave has been taken into account.</p>
        <p><strong>8.</strong> This change is to take place within seven days.</p>
    </div>

    <div class="signature-section">
        <p><strong>Name and Signature of the Authorizing Officer</strong></p>
        <div class="signature-line"></div>
        <div style="margin-top: 5px;">
            @if($staffOfficer)
                <strong>{{ strtoupper($staffOfficer->initials ?? '') }} {{ strtoupper($staffOfficer->surname ?? '') }}</strong><br>
                <strong>Service No:</strong> {{ $staffOfficer->service_number ?? 'N/A' }}<br>
                <strong>Rank/Designation:</strong> {{ strtoupper($staffOfficer->substantive_rank ?? 'N/A') }}<br>
            @elseif($areaController)
                <strong>{{ strtoupper($areaController->initials ?? '') }} {{ strtoupper($areaController->surname ?? '') }}</strong><br>
                <strong>Service No:</strong> {{ $areaController->service_number ?? 'N/A' }}<br>
                <strong>Rank/Designation:</strong> {{ strtoupper($areaController->substantive_rank ?? 'N/A') }}<br>
            @else
                <strong>N/A</strong><br>
                <strong>Service No:</strong> N/A<br>
                <strong>Rank/Designation:</strong> N/A<br>
            @endif
            <strong>Date & Stamp:</strong> {{ now()->format('d/m/Y') }}
        </div>
    </div>

        <div class="restricted" style="margin-top: 30px;">RESTRICTED</div>
    </div>
</body>
</html>

