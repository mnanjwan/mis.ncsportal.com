<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Staff Order - Print</title>
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
        }
        .restricted {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin: 5px 0;
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
            text-align: center;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 3px 0;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .file-ref {
            margin: 8px 0;
            font-size: 9pt;
        }
        .order-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }
        .order-details {
            margin: 10px 0;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details td {
            padding: 4px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .order-details td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .remark {
            margin: 10px 0;
            font-style: italic;
            font-size: 9.5pt;
        }
        .signature-section {
            margin-top: 15px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 15px auto 3px auto;
        }
        .signature-section p {
            margin: 3px 0;
            font-size: 9.5pt;
        }
        @media print {
            @page {
                size: A4;
                margin-top: 25mm;
                margin-bottom: 25mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            body {
                margin: 0;
                padding-top: 10mm;
                padding-bottom: 10mm;
                padding-left: 5mm;
                padding-right: 5mm;
                font-size: 10pt !important;
                line-height: 1.4 !important;
            }
            .document-container {
                margin: 0;
                padding: 10mm;
                max-width: 100%;
                background: transparent;
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
            .file-ref {
                margin: 5px 0;
            }
            .order-title {
                margin: 8px 0;
            }
            .order-details {
                margin: 8px 0;
            }
            .remark {
                margin: 8px 0;
            }
            .signature-section {
                margin-top: 10px;
            }
            .signature-line {
                margin: 10px auto 2px auto;
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
            <h2>{{ strtoupper($command->name ?? 'COMMAND') }}</h2>
        </div>

        <div class="file-ref">
            <strong>File Reg. No:</strong> {{ $internalStaffOrder->order_number ?? 'NCS/CMD/P.XXXXX' }}
        </div>

        <div class="order-title">INTERNAL STAFF ORDER</div>

        <div class="order-details">
            <table>
                <tr>
                    <td>STAFF ORDER DATE:</td>
                    <td>{{ $internalStaffOrder->order_date ? \Carbon\Carbon::parse($internalStaffOrder->order_date)->format('d M Y') : now()->format('d M Y') }}</td>
                </tr>
                @if($officer)
                    <tr>
                        <td>SERVICE NO:</td>
                        <td>{{ $officer->service_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>RANK:</td>
                        <td>{{ strtoupper($officer->substantive_rank ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td>NAME:</td>
                        <td>{{ strtoupper($officer->initials ?? '') }} {{ strtoupper($officer->surname ?? '') }}</td>
                    </tr>
                @endif
                @if($internalStaffOrder->current_unit)
                    <tr>
                        <td>CURRENT UNIT:</td>
                        <td>{{ strtoupper($internalStaffOrder->current_unit) }}</td>
                    </tr>
                @endif
                @if($internalStaffOrder->current_role)
                    <tr>
                        <td>CURRENT ROLE:</td>
                        <td>{{ strtoupper($internalStaffOrder->current_role) }}</td>
                    </tr>
                @endif
                @if($internalStaffOrder->target_unit)
                    <tr>
                        <td>NEW POSTING:</td>
                        <td>{{ strtoupper($internalStaffOrder->target_unit) }}</td>
                    </tr>
                @endif
                @if($internalStaffOrder->target_role)
                    <tr>
                        <td>NEW ROLE:</td>
                        <td>{{ strtoupper($internalStaffOrder->target_role) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="remark">
            <strong>Remark:</strong> This posting takes immediate effect, and the Officer is to remain in that post until further notice.
        </div>

        <div class="signature-section">
            <div class="signature-line"></div>
            <div style="margin-top: 5px;">
                @if($staffOfficer)
                    <strong>{{ strtoupper($staffOfficer->initials ?? '') }} {{ strtoupper($staffOfficer->surname ?? '') }}</strong><br>
                    <strong>Service No:</strong> {{ $staffOfficer->service_number ?? 'N/A' }}<br>
                    <strong>Rank/Designation:</strong> {{ strtoupper($staffOfficer->substantive_rank ?? 'N/A') }}<br>
                @else
                    <strong>N/A</strong><br>
                    <strong>Service No:</strong> N/A<br>
                    <strong>Rank/Designation:</strong> N/A<br>
                @endif
                <strong>STAFF OFFICER GENERAL</strong><br>
                <strong>Date & Stamp:</strong> {{ now()->format('d/m/Y') }}<br>
                <em>For: Comptroller, {{ strtoupper($command->name ?? 'COMMAND') }}</em>
            </div>
        </div>

    </div>
</body>
</html>
