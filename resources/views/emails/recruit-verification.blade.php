<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Onboarding Verification Update</h2>
    
    <p>
        @if($recruitName)
            Dear {{ $recruitName }},
        @else
            Dear Recruit,
        @endif
    </p>
    
    @if($verificationStatus === 'verified')
    <p>Congratulations! Your onboarding documents have been verified and approved by the Establishment office.</p>
    
    <h3>Your Information:</h3>
    <p>
        <strong>Name:</strong> {{ $recruitName }}<br>
        <strong>Appointment Number:</strong> {{ $recruit->appointment_number ?? 'N/A' }}<br>
        <strong>Rank:</strong> {{ $recruit->substantive_rank ?? 'N/A' }}<br>
        <strong>Email:</strong> {{ $recruit->email ?? 'N/A' }}
    </p>
    
    <h3>What Happens Next:</h3>
    <p>After verification, you will be assigned to training and receive your service number.</p>
    
    <p>You will be notified via email once your training assignment and service number have been assigned.</p>
    
    @if($verificationNotes)
    <h3>Additional Notes:</h3>
    <p>{{ $verificationNotes }}</p>
    @endif
    
    @else
    <p>Your onboarding documents have been reviewed by the Establishment office.</p>
    
    <h3>Verification Status: Rejected</h3>
    
    @if($verificationNotes)
    <h3>Reason:</h3>
    <p>{{ $verificationNotes }}</p>
    @endif
    
    <h3>Next Steps:</h3>
    <p>Please contact the Establishment office to resolve any issues with your submitted documents or information.</p>
    
    <p>You may need to resubmit certain documents or correct information as indicated by the Establishment office.</p>
    @endif
    
    <p>If you have any questions, please contact the Establishment office.</p>
    
    <p>Best regards,<br>
    NCS Establishment Office</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal. Please do not reply to this email.</small></p>
</body>
</html>

