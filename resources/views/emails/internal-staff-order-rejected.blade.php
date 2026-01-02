<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Internal Staff Order Rejected</h2>
    
    <p>Dear {{ $user->name ?? $user->email }},</p>
    
    <p>Your internal staff order has been rejected.</p>
    
    <h3>Order Details:</h3>
    <p>
        <strong>Order Number:</strong> {{ $orderNumber }}<br>
        <strong>Command:</strong> {{ $commandName }}<br>
        <strong>Rejected By:</strong> {{ $rejectedByName }}
    </p>
    
    <h3>Rejection Reason:</h3>
    <p>{{ $rejectionReason }}</p>
    
    <p>You can view the order details and make necessary corrections by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/internal-staff-orders/{{ $orderId }}">{{ $appUrl }}/staff-officer/internal-staff-orders/{{ $orderId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

