<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>
        @if($officerName)
            Dear {{ $officerName }},
        @else
            Dear Officer,
        @endif
    </p>

    <p>You have been invited to complete your onboarding for the NCS Employee Portal.</p>

    <p><strong>Your temporary login credentials are:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $email ?? 'Your registered email address' }}</li>
        <li><strong>Temporary Password:</strong> <code style="background-color: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace;">{{ $tempPassword }}</code></li>
    </ul>

    <p><strong style="color: #dc3545;">IMPORTANT:</strong> Please change your password immediately after logging in for security purposes.</p>

    <p>Please click the link below to access the onboarding form and complete your registration:</p>

    <p><a href="{{ $onboardingLink }}" style="color: #068b57; text-decoration: underline;">{{ $onboardingLink }}</a></p>

   <br>
    <p>If you have any questions or need assistance, please contact the HRD department.</p>

    <p>Best regards,<br>
    NCS Human Resources Department</p>

    <hr>

    <p><small>This is an automated message from the NCS Employee Portal.<br>
    Please do not reply to this email.</small></p>
</body>
</html>

