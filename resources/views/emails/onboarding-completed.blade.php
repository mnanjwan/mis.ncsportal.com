<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
        }
        .content {
            text-align: left;
        }
        h2 {
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
        }
        p {
            margin: 15px 0;
            color: #333333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #333333;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h2>{{ $notification->title }}</h2>
            
            <p>Dear {{ $user->name ?? 'Officer' }},</p>
            
            <p>{{ $notification->message }}</p>
            
            <p>You can now log in to your dashboard and access all available features.</p>
            
            <p>Thank you for completing your onboarding process.</p>
            
            <p>Best regards,<br>
            {{ config('app.name', 'PIS Portal') }} Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from {{ config('app.name', 'PIS Portal') }}.</p>
            <p><a href="{{ config('app.url') }}" style="color: #068b57;">Visit Portal</a></p>
        </div>
    </div>
</body>
</html>
