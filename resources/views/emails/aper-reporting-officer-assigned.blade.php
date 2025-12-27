<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Assigned for Review</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dbeafe; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #2563eb;">
        <h2 style="color: #1e40af; margin-top: 0;">APER Form Assigned for Review</h2>
    </div>
    
    <p>Dear {{ $reportingOfficer->name ?? $reportingOfficer->email }},</p>
    
    <p>You have been assigned as the <strong>Reporting Officer</strong> to review an APER form.</p>
    
    <div style="background-color: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Officer Details:</strong></p>
        <p style="margin: 5px 0 0 0;">
            <strong>Name:</strong> {{ $form->officer->initials }} {{ $form->officer->surname }}<br>
            <strong>Service Number:</strong> {{ $form->officer->service_number }}<br>
            <strong>Year:</strong> {{ $form->year }}<br>
            <strong>Submitted:</strong> {{ $form->submitted_at->format('F j, Y') }}
        </p>
    </div>
    
    <p>Please review the officer's APER form and complete your assessment sections. The form includes:</p>
    <ul>
        <li>Officer's self-assessment and performance details</li>
        <li>Your assessment of various performance aspects</li>
        <li>Overall performance evaluation</li>
        <li>Promotability assessment</li>
    </ul>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/reporting-officer/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Review APER Form</a>
    </div>
    
    <p><strong>Important:</strong> Please complete your review in a timely manner so the form can proceed to the Countersigning Officer.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

