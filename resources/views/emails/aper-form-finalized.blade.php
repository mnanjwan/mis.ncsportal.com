<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>APER Form Finalized</title>
</head>
<body>
    <h1>APER Form Finalized</h1>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your APER form for {{ $form->year }} has been finalized by the Staff Officer.</p>
    
    <h2>Form Details</h2>
    <ul>
        <li><strong>Year:</strong> {{ $form->year }}</li>
        <li><strong>Finalized At:</strong> {{ $form->finalized_at ? $form->finalized_at->format('d/m/Y H:i') : 'N/A' }}</li>
        <li><strong>Finalized By:</strong> {{ $staffOfficer ? $staffOfficer->email : 'Staff Officer' }}</li>
    </ul>
    
    @if($rejectionReason)
    <h2>Finalization Reason</h2>
    <p>{{ $rejectionReason }}</p>
    @endif
    
    <p>The form is now accessible to HRD and marks will be awarded based on the assessment.</p>
    
    <p>You can view the form by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}">View APER Form</a></p>
    
    <p>Best regards,<br>System</p>
</body>
</html>

