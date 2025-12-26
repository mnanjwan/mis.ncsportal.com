<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $notification->title }}</title>
</head>
<body>
    <h2>{{ $notification->title }}</h2>
    
    @php
        $officer = $user->officer ?? null;
        $officerName = $officer ? trim(($officer->initials ?? '') . ' ' . ($officer->surname ?? '')) : ($user->name ?? 'Officer');
    @endphp
    
    <p>Dear {{ $officerName }},</p>
    
    <p>{{ $notification->message }}</p>
    
    @if($officer)
    <p><strong>Your Service Information:</strong></p>
    <p>
        <strong>Service Number:</strong> {{ $officer->service_number ?? 'Pending Assignment' }}<br>
        @if($officer->appointment_number)
        <strong>Appointment Number:</strong> {{ $officer->appointment_number }}<br>
        @endif
        <strong>Rank:</strong> {{ $officer->substantive_rank ?? 'N/A' }}<br>
        @if($officer->presentStation)
        <strong>Command/Station:</strong> {{ $officer->presentStation->name ?? 'N/A' }}<br>
        @endif
    </p>
    @endif
    
    <p><strong>Your Login Credentials:</strong></p>
    <p>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Default Password:</strong> {{ $tempPassword ?? 'N/A' }}
    </p>
    
    <p><strong>Important:</strong> For security purposes, please change your password immediately after logging in. You can update your password from your dashboard settings.</p>
    
    <p>You can now log in to your dashboard and access all available features including:</p>
    <ul>
        <li>View and update your profile</li>
        <li>Apply for leave and passes</li>
        <li>Raise emolument requests</li>
        <li>View your service records</li>
        <li>Manage your account settings</li>
    </ul>
    
    <p><a href="{{ config('app.url') }}/login">Log In to Dashboard</a></p>
    
    <p>Thank you for completing your onboarding process.</p>
    
    <p>Best regards,<br>
    {{ config('app.name', 'PIS Portal') }} Team</p>
    
    <hr>
    <p><small>This is an automated notification from {{ config('app.name', 'PIS Portal') }}.</small><br>
    <small><a href="{{ config('app.url') }}">Visit Portal</a></small></p>
</body>
</html>
