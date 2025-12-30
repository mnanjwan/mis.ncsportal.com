<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>APER Form Rejected - Requires Review</title>
</head>
<body>
    <h1>APER Form Rejected - Requires Review</h1>
    
    <p>Dear Staff Officer,</p>
    
    <p>An officer has rejected their APER form and it requires your review.</p>
    
    <h2>Officer Information</h2>
    <ul>
        <li><strong>Name:</strong> {{ $officer->initials }} {{ $officer->surname }}</li>
        <li><strong>Service Number:</strong> {{ $officer->service_number }}</li>
        <li><strong>Year:</strong> {{ $form->year }}</li>
    </ul>
    
    <h2>Rejection Reason</h2>
    <p>{{ $rejectionReason }}</p>
    
    <h2>Form Details</h2>
    <ul>
        <li><strong>Reporting Officer:</strong> {{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}</li>
        <li><strong>Countersigning Officer:</strong> {{ $form->countersigningOfficer ? $form->countersigningOfficer->email : 'Not Assigned' }}</li>
        <li><strong>Rejected At:</strong> {{ $form->rejected_at ? $form->rejected_at->format('d/m/Y H:i') : 'N/A' }}</li>
    </ul>
    
    <p>You can review this form and either reassign it to a different Reporting Officer/Countersigning Officer or finalize it (which will make it accessible to HRD and marks will be awarded).</p>
    
    <p>You can access the form by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/aper-forms/review/{{ $form->id }}">Review APER Form</a></p>
    
    <p>Best regards,<br>System</p>
</body>
</html>

