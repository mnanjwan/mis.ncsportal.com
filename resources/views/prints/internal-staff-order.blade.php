<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Staff Order - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
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
            font-size: 14pt;
            margin: 10px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .file-ref {
            margin: 15px 0;
        }
        .order-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 20px 0;
        }
        .order-details {
            margin: 20px 0;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details td {
            padding: 8px;
            vertical-align: top;
        }
        .order-details td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .remark {
            margin: 20px 0;
            font-style: italic;
        }
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 40px auto 5px auto;
        }
        .stamp-area {
            margin-top: 20px;
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
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
            <tr>
                <td>NEW POSTING:</td>
                <td>{{ strtoupper($newPosting ?? 'TO BE ASSIGNED') }}</td>
            </tr>
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
                <strong>RT EYA</strong><br>
                <strong>Service No:</strong> 42499<br>
                <strong>Rank/Designation:</strong> AC<br>
            @endif
            <strong>STAFF OFFICER GENERAL</strong><br>
            <strong>Date & Stamp:</strong> {{ now()->format('d/m/Y') }}<br>
            <em>For: Comptroller, {{ strtoupper($command->name ?? 'COMMAND') }}</em>
        </div>
    </div>

    <div class="restricted" style="margin-top: 40px;">RESTRICTED</div>
</body>
</html>

