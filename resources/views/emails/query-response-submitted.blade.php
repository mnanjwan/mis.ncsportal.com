<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Query Response Submitted</h2>
    
    <p>Dear Staff Officer,</p>
    
    <p>Officer {{ $officer->initials }} {{ $officer->surname }} ({{ $officer->service_number }}) has submitted a response to your query and is awaiting your review.</p>
    
    <h3>Query Details:</h3>
    <p>
        <strong>Officer:</strong> {{ $officer->initials }} {{ $officer->surname }}<br>
        <strong>Service Number:</strong> {{ $officer->service_number }}<br>
        <strong>Query Issued:</strong> {{ $query->issued_at ? $query->issued_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}<br>
        <strong>Response Submitted:</strong> {{ $query->responded_at ? $query->responded_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}
    </p>
    
    <h3>Officer's Response:</h3>
    <p>{{ $query->response }}</p>
    
    <h3>Action Required:</h3>
    <p>Please review the officer's response and decide whether to accept or reject the query. If accepted, the query will be added to the officer's disciplinary record.</p>
    
    <p>You can review this query by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/queries/{{ $query->id }}">{{ $appUrl }}/staff-officer/queries/{{ $query->id }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>
