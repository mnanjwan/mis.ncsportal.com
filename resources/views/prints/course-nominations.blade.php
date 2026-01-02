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
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            Print Document
        </button>
    </div>

    <div class="restricted-header">RESTRICTED</div>
    <div class="restricted-footer">RESTRICTED</div>

    <div class="restricted">RESTRICTED</div>
    
    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h1>LIST OF SUCCESSFUL CANDIDATES</h1>
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

