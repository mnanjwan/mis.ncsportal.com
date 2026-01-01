<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Ready for Review</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dbeafe; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #2563eb;">
        <h2 style="color: #1e40af; margin-top: 0;">📋 APER Form Ready for Review</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your APER form for <strong>{{ $form->year }}</strong> has been completed by the Countersigning Officer and is now <strong>ready for your review</strong>.</p>
    
    <div style="background-color: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Form Status:</strong> Ready for Review</p>
        <p style="margin: 5px 0 0 0;">
            <strong>Year:</strong> {{ $form->year }}<br>
            @if($form->countersigning_officer_completed_at)
            <strong>Countersigned Date:</strong> {{ $form->countersigning_officer_completed_at->format('F j, Y') }}
            @endif
        </p>
    </div>
    
    <p>Please review the assessment and either accept or reject the form. If you accept, the form will be finalized. If you reject, you must provide a reason and the form will be sent to the Staff Officer for review.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Review APER Form</a>
    </div>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

