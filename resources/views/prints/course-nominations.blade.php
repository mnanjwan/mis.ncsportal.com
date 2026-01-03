<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Nominations - Print</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 9pt;
            line-height: 1.3;
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
            font-size: 11pt;
            margin: 5px 0;
        }
        .restricted-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
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
            font-size: 11pt;
            padding: 5px 0;
            background: white;
            z-index: 1000;
            display: none;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 3px 0;
        }
        .course-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            padding: 5px;
            background-color: #f0f0f0;
            border: 1px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
        }
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 8pt;
        }
        .sn-col { width: 5%; }
        .svcno-col { width: 12%; }
        .rank-col { width: 12%; }
        .name-col { width: 35%; }
        .rmk-col { width: 36%; }
        @media print {
            @page {
                margin-top: 20mm;
                margin-bottom: 20mm;
            }
            body {
                margin: 0;
                padding-top: 15mm;
                padding-bottom: 15mm;
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
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px; background: #f5f5f5; border-radius: 8px;">
        <form method="GET" action="{{ route('hrd.courses.print') }}" style="display: flex; gap: 15px; align-items: flex-end; justify-content: center; flex-wrap: wrap; margin-bottom: 15px;">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label for="start_date" style="font-weight: 600; font-size: 14px;">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate ?? '' }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label for="end_date" style="font-weight: 600; font-size: 14px;">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate ?? '' }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
            <button type="submit" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 4px; font-weight: 600;">
                Filter
            </button>
            <a href="{{ route('hrd.courses.print', ['tab' => $tab ?? 'all']) }}" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; text-decoration: none; font-weight: 600; display: inline-block;">
                Clear
            </a>
        </form>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px; font-weight: 600;">
            Print Document
        </button>
    </div>

    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>

    <div class="restricted">RESTRICTED</div>
    
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h1>LIST OF SUCCESSFUL CANDIDATES</h1>
        @php
            $statusText = '';
            if (isset($tab)) {
                if ($tab === 'in_progress') {
                    $statusText = ' (In Progress)';
                } elseif ($tab === 'completed') {
                    $statusText = ' (Completed)';
                }
            }
        @endphp
        @if($statusText)
            <p style="font-size: 10pt; margin-top: 5px; font-weight: bold;">
                {{ $statusText }}
            </p>
        @endif
        @if(isset($startDate) || isset($endDate))
            <p style="font-size: 10pt; margin-top: 5px;">
                @if(isset($startDate) && isset($endDate))
                    Date Range: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @elseif(isset($startDate))
                    From: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                @elseif(isset($endDate))
                    Until: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @endif
            </p>
        @endif
    </div>

    @foreach($printData as $courseData)
        <div class="course-title">
            {{ $courseData['course_name'] }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="sn-col">SNO</th>
                    <th class="svcno-col">SVCNO</th>
                    <th class="rank-col">RANK</th>
                    <th class="name-col">NAMES</th>
                    <th class="rmk-col">RMK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courseData['officers'] as $officer)
                    <tr>
                        <td>{{ $officer['serial_number'] }}</td>
                        <td>{{ $officer['service_number'] }}</td>
                        <td>{{ $officer['rank'] }}</td>
                        <td>{{ $officer['name'] }}</td>
                        <td>{{ $officer['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>

