<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Report (Print)</title>
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
        body::after {
            content: "NCS Management Information System (MIS)";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 28pt;
            font-weight: bold;
            color: #228B22;
            opacity: 0.20;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
            font-family: 'Times New Roman', serif;
            width: 80%;
            text-align: center;
        }
        @media print {
            body::after {
                opacity: 0.18;
                font-size: 24pt;
                width: 70%;
            }
            .no-print {
                display: none !important;
            }
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
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .report-info {
            margin: 15px 0;
            font-size: 11pt;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 18px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 20px 0;
            font-size: 10pt;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #000;
            padding: 5px 8px;
        }
        tr {
            page-break-inside: avoid;
        }
        .total-line {
            margin-top: 10px;
            font-weight: bold;
            font-size: 11pt;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="document-container">
        <div class="header">
            <h1>Vehicle Report</h1>
        </div>
        <div class="report-info">
            <p>Scope: {{ $scopeLabel }}</p>
            <p>Generated: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        @php $showCommandColumn = auth()->user()->hasRole('CC T&L') || auth()->user()->hasRole('CGC'); @endphp
        @foreach($vehiclesGroupedByType as $typeKey => $vehiclesInType)
            @php
                $typeLabel = $vehicleTypes[$typeKey] ?? $typeKey;
                $sn = 0;
            @endphp
            <div class="section-title">{{ $typeLabel }} ({{ $vehiclesInType->count() }})</div>
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Reg No.</th>
                        <th>Make/Model</th>
                        <th>Chassis No.</th>
                        <th>Engine No.</th>
                        @if($showCommandColumn)
                            <th>Command</th>
                        @endif
                        <th>Officer (Allocated To)</th>
                        <th>Service No.</th>
                        <th>Service Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehiclesInType as $v)
                        @php $sn++; @endphp
                        <tr>
                            <td>{{ $sn }}</td>
                            <td>{{ $v->reg_no ?? '-' }}</td>
                            <td>{{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: '-' }}</td>
                            <td>{{ $v->chassis_number ?? '-' }}</td>
                            <td>{{ $v->engine_number ?? '-' }}</td>
                            @if($showCommandColumn)
                                <td>{{ $v->currentCommand?->name ?? '—' }}</td>
                            @endif
                            <td>
                                @if($v->currentOfficer)
                                    {{ $v->currentOfficer->full_name ?? ($v->currentOfficer->surname . ' ' . ($v->currentOfficer->first_name ?? '') . ' ' . ($v->currentOfficer->middle_name ?? '')) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($v->currentOfficer)
                                    {{ $v->currentOfficer->service_number ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $v->service_status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <p class="total-line">Total: {{ $vehiclesGroupedByType->flatten(1)->count() }} vehicle(s)</p>
    </div>
</body>
</html>
