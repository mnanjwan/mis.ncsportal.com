<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form {{ $form->year }} - {{ $form->officer->surname }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .field-label {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
        }
        .field-value {
            margin-bottom: 8px;
            padding-left: 10px;
        }
        .grade-badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            margin-right: 5px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 10px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
            Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <h1>NIGERIA CUSTOMS SERVICE</h1>
        <h2>CONFIDENTIAL</h2>
        <h2>ANNUAL PERFORMANCE EVALUATION REPORT</h2>
        <p style="font-style: italic;">(For Officers on HAPASS 03 and above)</p>
    </div>

    <!-- Part 1: Personal Records -->
    <div class="section">
        <div class="section-title">PART 1: PERSONAL RECORDS OF OFFICER</div>
        <table>
            <tr>
                <th style="width: 30%;">Service Number</th>
                <td>{{ $form->service_number ?? $form->officer->service_number }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $form->title }} {{ $form->surname }} {{ $form->forenames }}</td>
            </tr>
            <tr>
                <th>Department/Area</th>
                <td>{{ $form->department_area ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Cadre</th>
                <td>{{ $form->cadre ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Rank</th>
                <td>{{ $form->rank ?? $form->officer->substantive_rank }}</td>
            </tr>
            <tr>
                <th>HAPASS</th>
                <td>{{ $form->hapass ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date of First Appointment</th>
                <td>{{ $form->date_of_first_appointment ? $form->date_of_first_appointment->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date of Present Appointment</th>
                <td>{{ $form->date_of_present_appointment ? $form->date_of_present_appointment->format('d/m/Y') : 'N/A' }}</td>
            </tr>
        </table>

        @if($form->qualifications && count($form->qualifications) > 0)
            <div class="field-label">Qualifications:</div>
            <table>
                <tr>
                    <th>Qualification</th>
                    <th>Year Obtained</th>
                </tr>
                @foreach($form->qualifications as $qual)
                    @if(!empty($qual['qualification']))
                        <tr>
                            <td>{{ $qual['qualification'] }}</td>
                            <td>{{ $qual['year'] ?? 'N/A' }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        @endif
    </div>

    <!-- Overall Assessment -->
    @if($form->overall_assessment)
        <div class="section">
            <div class="section-title">Overall Assessment</div>
            <div class="field-label">Grade: <span class="grade-badge">{{ $form->overall_assessment }}</span></div>
            @php
                $overallLabels = ['A' => 'Outstanding', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Satisfactory', 'E' => 'Fair', 'F' => 'Poor'];
            @endphp
            <div class="field-value">{{ $overallLabels[$form->overall_assessment] ?? '' }}</div>
        </div>
    @endif

    @if($form->general_remarks)
        <div class="section">
            <div class="section-title">General Remarks</div>
            <div class="field-value" style="white-space: pre-wrap;">{{ $form->general_remarks }}</div>
        </div>
    @endif

    @if($form->promotability)
        <div class="section">
            <div class="section-title">Promotability</div>
            <div class="field-value">Grade: {{ $form->promotability }}</div>
        </div>
    @endif

    <!-- Declarations -->
    <div class="section footer">
        <div class="section-title">Declarations</div>
        @if($form->reporting_officer_declaration)
            <div class="field-label">Reporting Officer:</div>
            <div class="field-value">{{ $form->reporting_officer_declaration }}</div>
            @if($form->reportingOfficer)
                <div class="field-value"><strong>Signed:</strong> {{ $form->reportingOfficer->email }}</div>
            @endif
            @if($form->reporting_officer_signed_at)
                <div class="field-value"><strong>Date:</strong> {{ $form->reporting_officer_signed_at->format('d/m/Y') }}</div>
            @endif
        @endif

        @if($form->countersigning_officer_declaration)
            <div class="field-label" style="margin-top: 15px;">Countersigning Officer:</div>
            <div class="field-value">{{ $form->countersigning_officer_declaration }}</div>
            @if($form->countersigningOfficer)
                <div class="field-value"><strong>Signed:</strong> {{ $form->countersigningOfficer->email }}</div>
            @endif
            @if($form->countersigning_officer_signed_at)
                <div class="field-value"><strong>Date:</strong> {{ $form->countersigning_officer_signed_at->format('d/m/Y') }}</div>
            @endif
        @endif
    </div>
</body>
</html>

