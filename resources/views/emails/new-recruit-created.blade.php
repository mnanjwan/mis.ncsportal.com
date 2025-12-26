<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to NCS - Your Recruit Profile Has Been Created</title>
</head>
<body>
    <h2>Welcome to Nigeria Customs Service</h2>
    
    <p>Dear {{ trim(($recruit->initials ?? '') . ' ' . ($recruit->surname ?? '')) }},</p>
    
    <p>Your recruit profile has been successfully created in the NCS Employee Portal system.</p>
    
    <p><strong>Your Profile Information:</strong></p>
    <p>
        <strong>Appointment Number:</strong> {{ $recruit->appointment_number ?? 'Pending' }}<br>
        <strong>Rank:</strong> {{ $recruit->substantive_rank ?? 'N/A' }}<br>
        <strong>Grade Level:</strong> {{ $recruit->salary_grade_level ?? 'N/A' }}<br>
        @if($recruit->presentStation)
        <strong>Command/Station:</strong> {{ $recruit->presentStation->name ?? 'N/A' }}<br>
        @endif
    </p>
    
    <p><strong>What's Next?</strong></p>
    <ul>
        <li>Your profile has been created and assigned an appointment number</li>
        <li>You will be notified when your service number is assigned after training completion</li>
        <li>Once your service number is assigned, you will receive an onboarding email with login credentials</li>
        <li>You can then access the portal to manage your profile and submit applications</li>
    </ul>
    
    <p><strong>Important:</strong> Please keep your appointment number safe. You will need it for future reference and when you receive your service number.</p>
    
    <p>If you have any questions or need assistance, please contact your command's HRD office or the Establishment unit.</p>
    
    <p>Thank you for joining the Nigeria Customs Service.</p>
    
    <p>Best regards,<br>
    {{ config('app.name', 'NCS Employee Portal') }} Team<br>
    Establishment Unit</p>
    
    <hr>
    <p><small>This is an automated notification from {{ config('app.name', 'NCS Employee Portal') }}.</small><br>
    <small><a href="{{ config('app.url') }}">Visit Portal</a></small></p>
</body>
</html>

