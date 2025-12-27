<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Submission Period Now Open</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #2563eb; margin-top: 0;">APER Form Submission Period Now Open</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>The Annual Performance Evaluation Report (APER) form submission period for <strong>{{ $timeline->year }}</strong> is now open.</p>
    
    <div style="background-color: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Submission Period:</strong></p>
        <p style="margin: 5px 0 0 0;">
            <strong>Start:</strong> {{ $timeline->start_date->format('l, F j, Y \a\t g:i A') }}<br>
            <strong>End:</strong> {{ $timeline->end_date->format('l, F j, Y \a\t g:i A') }}
        </p>
    </div>
    
    <p>Please log in to your portal and complete your APER form before the deadline. The form will be pre-filled with your information, but you may need to add additional details.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/officer/aper-forms/create" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Submit APER Form</a>
    </div>
    
    <p><strong>Important Notes:</strong></p>
    <ul>
        <li>Forms must be submitted before the deadline</li>
        <li>You can save your form as a draft and continue editing until you submit</li>
        <li>Once submitted, you cannot edit the form</li>
        <li>After submission, your Reporting Officer will review and assess your form</li>
    </ul>
    
    <p>If you have any questions or need assistance, please contact HRD.</p>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

