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
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
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
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 30px;
        }
        .emblem {
            text-align: center;
            margin-bottom: 20px;
        }
        .emblem-symbol {
            font-size: 24pt;
            margin: 10px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            letter-spacing: 2px;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .reference-section {
            margin: 15px 0;
            text-align: center;
        }
        .reference-section p {
            margin: 5px 0;
        }
        .form-section {
            margin: 20px 0;
        }
        .form-section p {
            margin: 8px 0;
        }
        .form-field {
            border-bottom: 1px solid #000;
            min-height: 25px;
            display: inline-block;
            min-width: 200px;
            padding: 0 5px;
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
        .authorizing-section {
            margin-top: 40px;
        }
        .authorizing-section p {
            margin: 8px 0;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 30px 0 5px 0;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .container {
                border: none;
                padding: 0;
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

    <div class="container">
        {{-- NCS Logo/Emblem --}}
        <div class="ncs-logo-container" style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('logo.jpg') }}" alt="NCS Logo" style="max-width: 120px; height: auto; display: block; margin: 0 auto;">
        </div>

        <div class="header">
            <h1>NIGERIA CUSTOMS SERVICE</h1>
            <h2>CUSTOMS</h2>
        </div>

        <div class="reference-section">
            <p><strong>003472</strong></p>
            <p><strong>Date of Issue:</strong> <span class="form-field">{{ $passApplication->start_date ? \Carbon\Carbon::parse($passApplication->start_date)->format('d/m/Y') : now()->format('d/m/Y') }}</span></p>
            <p><strong>Area/Station/Unit:</strong> <span class="form-field">{{ strtoupper($command->name ?? 'COMMAND') }}</span></p>
        </div>

        <div class="form-section">
            <h2 style="text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0;">PASS</h2>
            <div class="to-whom">TO WHOM IT MAY CONCERN</div>
        </div>

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

        <div class="authorizing-section">
            <p><strong>Name and Signature of the Authorizing Officer</strong></p>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">
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
            </p>
        </div>
    </div>
</body>
</html>

