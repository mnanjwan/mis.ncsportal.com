<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCS Employee Portal - Onboarding</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #068b57;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #068b57;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials {
            background-color: #fff;
            padding: 15px;
            border-left: 4px solid #068b57;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #333333;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NCS Employee Portal</h1>
        <p>Onboarding Invitation</p>
    </div>
    
    <div class="content">
        @if($officerName)
            <p>Dear {{ $officerName }},</p>
        @else
            <p>Dear Officer,</p>
        @endif

        <p>You have been invited to complete your onboarding for the NCS Employee Portal.</p>

        <p>Please use the link below to access the onboarding form and complete your registration:</p>

        <div style="text-align: center;">
            <a href="{{ $onboardingLink }}" class="button">Complete Onboarding</a>
        </div>

        <div class="credentials">
            <p><strong>Onboarding Link:</strong></p>
            <p style="word-break: break-all; font-family: monospace; font-size: 12px;">{{ $onboardingLink }}</p>
            
            <p style="margin-top: 15px;"><strong>Temporary Password:</strong></p>
            <p style="font-family: monospace; font-size: 14px; font-weight: bold;">{{ $tempPassword }}</p>
        </div>

        <p><strong>Important Instructions:</strong></p>
        <ul>
            <li>Click the button above or copy the link to complete your onboarding</li>
            <li>Use the temporary password provided to log in</li>
            <li>You will be required to change your password after first login</li>
            <li>Complete all required information in the multi-step form</li>
            <li>Upload required documents (preferably in JPEG format)</li>
        </ul>

        <p>If you have any questions or need assistance, please contact the HRD department.</p>

        <p>Best regards,<br>
        <strong>NCS Human Resources Department</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated message from the NCS Employee Portal.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>

