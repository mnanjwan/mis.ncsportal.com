<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Internal Staff Order Approved</h2>
    
    <p>Dear {{ $user->name ?? $user->email }},</p>
    
    <p>An internal staff order has been approved and the roster has been updated.</p>
    
    <h3>Order Details:</h3>
    <p>
        <strong>Order Number:</strong> {{ $orderNumber }}<br>
        <strong>Command:</strong> {{ $commandName }}<br>
        <strong>Officer:</strong> {{ $officerName }} ({{ $serviceNumber }})<br>
        @if($currentUnit)
            <strong>Previous Assignment:</strong> {{ $currentUnit }}@if($currentRole) - {{ $currentRole }}@endif<br>
        @endif
        <strong>New Assignment:</strong> {{ $targetUnit }} - {{ $targetRole }}
    </p>
    
    @if($outgoingOfficer)
        <p><strong>Note:</strong> {{ $outgoingOfficer->initials }} {{ $outgoingOfficer->surname }} ({{ $outgoingOfficer->service_number }}) has been replaced as {{ $targetRole }} of {{ $targetUnit }} and reassigned as a regular member.</p>
    @endif
    
    <p>You can view the order details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/print/internal-staff-order/{{ $orderId }}">{{ $appUrl }}/print/internal-staff-order/{{ $orderId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

