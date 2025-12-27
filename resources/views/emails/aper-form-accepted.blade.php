<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APER Form Accepted</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #d1fae5; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #10b981;">
        <h2 style="color: #059669; margin-top: 0;">✓ APER Form Accepted</h2>
    </div>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your APER form for <strong>{{ $form->year }}</strong> has been <strong>accepted</strong> and is now complete.</p>
    
    <div style="background-color: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <p style="margin: 0;"><strong>Form Status:</strong> Accepted</p>
        <p style="margin: 5px 0 0 0;">
            <strong>Accepted Date:</strong> {{ $form->accepted_at->format('F j, Y') }}<br>
            <strong>Year:</strong> {{ $form->year }}
        </p>
    </div>
    
    <p>Your Annual Performance Evaluation Report has been finalized and will be kept on record. You can view or download a PDF copy of your completed form at any time.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}" style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;">View Form</a>
        <a href="{{ $appUrl }}/officer/aper-forms/{{ $form->id }}/export" style="background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Download PDF</a>
    </div>
    
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated notification from the Personnel Information System Portal.<br>
        Please do not reply to this email.
    </p>
</body>
</html>

