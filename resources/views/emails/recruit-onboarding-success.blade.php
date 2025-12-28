<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Onboarding Completed Successfully</h2>
    
    <p>
        @if($recruitName)
            Dear {{ $recruitName }},
        @else
            Dear Recruit,
        @endif
    </p>
    
    <p>Congratulations! You have successfully completed your onboarding process.</p>
    
    <h3>Your Information:</h3>
    <p>
        <strong>Name:</strong> {{ $recruitName }}<br>
        <strong>Appointment Number:</strong> {{ $recruit->appointment_number ?? 'N/A' }}<br>
        <strong>Rank:</strong> {{ $recruit->substantive_rank ?? 'N/A' }}<br>
        <strong>Email:</strong> {{ $recruit->email ?? 'N/A' }}
    </p>
    
    <h3>What Happens Next:</h3>
    <p>The Establishment office will now review your submitted documents and information.</p>
    
    <p>You will receive an email notification once your documents have been verified.</p>
    
    <p>After verification, you will be assigned to training and receive your service number.</p>
    
    <h3>Important:</h3>
    <p>Please keep your email address active. You will receive further instructions and updates via email.</p>
    
    <p>If you have any questions, please contact the Establishment office.</p>
    
    <p>Best regards,<br>
    NCS Establishment Office</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal. Please do not reply to this email.</small></p>
</body>
</html>

