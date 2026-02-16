<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movement Order - Print</title>
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
            margin-bottom: 20px;
            text-align: center;
        }
        .main-header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .deployment-info {
            margin: 15px 0;
            font-size: 11pt;
        }
        .deployment-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .deployment-date {
            margin-top: 5px;
        }
        .command-section {
            page-break-before: always;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .command-section:first-child {
            page-break-before: auto;
        }
        .command-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 15px 0;
            padding-top: 10px;
            text-transform: uppercase;
            page-break-after: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
        th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #000;
            padding: 6px;
        }
        .sn-col { width: 6%; }
        .rank-col { width: 12%; }
        .svc-col { width: 12%; }
        .name-col { width: 23%; }
        .present-col { width: 23%; }
        .new-col { width: 24%; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 11pt;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
        }
        @media print {
            @page {
                margin-top: 25mm;
                margin-bottom: 25mm;
                margin-left: 15mm;
                margin-right: 15mm;
            }
            body {
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
            .command-section {
                margin-top: 20px;
                padding-top: 10px;
            }
            table {
                margin-top: 15px;
                margin-bottom: 15px;
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
    </div>

    <div class="restricted-header" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; top: 0; left: 0; right: 0;">RESTRICTED</div>
    <div class="restricted-footer" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 5px 0; background: white; z-index: 1000; display: none; position: fixed; bottom: 0; left: 0; right: 0;">RESTRICTED</div>

    <div class="document-container">
    <div class="restricted" style="text-align: center; font-weight: bold; font-size: 11pt; margin: 5px 0;">RESTRICTED</div>
        <div class="header-section">
            <div class="main-header">
                <h1>NIGERIA CUSTOMS SERVICE</h1>
            </div>
            <div class="deployment-info">
                <div class="deployment-number">MOVEMENT ORDER</div>
                <div class="deployment-date">
                    <strong>Date:</strong> {{ $deployment->published_at ? $deployment->published_at->format('d M Y') : ($deployment->created_at ? $deployment->created_at->format('d M Y') : now()->format('d M Y')) }}
                </div>
                @if(isset($manningRequest))
                    <div class="deployment-date" style="margin-top: 10px;">
                        <strong>Manning Request:</strong> #{{ $manningRequest->id }} - {{ $manningRequest->command->name ?? 'N/A' }}
                    </div>
                @endif
            </div>
        </div>

        @php
            // Group assignments by command and sort by rank
            $rankOrder = [
                'CGC' => 1, 'DCG' => 2, 'ACG' => 3, 'CC' => 4, 'DC' => 5,
                'AC' => 6, 'CSC' => 7, 'SC' => 8, 'DSC' => 9,
                'ASC I' => 10, 'ASC II' => 11, 'IC' => 12, 'AIC' => 13,
                'CA I' => 14, 'CA II' => 15, 'CA III' => 16,
            ];
            
            $assignmentsByCommand = $assignments->groupBy('to_command_id');
            $commandGroups = [];
            $serialNumber = 1;
            
            foreach ($assignmentsByCommand as $commandId => $commandAssignments) {
                $command = $commandAssignments->first()->toCommand;
                
                // Sort assignments by rank
                $sortedAssignments = $commandAssignments->sortBy(function($assignment) use ($rankOrder) {
                    $rank = $assignment->officer->display_rank ?? '';
                    return $rankOrder[$rank] ?? 999;
                });
                
                $items = [];
                foreach ($sortedAssignments as $assignment) {
                    $officer = $assignment->officer;
                    $items[] = [
                        'serial_number' => $serialNumber++,
                        'rank' => $officer->display_rank ?? 'N/A',
                        'name' => trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')),
                        'service_number' => $officer->service_number ?? 'N/A',
                        'previous_posting' => $assignment->fromCommand->name ?? 'N/A',
                        'new_posting' => $assignment->toCommand->name ?? 'N/A',
                    ];
                }
                
                $commandGroups[] = [
                    'command_name' => $command->name ?? 'Unknown',
                    'items' => $items,
                ];
            }
        @endphp

        @foreach($commandGroups as $index => $group)
        <div class="command-section">
            <div class="command-title">{{ strtoupper($group['command_name']) }}</div>
            
            <table>
                <thead>
                    <tr>
                        <th class="sn-col">SNo</th>
                        <th class="rank-col">RANK</th>
                        <th class="svc-col">SRV NO</th>
                        <th class="name-col">NAME</th>
                        <th class="present-col">PREVIOUS POSTING</th>
                        <th class="new-col">NEW POSTING</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $item)
                    <tr>
                        <td>{{ $item['serial_number'] }}</td>
                        <td>{{ strtoupper($item['rank']) }}</td>
                        <td>{{ $item['service_number'] ?? 'N/A' }}</td>
                        <td>{{ strtoupper($item['name']) }}</td>
                        <td>{{ strtoupper($item['previous_posting']) }}</td>
                        <td>{{ strtoupper($item['new_posting']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($index === count($commandGroups) - 1)
            <div class="footer">
                <div class="signature-line">
                    <div style="margin-bottom: 40px;">
                        <div style="font-weight: bold; margin-bottom: 5px;">DCG HRD</div>
                        <div style="font-size: 10pt;">Deputy Comptroller-General</div>
                        <div style="font-size: 10pt;">Human Resources Development</div>
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px;">
                            Signature
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endforeach

    </div>
</body>
</html>
