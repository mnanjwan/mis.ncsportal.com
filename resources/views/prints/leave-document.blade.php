<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Document - Print</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Times New Roman', serif !important;
            font-size: 10pt !important;
            line-height: 1.4 !important;
            color: #000 !important;
            background: #fff !important;
        }
        body {
            position: relative;
            padding: 10px;
        }
        .document-container {
            max-width: 210mm;
            margin: 0 auto;
            background: transparent;
            padding: 10mm;
            font-family: 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            position: relative;
            z-index: 1;
        }
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 28pt;
            font-weight: bold;
            color: #228B22; /* Forest Green color */
            opacity: 0.25;
            z-index: 0;
            pointer-events: none;
            white-space: nowrap;
            font-family: 'Times New Roman', serif;
            width: 80%;
            text-align: center;
        }
        @media print {
            .watermark {
                opacity: 0.20;
                font-size: 24pt;
                width: 70%;
            }
            .document-container {
                background: transparent;
            }
        }
        .restricted {
            text-align: center !important;
            font-weight: bold !important;
            font-size: 10pt !important;
            margin: 5px 0 !important;
        }
        .restricted-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
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
            font-size: 10pt;
            padding: 5px 0;
            background: white;
            z-index: 1000;
            display: none;
        }
        .header {
            text-align: center !important;
            margin-bottom: 8px !important;
        }
        .header h1 {
            font-size: 14pt !important;
            font-weight: bold !important;
            text-decoration: underline !important;
            margin: 3px 0 !important;
            text-align: center !important;
        }
        .header-info {
            margin: 8px 0;
        }
        .header-info p {
            margin: 2px 0;
            font-size: 9pt;
        }
        .leave-type-section {
            margin: 8px 0;
            font-size: 10pt;
        }
        .leave-type-section strong {
            background-color: #ffff00;
            padding: 1px 3px;
        }
        .officer-info {
            margin: 8px 0;
        }
        .officer-info p {
            margin: 3px 0;
        }
        .leave-details {
            margin: 8px 0;
        }
        .leave-details p {
            margin: 3px 0;
        }
        .instructions {
            margin: 10px 0;
        }
        .instructions ol {
            margin: 5px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 4px 0;
            font-size: 9.5pt;
        }
        .instructions .blank-line {
            border-bottom: 1px solid #000;
            min-height: 15px;
            margin: 3px 0;
            display: inline-block;
            min-width: 150px;
        }
        .signature-section {
            margin-top: 15px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 15px 0 3px 0;
        }
        .signature-section p {
            margin: 3px 0;
            font-size: 9.5pt;
        }
        @media print {
            @page {
                size: A4;
                margin-top: 20mm;
                margin-bottom: 20mm;
            }
            * {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding-top: 15mm;
                padding-bottom: 15mm;
                background: #fff;
                font-size: 10pt !important;
                line-height: 1.4 !important;
            }
            .document-container {
                margin: 0;
                padding: 10mm;
                max-width: 100%;
                background: transparent;
                font-size: 10pt;
                line-height: 1.4;
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
            .header {
                margin-bottom: 5px !important;
            }
            .header-info {
                margin: 5px 0;
            }
            .header-info p {
                margin: 1px 0;
            }
            .leave-type-section {
                margin: 5px 0;
            }
            .officer-info {
                margin: 5px 0;
            }
            .officer-info p {
                margin: 2px 0;
            }
            .leave-details {
                margin: 5px 0;
            }
            .leave-details p {
                margin: 2px 0;
            }
            .instructions {
                margin: 8px 0;
            }
            .instructions li {
                margin: 3px 0;
            }
            .signature-section {
                margin-top: 10px;
            }
            .signature-line {
                margin: 10px 0 2px 0;
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

    <!-- Watermark -->
    <div class="watermark">NCS Management Information System (MIS)</div>
    
    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>
    
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
                It is expected you will remain in the post until
                <div class="blank-line"></div>
            </li>
            <li>
                Please furnish your leave address.
            </li>
            <li>
                Date increment due
                <div class="blank-line"></div>
            </li>
            <li>
                Date confidential report to reach the secretary
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
        <p style="margin-top: 8px;"><strong>7.</strong> Your {{ now()->format('Y') }} annual leave has been taken into account.</p>
        <p style="margin-top: 3px;"><strong>8.</strong> This change is to take place within seven days.</p>
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
    </div>
</body>
</html>

