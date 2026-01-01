<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>{{ $notification->title }}</h2>
    
    <p>Dear {{ $user->name }},</p>
    
    <p>{{ $notification->message }}</p>
    
    @if($notification->entity_type && $notification->entity_id)
        <p>You can view the details by clicking the link below:</p>
        <p><a href="{{ $appUrl }}/notifications/{{ $notification->id }}">{{ $appUrl }}/notifications/{{ $notification->id }}</a></p>
    @endif
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    {{ config('app.name', 'NCS Employee Portal') }}</p>
    
    <hr>
    <p><small>This is an automated notification from the {{ config('app.name', 'NCS Employee Portal') }}.</small></p>
</body>
</html>
