<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h3>Your Service Information:</h3>
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
    
    <h3>Your Login Credentials:</h3>
    <p>
        <strong>Email:</strong> {{ $user->email }}<br>
        <strong>Default Password:</strong> {{ $tempPassword ?? 'N/A' }}
    </p>
    
    <div style="background-color: #fef3c7; border: 2px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <p style="margin: 0; font-weight: bold; color: #92400e; font-size: 16px;">⚠️ MANDATORY PASSWORD CHANGE REQUIRED</p>
        <p style="margin: 10px 0 0 0; color: #78350f;">
            <strong>You MUST change your default password before you can access any features of the system.</strong>
        </p>
        <p style="margin: 10px 0 0 0; color: #78350f;">
            After logging in with your default password, you will see a password change modal on your dashboard. 
            <strong>You will not be able to access any other pages or features until you change your password.</strong>
        </p>
        <p style="margin: 10px 0 0 0; color: #78350f;">
            Please change your password immediately upon first login for security purposes.
        </p>
    </div>
    
    <p><strong>Steps to Access the System:</strong></p>
    <ol>
        <li>Log in using your email and the default password provided above</li>
        <li>You will be redirected to your dashboard where a password change modal will appear</li>
        <li>Change your password using the modal (you cannot close it without changing the password)</li>
        <li>After changing your password, you will receive a confirmation email</li>
        <li>You can then access all dashboard features normally</li>
    </ol>
    
    <p><a href="{{ config('app.url') }}/login" style="display: inline-block; padding: 10px 20px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Log In to Dashboard</a></p>
    
    <p>Thank you for completing your onboarding process.</p>
    
    <p>Best regards,<br>
    {{ config('app.name', 'PIS Portal') }} Team</p>
    
    <hr>
    <p><small>This is an automated notification from {{ config('app.name', 'PIS Portal') }}.</small><br>
    <small><a href="{{ config('app.url') }}">Visit Portal</a></small></p>
</body>
</html>
