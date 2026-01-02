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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: 'Times New Roman', serif !important;
            font-size: 11pt !important;
            line-height: 1.5 !important;
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
            background: transparent;
            padding: 20mm;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.5;
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
        .header-section {
            margin-bottom: 12px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .stamp {
            border: 2px solid #000;
            border-radius: 50%;
            width: 90px;
            height: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8pt;
            padding: 4px;
        }
        .main-header {
            text-align: center;
            flex: 1;
        }
        .main-header h1 {
            font-size: 15pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .main-header h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .file-info {
            margin: 10px 0;
        }
        .file-info table {
            width: 100%;
        }
        .file-info td {
            padding: 2px 0;
            font-size: 10pt;
        }
        .officer-details {
            margin: 12px 0;
        }
        .officer-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .officer-details td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10pt;
        }
        .posting-info {
            margin: 12px 0;
        }
        .posting-info p {
            margin: 5px 0;
            font-size: 10pt;
        }
        .distribution {
            margin: 12px 0;
            font-size: 9pt;
        }
        .signature-section {
            margin-top: 30px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 20px 0 5px auto;
        }
        .handwritten-note {
            margin: 10px 0;
            font-style: italic;
            color: #333;
            font-size: 9pt;
        }
        @media print {
            @page {
                size: A4;
                margin-top: 20mm;
                margin-bottom: 20mm;
            }
            body {
                margin: 0;
                padding-top: 15mm;
                padding-bottom: 15mm;
            }
            .document-container {
                padding: 15mm;
            }
            .no-print {
                display: none;
            }
            .restricted-header,
            .restricted-footer {
                display: block !important;
            }
            .restricted:not(.restricted-header):not(.restricted-footer) {
                display: none;
            }
            .header-section {
                margin-bottom: 10px;
            }
            .officer-details {
                margin: 10px 0;
            }
            .posting-info {
                margin: 10px 0;
            }
            .signature-section {
                margin-top: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">NCS Management Information System (MIS)</div>

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Document
        </button>
    </div>

    <div class="restricted-header" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; top: 0; left: 0; right: 0;">RESTRICTED</div>
    <div class="restricted-footer" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; bottom: 0; left: 0; right: 0;">RESTRICTED</div>

    <div class="document-container">
    <div class="restricted" style="text-align: center; font-weight: bold; font-size: 11pt; margin: 5px 0;">RESTRICTED</div>
    <div class="header-section">
        <div class="header-top">
          
            <div class="main-header">
                <h1>NIGERIA CUSTOMS SERVICE</h1>
                <h2>Deputy Comptroller-General, Human Resource Development</h2>
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
                    <td><strong>Date:</strong> {{ $staffOrder->effective_date ? $staffOrder->effective_date->format('d M Y') : now()->format('d M Y') }}</td>
                </tr>
                @if($staffOrder->order_type)
                <tr>
                    <td><strong>Order Type:</strong> {{ strtoupper($staffOrder->order_type) }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Status:</strong> {{ strtoupper($staffOrder->status ?? 'DRAFT') }}</td>
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
        <p><strong>From Command:</strong> {{ $fromCommand ? strtoupper($fromCommand->name) : 'N/A' }}</p>
        <p><strong>To Command:</strong> {{ strtoupper($toCommand->name ?? 'TO BE ASSIGNED') }}</p>
        <p><strong>You will report to:</strong> {{ strtoupper($toCommand->name ?? 'TO BE ASSIGNED') }}</p>
        <p><strong>And assume duty as:</strong> {{ strtoupper($officer->substantive_rank ?? 'N/A') }}</p>
        <p style="margin-top: 15px;"><strong>With immediate effect.</strong></p>
    </div>

    @if($staffOrder->description)
    <div class="posting-info">
        <p><strong>Description:</strong></p>
        <p style="margin-left: 20px; text-align: justify;">{{ $staffOrder->description }}</p>
    </div>
    @endif

    <div class="distribution">
        <p><strong>Copies:</strong> S/O FILE</p>
        @if($staffOrder->is_altered)
        <p><strong>Note:</strong> This order has been altered.</p>
        @if($staffOrder->altered_at)
        <p><strong>Altered At:</strong> {{ $staffOrder->altered_at->format('d M Y H:i') }}</p>
        @endif
        @endif
    </div>

    <div class="handwritten-note">
        <p>(Posting is not at Officer's request)</p>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div style="margin-top: 5px;">
            @if(isset($hrdOfficer) && $hrdOfficer)
                <strong>{{ strtoupper($hrdOfficer->initials ?? '') }} {{ strtoupper($hrdOfficer->surname ?? '') }}</strong><br>
                <strong>Service No:</strong> {{ $hrdOfficer->service_number ?? 'N/A' }}<br>
                <strong>Rank/Designation:</strong> {{ strtoupper($hrdOfficer->substantive_rank ?? 'N/A') }}<br>
            @else
                <strong>N/A</strong><br>
                <strong>Service No:</strong> N/A<br>
                <strong>Rank/Designation:</strong> N/A<br>
            @endif
            <strong>Date & Stamp:</strong> {{ now()->format('d/m/Y') }}<br>
            <em>For: COMPTROLLER GENERAL OF CUSTOMS</em>
        </div>
    </div>

    </div>
</body>
</html>

