<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fee2e2; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
        <h2 style="color: #dc2626; margin-top: 0;">APER Form Rejected</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your APER form for <strong>{{ $form->year }}</strong> has been <strong>rejected</strong>.</p>
    
    <div style="background-color: #fef2f2; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0;">
        <p style="margin: 0;"><strong>Rejection Reason:</strong></p>
        <p style="margin: 5px 0 0 0; color: #991b1b;">
            {{ $form->rejection_reason }}
        </p>
    </div>
    
    <p>The form has been sent back to HRD/Staff Officer for reassignment to a Reporting Officer. You will be notified once a new Reporting Officer has been assigned.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">View Form</a>
    </div>
    
    <p>If you have any questions about the rejection, please contact HRD.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

