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
        .header-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #000;
            border-radius: 4px;
            margin: 0 auto 10px;
        }
        .header-with-photo {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header-content {
            flex: 1;
            text-align: center;
        }
        .header-photo-container {
            flex-shrink: 0;
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

    <div class="header-with-photo">
        <div class="header-photo-container">
            @php
                $profilePictureUrl = $form->officer->getProfilePictureUrlFull();
            @endphp
            @if($profilePictureUrl)
                <img src="{{ $profilePictureUrl }}" 
                     alt="Officer Passport Photo" 
                     class="header-photo">
            @else
                <div class="header-photo" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-size: 10px; text-align: center;">
                    No Photo<br>Available
                </div>
            @endif
        </div>
        <div class="header-content">
            <h1>NIGERIA CUSTOMS SERVICE</h1>
            <h2>CONFIDENTIAL</h2>
            <h2>ANNUAL PERFORMANCE EVALUATION REPORT</h2>
            <p style="font-style: italic;">(For Officers on HAPASS 03 and above)</p>
            <p style="margin-top: 10px; font-size: 12px; font-weight: bold;">
                {{ $form->officer->initials }} {{ $form->officer->surname }} - {{ $form->officer->service_number }}
            </p>
        </div>
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
        <div class="section-title">DECLARATIONS</div>
        
        <!-- Officer Declaration -->
        <div style="margin-bottom: 20px;">
            <div class="field-label" style="font-weight: bold; font-size: 12px;">DECLARATION</div>
            <p style="font-style: italic; font-size: 10px; margin-bottom: 10px;">
                (Comments by the officer on whom the report is rendered)
            </p>
            <p style="font-size: 10px; margin-bottom: 10px;">
                I certify that I have seen the content of this Report and that the reporting officer has discussed them with me. 
                I have the following comments to make (if no comment, so hereunder):
            </p>
            <div class="field-value" style="min-height: 60px; border: 1px solid #ccc; padding: 10px;">
                {{ $form->officer_comments ?? 'No comments provided.' }}
            </div>
            @if($form->officer_signed_at)
                <div style="margin-top: 15px; display: flex; justify-content: space-between;">
                    <div>
                        <strong>Date:</strong> {{ $form->officer_signed_at->format('d/m/Y') }}
                    </div>
                    <div>
                        <strong>Signature:</strong> {{ $form->officer->initials }} {{ $form->officer->surname }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Reporting Officer Declaration -->
        <div style="margin-bottom: 20px; border-top: 1px solid #000; padding-top: 15px;">
            <div class="field-label" style="font-weight: bold; font-size: 12px;">DECLARATION BY REPORTING OFFICER</div>
            @if($form->reporting_officer_declaration)
                <div class="field-value" style="min-height: 60px; border: 1px solid #ccc; padding: 10px; white-space: pre-wrap;">
                    {{ $form->reporting_officer_declaration }}
                </div>
                @if($form->reporting_officer_signed_at)
                    <div style="margin-top: 15px; display: flex; justify-content: space-between;">
                        <div>
                            <strong>Date:</strong> {{ $form->reporting_officer_signed_at->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Signed By:</strong> {{ $form->reportingOfficerUser ? $form->reportingOfficerUser->email : ($form->reportingOfficer ? $form->reportingOfficer->email : 'N/A') }}
                        </div>
                    </div>
                @endif
            @else
                <p style="font-style: italic;">Not yet completed</p>
            @endif
        </div>

        <!-- Countersigning Officer Declaration -->
        <div style="margin-bottom: 20px; border-top: 1px solid #000; padding-top: 15px;">
            <div class="field-label" style="font-weight: bold; font-size: 12px;">DECLARATION BY COUNTERSIGNING OFFICER</div>
            @if($form->countersigning_officer_declaration)
                <div class="field-value" style="min-height: 60px; border: 1px solid #ccc; padding: 10px; white-space: pre-wrap;">
                    {{ $form->countersigning_officer_declaration }}
                </div>
                @if($form->countersigning_officer_signed_at)
                    <div style="margin-top: 15px; display: flex; justify-content: space-between;">
                        <div>
                            <strong>Date:</strong> {{ $form->countersigning_officer_signed_at->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Signed By:</strong> {{ $form->countersigningOfficerUser ? $form->countersigningOfficerUser->email : ($form->countersigningOfficer ? $form->countersigningOfficer->email : 'N/A') }}
                        </div>
                    </div>
                @endif
            @else
                <p style="font-style: italic;">Not yet completed</p>
            @endif
        </div>

        <!-- Head of Department Declaration -->
        <div style="margin-bottom: 20px; border-top: 1px solid #000; padding-top: 15px;">
            <div class="field-label" style="font-weight: bold; font-size: 12px;">DECLARATION BY HEAD OF DEPARTMENT</div>
            @if($form->head_of_department_declaration)
                <div class="field-value" style="min-height: 60px; border: 1px solid #ccc; padding: 10px; white-space: pre-wrap;">
                    {{ $form->head_of_department_declaration }}
                </div>
                @if($form->head_of_department_signed_at)
                    <div style="margin-top: 15px; display: flex; justify-content: space-between;">
                        <div>
                            <strong>Date:</strong> {{ $form->head_of_department_signed_at->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Signed By:</strong> {{ $form->headOfDepartment ? $form->headOfDepartment->email : 'N/A' }}
                        </div>
                    </div>
                @endif
            @else
                <p style="font-style: italic;">Not yet completed</p>
            @endif
        </div>
    </div>
</body>
</html>

