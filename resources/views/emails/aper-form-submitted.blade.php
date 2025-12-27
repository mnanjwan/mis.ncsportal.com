<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Submitted Successfully</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #d1fae5; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #10b981;">
        <h2 style="color: #059669; margin-top: 0;">✓ APER Form Submitted Successfully</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your APER form for <strong>{{ $form->year }}</strong> has been submitted successfully on {{ $form->submitted_at->format('l, F j, Y \a\t g:i A') }}.</p>
    
    <div style="background-color: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Next Steps:</strong></p>
        <ul style="margin: 10px 0 0 20px;">
            <li>Your form is now awaiting Reporting Officer assignment</li>
            <li>HRD or Staff Officer will assign a Reporting Officer to review your form</li>
            <li>You will be notified once a Reporting Officer has been assigned</li>
            <li>After review, the form will be forwarded to the Countersigning Officer</li>
            <li>Finally, you will be asked to accept or reject the completed assessment</li>
        </ul>
    </div>
    
    <p>You can view your submitted form at any time through the portal.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Submitted Form</a>
    </div>
    
    <p><strong>Note:</strong> You cannot edit your form after submission. If you need to make changes, please contact HRD.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

