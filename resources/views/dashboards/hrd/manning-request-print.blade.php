<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manning Request - #{{ $request->id }}</title>
    <style>
        @media print {
            @page {
                margin-top: 25mm;
                margin-bottom: 25mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            body { 
                margin: 0;
                padding: 10mm 5mm;
            }
            .no-print { display: none; }
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
            table {
                margin-top: 15px;
                margin-bottom: 15px;
            }
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            font-size: 14px;
        }
        .info-item strong {
            display: inline-block;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .summary-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .summary-item {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
        }
        .summary-item .label {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">
            Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer; border-radius: 4px; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>MANNING REQUEST</h1>
        <p><strong>Request Number:</strong> #{{ $request->id }}</p>
        <p><strong>Date:</strong> {{ $request->created_at ? $request->created_at->format('d/m/Y') : 'N/A' }}</p>
        <p><strong>Status:</strong> {{ $request->status }}</p>
    </div>

    @if(isset($postedOfficers) && count($postedOfficers) > 0)
        <div style="text-align: center; font-weight: bold; font-size: 16pt; margin: 30px 0 20px 0; text-transform: uppercase;">
            {{ strtoupper($request->command->name ?? 'N/A') }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>RANK</th>
                    <th>SRV NO</th>
                    <th>INITIALS</th>
                    <th>SURNAME</th>
                    <th>CURRENT POSTING</th>
                    <th>NEW POSTING</th>
                </tr>
            </thead>
            <tbody>
                @foreach($postedOfficers as $officer)
                    <tr>
                        <td>{{ $officer['serial_number'] }}</td>
                        <td>{{ strtoupper($officer['rank']) }}</td>
                        <td>{{ $officer['service_number'] }}</td>
                        <td>{{ strtoupper($officer['initials']) }}</td>
                        <td>{{ strtoupper($officer['surname']) }}</td>
                        <td>{{ strtoupper($officer['current_posting']) }}</td>
                        <td>{{ strtoupper($officer['new_posting']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="info-section">
            <p style="text-align: center; margin: 40px 0; font-size: 14px;">No officers have been posted for this request yet.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        @if(isset($postedOfficers) && count($postedOfficers) > 0)
        <p>Total Officers: {{ count($postedOfficers) }}</p>
        @endif
    </div>
</body>
</html>

