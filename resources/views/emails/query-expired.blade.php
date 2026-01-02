<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Query Expired - Added to Disciplinary Record</h2>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>This is to inform you that a query issued to you has <strong>EXPIRED</strong> and has been automatically added to your disciplinary record.</p>
    
    <h3>Query Details:</h3>
    <p>
        <strong>Issued By:</strong> {{ $issuedBy->name ?? $issuedBy->email ?? 'Staff Officer' }}<br>
        <strong>Date Issued:</strong> {{ $query->issued_at ? $query->issued_at->format('l, F j, Y \a\t g:i A') : 'N/A' }}<br>
        <strong>Response Deadline:</strong> {{ $query->response_deadline ? $query->response_deadline->format('l, F j, Y \a\t g:i A') : 'N/A' }}<br>
        <strong>Service Number:</strong> {{ $officer->service_number }}
    </p>
    
    <h3>Reason(s) for Query:</h3>
    <p>{{ $query->reason }}</p>
    
    <h3>Important Notice:</h3>
    <p>The response deadline for this query has passed without receiving your response. As a result, this query has been <strong>automatically added to your disciplinary record</strong>.</p>
    
    <p>This query will now appear in your disciplinary record and can be viewed by HRD and other authorized personnel.</p>
    
    <p>You can view the query details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/officer/queries/{{ $query->id }}">{{ $appUrl }}/officer/queries/{{ $query->id }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>





