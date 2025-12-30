<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Duty Roster Approved</title>
</head>
<body>
    <h1>Duty Roster Approved</h1>
    
    <p>Dear Staff Officer,</p>
    
    <p>Your duty roster has been approved.</p>
    
    <h2>Roster Details</h2>
    <ul>
        <li><strong>Roster ID:</strong> {{ $rosterId }}</li>
        <li><strong>Unit:</strong> {{ $roster->unit ?? 'Not specified' }}</li>
        <li><strong>Command:</strong> {{ $commandName }}</li>
        <li><strong>Period:</strong> {{ $periodStart }} to {{ $periodEnd }}</li>
        <li><strong>Approved By:</strong> {{ $approvedByName }}</li>
        <li><strong>Approved At:</strong> {{ $roster->approved_at ? \Carbon\Carbon::parse($roster->approved_at)->format('d/m/Y H:i') : 'N/A' }}</li>
    </ul>
    
    <p>All assigned officers (OIC, 2IC, and regular officers) have been notified via email about their assignments.</p>
    
    <p>You can view the roster details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/roster/{{ $rosterId }}">View Roster</a></p>
    
    <p>Best regards,<br>System</p>
</body>
</html>

