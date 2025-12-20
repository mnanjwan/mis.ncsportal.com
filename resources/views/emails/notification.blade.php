<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
        }
        .header {
            background-color: #068b57;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px 20px;
        }
        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #068b57;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #333333;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">{{ config('app.name', 'MIS Portal') }}</h1>
        </div>
        
        <div class="content">
            <span class="notification-type" style="background-color: #e6f7f0; color: #068b57;">
                {{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}
            </span>
            
            <h2 style="color: #333333; margin-top: 0;">{{ $notification->title }}</h2>
            
            <div style="color: #333333; white-space: pre-wrap;">{{ $notification->message }}</div>
            
            @if($notification->entity_type && $notification->entity_id)
                <a href="{{ $appUrl }}/notifications/{{ $notification->id }}" class="button">
                    View Details
                </a>
            @endif
        </div>
        
        <div class="footer">
            <p>This is an automated notification from {{ config('app.name', 'PIS Portal') }}.</p>
            <p>You are receiving this because you are subscribed to notifications.</p>
            <p style="margin-top: 10px;">
                <a href="{{ $appUrl }}" style="color: #068b57; text-decoration: none;">Visit Portal</a>
            </p>
        </div>
    </div>
</body>
</html>
