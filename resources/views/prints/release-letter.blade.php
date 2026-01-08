<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Release Letter - Print</title>
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
            color: #228B22;
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
        .main-header {
            text-align: center;
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
        .release-info {
            margin: 12px 0;
        }
        .release-info p {
            margin: 5px 0;
            font-size: 10pt;
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
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
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
            }
            .document-container {
                padding: 10mm;
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
        }
    </style>
</head>
<body>
    <div class="watermark">NCS Management Information System (MIS)</div>
    
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Document
        </button>
        <form action="{{ route('staff-officer.postings.mark-release-letter-printed', $posting->id) }}" method="POST" style="display: inline-block; margin-left: 10px;">
            @csrf
            <button type="submit" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 5px;">
                Mark as Printed
            </button>
        </form>
    </div>

    <div class="restricted-header" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; top: 0; left: 0; right: 0;">RESTRICTED</div>
    <div class="restricted-footer" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; bottom: 0; left: 0; right: 0;">RESTRICTED</div>

    <div class="document-container">
    <div class="restricted" style="text-align: center; font-weight: bold; font-size: 11pt; margin: 5px 0;">RESTRICTED</div>
    <div class="header-section">
        <div class="main-header">
            <h1>NIGERIA CUSTOMS SERVICE</h1>
            <h2>{{ strtoupper($fromCommand->name ?? 'COMMAND') }}</h2>
        </div>

        <div class="file-info">
            <table>
                <tr>
                    <td><strong>Date:</strong> {{ $printDate ? \Carbon\Carbon::parse($printDate)->format('d M Y') : now()->format('d M Y') }}</td>
                </tr>
                @if(isset($releaseLetter) && $releaseLetter->letter_number)
                <tr>
                    <td><strong>Release Letter No:</strong> {{ $releaseLetter->letter_number }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>{{ $orderType }}:</strong> {{ $orderNumber }}</td>
                </tr>
                @if(isset($orderDate))
                <tr>
                    <td><strong>{{ $orderType }} Date:</strong> {{ $orderDate }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div style="text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0;">
        RELEASE LETTER
    </div>

    <div class="officer-details">
        <table>
            <tr>
                <td><strong>Service No:</strong></td>
                <td>{{ $posting->officer->service_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{ strtoupper($posting->officer->initials ?? '') }} {{ strtoupper($posting->officer->surname ?? '') }}</td>
            </tr>
            <tr>
                <td><strong>Rank:</strong></td>
                <td>{{ strtoupper($posting->officer->substantive_rank ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    <div class="release-info">
        <p style="margin-top: 15px; text-align: justify;">
            This is to certify that <strong>{{ strtoupper($posting->officer->initials ?? '') }} {{ strtoupper($posting->officer->surname ?? '') }}</strong>, 
            Service Number <strong>{{ $posting->officer->service_number ?? 'N/A' }}</strong>, 
            Rank <strong>{{ strtoupper($posting->officer->substantive_rank ?? 'N/A') }}</strong>, 
            is hereby released from <strong>{{ strtoupper($fromCommand->name ?? 'N/A') }}</strong> 
            for posting to <strong>{{ strtoupper($toCommand->name ?? 'N/A') }}</strong> 
            in accordance with {{ $orderType }} <strong>{{ $orderNumber }}</strong> dated {{ $orderDate ?? ($posting->posting_date ? $posting->posting_date->format('d M Y') : now()->format('d M Y')) }}.
        </p>
        <p style="margin-top: 10px; text-align: justify;">
            The officer is to report to <strong>{{ strtoupper($toCommand->name ?? 'N/A') }}</strong> for acceptance and documentation.
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div style="margin-top: 5px;">
            @if(isset($printedBy) && $printedBy)
                <strong>{{ strtoupper($printedBy->name ?? 'Staff Officer') }}</strong><br>
            @else
                <strong>Staff Officer</strong><br>
            @endif
            <strong>{{ strtoupper($fromCommand->name ?? 'COMMAND') }}</strong><br>
            <strong>Date & Stamp:</strong> {{ $printDate ? \Carbon\Carbon::parse($printDate)->format('d/m/Y') : now()->format('d/m/Y') }}
        </div>
    </div>

    </div>
</body>
</html>

