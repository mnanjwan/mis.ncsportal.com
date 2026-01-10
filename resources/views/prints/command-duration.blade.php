<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Duration Report - Print</title>
    <style>
        @page {
            size: A4;
            margin: 25mm 15mm;
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
            @page {
                margin-top: 25mm;
                margin-bottom: 25mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            body {
                padding: 10mm 5mm;
            }
            body::after {
                opacity: 0.20;
                z-index: -1;
                font-size: 24pt;
                width: 70%;
                max-width: 70%;
            }
            table {
                margin-top: 15px;
                margin-bottom: 15px;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header h2 {
            font-size: 14pt;
            margin: 5px 0;
        }
        .criteria {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #000;
        }
        .criteria-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .criteria-item {
            margin: 3px 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #000;
            padding: 6px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10pt;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h2>COMMAND DURATION REPORT</h2>
    </div>

    <div class="criteria">
        <div class="criteria-title">Search Criteria:</div>
        <div class="criteria-item"><strong>Zone:</strong> {{ $zone->name ?? 'N/A' }}</div>
        <div class="criteria-item"><strong>Command:</strong> {{ $command->name ?? 'N/A' }}</div>
        @if($request->filled('rank'))
            <div class="criteria-item"><strong>Rank:</strong> {{ $request->rank }}</div>
        @endif
        @if($request->filled('sex') && $request->sex !== 'Any')
            <div class="criteria-item"><strong>Sex:</strong> {{ $request->sex }}</div>
        @endif
        @if($request->filled('duration_years'))
            <div class="criteria-item"><strong>Duration:</strong> 
                @if($request->duration_years == 10)
                    10+ Years
                @else
                    {{ $request->duration_years }} {{ $request->duration_years == 1 ? 'Year' : 'Years' }}
                @endif
            </div>
        @endif
        <div class="criteria-item"><strong>Generated:</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    @if($officers->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">S/N</th>
                    <th style="width: 12%;">Rank</th>
                    <th style="width: 12%;">Service Number</th>
                    <th style="width: 20%;">Full Name</th>
                    <th style="width: 15%;">Date Posted</th>
                    <th style="width: 15%;">Duration</th>
                    <th style="width: 11%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($officers as $index => $officer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $officer->substantive_rank }}</td>
                        <td>{{ $officer->service_number }}</td>
                        <td>{{ $officer->full_name }}</td>
                        <td>{{ $officer->date_posted_to_command ? $officer->date_posted_to_command->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $officer->duration_display }}</td>
                        <td>{{ $officer->current_status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Total Officers:</strong> {{ $officers->count() }}</p>
            <p>Report generated on {{ now()->format('d/m/Y') }} at {{ now()->format('H:i:s') }}</p>
        </div>
    @else
        <div style="text-align: center; padding: 40px;">
            <p><strong>No officers found matching the search criteria.</strong></p>
        </div>
    @endif

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14pt; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14pt; cursor: pointer; margin-left: 10px;">Close</button>
    </div>
</body>
</html>

