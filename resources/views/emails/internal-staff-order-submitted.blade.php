<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Internal Staff Order Submitted - Requires Approval</h2>
    
    <p>Dear {{ $user->name ?? $user->email }},</p>
    
    <p>An internal staff order has been submitted and requires your approval.</p>
    
    <h3>Order Details:</h3>
    <p>
        <strong>Order Number:</strong> {{ $orderNumber }}<br>
        <strong>Command:</strong> {{ $commandName }}<br>
        <strong>Prepared By:</strong> {{ $preparedByName }}<br>
        <strong>Officer:</strong> {{ $officerName }} ({{ $serviceNumber }})<br>
        @if($currentUnit)
            <strong>Current Assignment:</strong> {{ $currentUnit }}@if($currentRole) - {{ $currentRole }}@endif<br>
        @endif
        <strong>Target Assignment:</strong> {{ $targetUnit }} - {{ $targetRole }}
    </p>
    
    <p>Please review and approve or reject this order by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/dc-admin/internal-staff-orders/{{ $orderId }}">{{ $appUrl }}/dc-admin/internal-staff-orders/{{ $orderId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

