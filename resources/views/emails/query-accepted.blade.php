<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Query Response Accepted</h2>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>Your response to the query has been reviewed and <strong>ACCEPTED</strong>.</p>
    
    <h3>Query Details:</h3>
    <p>
        <strong>Query Issued:</strong> {{ $query->issued_at ? $query->issued_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}<br>
        <strong>Response Submitted:</strong> {{ $query->responded_at ? $query->responded_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}<br>
        <strong>Reviewed On:</strong> {{ $query->reviewed_at ? $query->reviewed_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}
    </p>
    
    <h3>Important:</h3>
    <p>This query has been added to your disciplinary record and will be visible in your profile and HRD's view of your profile.</p>
    
    <p>Your response has been reviewed by the Staff Officer who issued the query. This query will now appear in your disciplinary record and can be viewed by HRD and other authorized personnel.</p>
    
    <p>You can view the query details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/officer/queries/{{ $query->id }}">{{ $appUrl }}/officer/queries/{{ $query->id }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>
