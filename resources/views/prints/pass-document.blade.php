<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pass Document - Print</title>
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
            background: transparent;
            padding: 20mm;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.6;
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
            font-size: 12pt !important;
            margin: 10px 0 !important;
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
        .pass-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0;
        }
        .to-whom {
            text-align: center;
            font-size: 12pt;
            font-style: italic;
            margin: 15px 0;
        }
        .bearer-info {
            margin: 20px 0;
        }
        .bearer-info p {
            margin: 8px 0;
        }
        .pass-details {
            margin: 20px 0;
        }
        .pass-details p {
            margin: 8px 0;
        }
        .form-field {
            border-bottom: 1px solid #000;
            min-height: 20px;
            display: inline-block;
            min-width: 200px;
            padding: 0 5px;
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
            @page {
                margin-top: 25mm;
                margin-bottom: 25mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            * {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding-top: 10mm;
                padding-bottom: 10mm;
                padding-left: 5mm;
                padding-right: 5mm;
                background: #fff;
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
            <p><strong>File ref</strong> {{ $passApplication->id ? 'NCS/OPS/ZA/P. ' . str_pad($passApplication->officer->service_number ?? '00000', 5, '0', STR_PAD_LEFT) : 'NCS/OPS/ZA/P. XXXXX' }}</p>
            <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            <p><strong>No:</strong> {{ now()->format('m') }}/{{ now()->format('y') }}</p>
        </div>

        <div class="pass-title">PASS</div>
        <div class="to-whom">TO WHOM IT MAY CONCERN</div>

        <div class="bearer-info">
            <p>The bearer <strong><span class="form-field">{{ strtoupper($passApplication->officer->initials ?? '') }} {{ strtoupper($passApplication->officer->surname ?? '') }}</span></strong></p>
            <p><strong>Service No:</strong> <span class="form-field">{{ $passApplication->officer->service_number ?? 'N/A' }}</span></p>
            <p>is a Staff of this Area/station/unit... <span class="form-field">{{ strtoupper($command->name ?? 'COMMAND') }}</span></p>
        </div>

        <div class="pass-details">
            <p>He is granted <strong><span class="form-field">{{ $passApplication->number_of_days ?? 'XX' }}</span></strong> DAYS casual Leave with effect</p>
            <p>From <strong><span class="form-field">{{ $passApplication->start_date ? \Carbon\Carbon::parse($passApplication->start_date)->format('d M Y') : 'DD MMM YY' }}</span></strong> to travel to <span class="form-field">{{ strtoupper($passApplication->reason ?? 'DESTINATION') }}</span></p>
            <p>The pass expires on <strong><span class="form-field">{{ $passApplication->end_date ? \Carbon\Carbon::parse($passApplication->end_date)->format('d M Y') : 'DD MMM YY' }}</span></strong></p>
        </div>

        <div class="signature-section">
            <p><strong>Name and Signature of the Authorizing Officer</strong></p>
            <div class="signature-line"></div>
            <div style="margin-top: 5px;">
                @if($authorizingOfficer)
                    <strong>{{ strtoupper($authorizingOfficer->initials ?? '') }} {{ strtoupper($authorizingOfficer->surname ?? '') }}</strong><br>
                    <strong>Service No:</strong> {{ $authorizingOfficer->service_number ?? 'N/A' }}<br>
                    <strong>Rank/Designation:</strong> {{ strtoupper($authorizingOfficer->substantive_rank ?? 'N/A') }}<br>
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

