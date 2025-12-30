<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Duty Roster Rejected</title>
</head>
<body>
    <h1>Duty Roster Rejected</h1>
    
    <p>Dear Staff Officer,</p>
    
    <p>Your duty roster has been rejected and requires revision.</p>
    
    <h2>Roster Details</h2>
    <ul>
        <li><strong>Roster ID:</strong> {{ $rosterId }}</li>
        <li><strong>Command:</strong> {{ $commandName }}</li>
        <li><strong>Period:</strong> {{ $periodStart }} to {{ $periodEnd }}</li>
        <li><strong>Rejected By:</strong> {{ $rejectedByName }}</li>
        <li><strong>Rejected At:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</li>
    </ul>
    
    <h2>Rejection Reason</h2>
    <p>{{ $rejectionReason }}</p>
    
    <p>Please review the rejection reason and make the necessary corrections. You can edit the roster by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/roster/{{ $rosterId }}/edit">Edit Roster</a></p>
    
    <p>Best regards,<br>System</p>
</body>
</html>

