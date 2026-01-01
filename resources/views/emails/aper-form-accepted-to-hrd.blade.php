<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Accepted - Ready for Grading</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dcfce7; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #10b981;">
        <h2 style="color: #059669; margin-top: 0;">APER Form Accepted - Ready for Grading</h2>
    </div>
    
    <p>Dear HRD Officer,</p>
    
    <p>An APER form has been <strong>accepted</strong> by an officer and is now ready for your review and grading.</p>
    
    <h2 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">Officer Information</h2>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 10px;"><strong>Name:</strong> {{ $officer->initials }} {{ $officer->surname }}</li>
        <li style="margin-bottom: 10px;"><strong>Service Number:</strong> {{ $officer->service_number }}</li>
        <li style="margin-bottom: 10px;"><strong>Year:</strong> {{ $form->year }}</li>
        <li style="margin-bottom: 10px;"><strong>Rank:</strong> {{ $officer->rank ?? $officer->substantive_rank ?? 'N/A' }}</li>
    </ul>
    
    <h2 style="color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-top: 30px;">Form Details</h2>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 10px;"><strong>Reporting Officer:</strong> {{ $form->reportingOfficer ? $form->reportingOfficer->email : 'Not Assigned' }}</li>
        <li style="margin-bottom: 10px;"><strong>Countersigning Officer:</strong> {{ $form->countersigningOfficer ? $form->countersigningOfficer->email : 'Not Assigned' }}</li>
        <li style="margin-bottom: 10px;"><strong>Accepted At:</strong> {{ $form->accepted_at ? $form->accepted_at->format('d/m/Y H:i') : 'N/A' }}</li>
        <li style="margin-bottom: 10px;"><strong>Status:</strong> Accepted</li>
    </ul>
    
    <div style="background-color: #f0f9ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Action Required:</strong> Please review and grade this APER form. You can access it through the HRD dashboard.</p>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/hrd/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Review & Grade APER Form</a>
    </div>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

