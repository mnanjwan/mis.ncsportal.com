<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Order - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.5;
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
        .header-section {
            margin-bottom: 20px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .stamp {
            border: 2px solid #000;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9pt;
            padding: 5px;
        }
        .main-header {
            text-align: center;
            flex: 1;
        }
        .main-header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .main-header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .file-info {
            margin: 15px 0;
        }
        .file-info table {
            width: 100%;
        }
        .file-info td {
            padding: 3px 0;
        }
        .officer-details {
            margin: 20px 0;
        }
        .officer-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .officer-details td {
            padding: 5px 0;
            vertical-align: top;
        }
        .posting-info {
            margin: 20px 0;
        }
        .posting-info p {
            margin: 8px 0;
        }
        .distribution {
            margin: 20px 0;
            font-size: 10pt;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 30px 0 5px auto;
        }
        .handwritten-note {
            margin: 15px 0;
            font-style: italic;
            color: #333;
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

    <div class="header-section">
        <div class="header-top">
            <div class="stamp">
                <div style="font-size: 8pt; font-weight: bold;">NIGERIA</div>
                <div style="font-size: 8pt; font-weight: bold;">CUSTOMS</div>
                <div style="font-size: 8pt; font-weight: bold;">SERVICE</div>
                <div style="font-size: 7pt; margin-top: 3px;">RECEIVED</div>
                <div style="font-size: 7pt;">{{ now()->format('d M Y') }}</div>
            </div>
            <div class="main-header">
                <h1>NIGERIA CUSTOMS SERVICE</h1>
                <h2>of the Deputy Comptroller-General, Human Resource Development</h2>
            </div>
            <div style="width: 100px;"></div>
        </div>

        <div class="file-info">
            <table>
                <tr>
                    <td><strong>File Reference:</strong> {{ $staffOrder->order_number ?? 'NCS/ADM/EST/P.XXXXX' }}</td>
                </tr>
                <tr>
                    <td><strong>Staff Order No.:</strong> {{ $staffOrder->order_number ?? 'XXXX/XXXX' }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong> {{ $staffOrder->effective_date ? \Carbon\Carbon::parse($staffOrder->effective_date)->format('d M Y') : now()->format('d M Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="officer-details">
        <table>
            <tr>
                <td><strong>Service No:</strong></td>
                <td>{{ $officer->service_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Initials:</strong></td>
                <td>{{ strtoupper($officer->initials ?? '') }}</td>
            </tr>
            <tr>
                <td><strong>Surname:</strong></td>
                <td>{{ strtoupper($officer->surname ?? '') }}</td>
            </tr>
            <tr>
                <td><strong>Rank:</strong></td>
                <td>{{ strtoupper($officer->substantive_rank ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    <div class="posting-info">
        <p><strong>You will report to:</strong> {{ strtoupper($toCommand->name ?? 'TO BE ASSIGNED') }}</p>
        <p><strong>And assume duty as:</strong> {{ strtoupper($officer->substantive_rank ?? 'N/A') }}</p>
        <p style="margin-top: 15px;"><strong>With immediate effect.</strong></p>
    </div>

    <div class="distribution">
        <p><strong>Through:</strong> {{ $fromCommand ? strtoupper($fromCommand->name) : 'N/A' }}</p>
        <p><strong>Copies:</strong> S/O FILE</p>
    </div>

    <div class="handwritten-note">
        <p>(Posting is not at Officer's request)</p>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div style="margin-top: 5px;">
            @if(isset($createdByOfficer) && $createdByOfficer)
                <strong>{{ strtoupper($createdByOfficer->initials ?? '') }} {{ strtoupper($createdByOfficer->surname ?? '') }}</strong><br>
                <strong>Service No:</strong> {{ $createdByOfficer->service_number ?? 'N/A' }}<br>
                <strong>Rank/Designation:</strong> {{ strtoupper($createdByOfficer->substantive_rank ?? 'N/A') }}<br>
            @else
                <strong>GA ITOTOH</strong><br>
                <strong>Service No:</strong> N/A<br>
                <strong>Rank/Designation:</strong> DEPUTY COMPTROLLER GENERAL (HRD)<br>
            @endif
            <strong>Date & Stamp:</strong> {{ now()->format('d/m/Y') }}<br>
            <em>For: COMPTROLLER GENERAL OF CUSTOMS</em>
        </div>
    </div>
</body>
</html>

