<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment List - {{ $deployment->deployment_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; }
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
        }
        .command-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .command-header {
            background-color: #e0e0e0;
            padding: 10px;
            font-weight: bold;
            margin-bottom: 10px;
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
        <h1>MANNING DEPLOYMENT LIST</h1>
        @if(isset($manningRequest))
            <p><strong>Manning Request:</strong> #{{ $manningRequest->id }} - {{ $manningRequest->command->name ?? 'N/A' }}</p>
        @endif
        <p><strong>Deployment Number:</strong> {{ $deployment->deployment_number }}</p>
        <p><strong>Date:</strong> {{ $deployment->published_at ? $deployment->published_at->format('d/m/Y') : $deployment->created_at->format('d/m/Y') }}</p>
        <p><strong>Status:</strong> {{ $deployment->status }}</p>
        @if(isset($manningRequest))
            <p><strong>Note:</strong> This print shows only officers from Manning Request #{{ $manningRequest->id }}</p>
        @endif
    </div>

    @php
        // Group assignments by command
        $assignmentsByCommand = $assignments->groupBy('to_command_id');
    @endphp

    @foreach($assignmentsByCommand as $commandId => $commandAssignments)
        @php
            $command = $commandAssignments->first()->toCommand;
        @endphp
        <div class="command-section">
            <div class="command-header">
                Command: {{ $command->name ?? 'Unknown' }}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Service Number</th>
                        <th>Name</th>
                        <th>Rank</th>
                        <th>From Command</th>
                        <th>To Command</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commandAssignments as $index => $assignment)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $assignment->officer->service_number ?? 'N/A' }}</td>
                            <td>{{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}</td>
                            <td>{{ $assignment->officer->substantive_rank ?? 'N/A' }}</td>
                            <td>{{ $assignment->fromCommand->name ?? 'N/A' }}</td>
                            <td>{{ $assignment->toCommand->name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Overall Summary (sorted by rank) -->
    <div class="command-section" style="margin-top: 40px;">
        <div class="command-header">
            Overall Deployment Summary (Sorted by Rank)
        </div>
        <table>
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Service Number</th>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>From Command</th>
                    <th>To Command</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $index => $assignment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $assignment->officer->service_number ?? 'N/A' }}</td>
                        <td>{{ $assignment->officer->initials ?? '' }} {{ $assignment->officer->surname ?? '' }}</td>
                        <td>{{ $assignment->officer->substantive_rank ?? 'N/A' }}</td>
                        <td>{{ $assignment->fromCommand->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->toCommand->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Generated on: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Total Officers: {{ $assignments->count() }}</p>
    </div>
</body>
</html>

