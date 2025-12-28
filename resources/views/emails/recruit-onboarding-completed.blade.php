<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Recruit Onboarding Completed</h2>
    
    <p>Dear Establishment Team,</p>
    
    <p>A recruit has completed their onboarding process and is ready for verification.</p>
    
    <h3>Recruit Information:</h3>
    <p>
        <strong>Name:</strong> {{ $recruitName }}<br>
        <strong>Email:</strong> {{ $recruit->email ?? 'N/A' }}<br>
        <strong>Appointment Number:</strong> {{ $recruit->appointment_number ?? 'N/A' }}<br>
        <strong>Rank:</strong> {{ $recruit->substantive_rank ?? 'N/A' }}<br>
        <strong>Salary Grade Level:</strong> {{ $recruit->salary_grade_level ?? 'N/A' }}<br>
        <strong>Phone:</strong> {{ $recruit->phone_number ?? 'N/A' }}
    </p>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Review the recruit's submitted information and documents</li>
        <li>Verify all uploaded documents</li>
        <li>Approve or reject the onboarding</li>
    </ol>
    
    <p>You can view and verify this recruit by logging into the Establishment dashboard.</p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

