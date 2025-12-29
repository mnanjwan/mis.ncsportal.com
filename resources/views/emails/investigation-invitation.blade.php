<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Investigation Invitation</h2>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>You have been invited to an investigation hearing.</p>
    
    <h3>Investigation Details:</h3>
    <p>
        <strong>Investigation Officer:</strong> {{ $investigationOfficer->name ?? $investigationOfficer->email ?? 'Investigation Unit' }}<br>
        <strong>Date Invited:</strong> {{ $investigation->invited_at ? $investigation->invited_at->format('l, F j, Y \a\t g:i A') : date('l, F j, Y \a\t g:i A') }}<br>
        <strong>Service Number:</strong> {{ $officer->service_number }}
    </p>
    
    <h3>Invitation Message:</h3>
    <p>{{ $investigation->invitation_message ?? 'Please contact the Investigation Unit for further details.' }}</p>
    
    <h3>Action Required:</h3>
    <p>Please review the investigation invitation and contact the Investigation Unit if you have any questions.</p>
    
    <p>You can view the investigation details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/investigation/{{ $investigation->id }}">{{ $appUrl }}/investigation/{{ $investigation->id }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal - Investigation Unit</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

