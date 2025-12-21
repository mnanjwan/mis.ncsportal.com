<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
</head>
<body>
    <p>Dear {{ $user->officer && $user->officer->full_name ? $user->officer->full_name : 'Officer' }},</p>
    
    <p>You have requested to reset your password for your NCS Employee Portal account.</p>
    
    <p>Click the link below to reset your password:</p>
    
    <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
    
    <p>This link will expire in 60 minutes.</p>
    
    <p>If you did not request a password reset, please ignore this email or contact HRD if you have concerns.</p>
    
    <p>Best regards,<br>
    {{ config('app.name', 'NCS Employee Portal') }} Team</p>
    
    <p style="margin-top: 30px; font-size: 12px; color: #666;">
        This is an automated notification from {{ config('app.name', 'NCS Employee Portal') }}.
    </p>
</body>
</html>

