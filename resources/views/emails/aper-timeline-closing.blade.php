<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Submission Deadline Approaching</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fef3c7; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f59e0b;">
        <h2 style="color: #d97706; margin-top: 0;">⚠️ APER Form Submission Deadline Approaching</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>This is a reminder that the APER form submission period for <strong>{{ $timeline->year }}</strong> is ending soon.</p>
    
    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;">
        <p style="margin: 0;"><strong>Deadline:</strong></p>
        <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold; color: #d97706;">
            {{ $endDate->format('l, F j, Y \a\t g:i A') }}<br>
            <span style="font-size: 14px;">({{ $daysRemaining }} day(s) remaining)</span>
        </p>
    </div>
    
    @if($hasDraft)
        <p>You currently have a <strong>draft</strong> APER form. Please complete and submit it before the deadline.</p>
    @else
        <p>You have not yet submitted your APER form for this year. Please complete and submit it before the deadline.</p>
    @endif
    
    <div style="text-align: center; margin: 30px 0;">
        @if($hasDraft)
            <a href="{{ $appUrl }}/officer/aper-forms/{{ $formId }}/edit" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;">Complete Draft Form</a>
        @else
            <a href="{{ $appUrl }}/officer/aper-forms/create" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Submit APER Form</a>
        @endif
    </div>
    
    <p><strong>Important:</strong> Forms cannot be edited or submitted after the deadline. Please ensure you submit your form on time.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

