<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Staff Order Created</h2>
    
    <p>Dear {{ $user->name ?? $user->email }},</p>
    
    <p>A new staff order has been created for you.</p>
    
    <h3>Order Details:</h3>
    <p>
        <strong>Order Number:</strong> {{ $orderNumber }}<br>
        <strong>From Command:</strong> {{ $fromCommandName }}<br>
        <strong>To Command:</strong> {{ $toCommandName }}<br>
        <strong>Effective Date:</strong> {{ \Carbon\Carbon::parse($effectiveDate)->format('d/m/Y') }}<br>
        <strong>Status:</strong> {{ $status }}
        @if($description)
            <br><strong>Description:</strong> {{ $description }}
        @endif
    </p>
    
    <p>You can view the details of this staff order by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/hrd/staff-orders/{{ $orderId }}">{{ $appUrl }}/hrd/staff-orders/{{ $orderId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

