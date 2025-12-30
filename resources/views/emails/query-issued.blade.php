<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Query Issued</h2>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>A query has been issued to you and requires your response.</p>
    
    <h3>Query Details:</h3>
    <p>
        <strong>Issued By:</strong> {{ $issuedBy->name ?? $issuedBy->email ?? 'Staff Officer' }}<br>
        <strong>Date Issued:</strong> {{ $query->issued_at ? $query->issued_at->format('l, F j, Y \a\t g:i A') : date('l, F j, Y \a\t g:i A') }}<br>
        <strong>Response Deadline:</strong> {{ $query->response_deadline ? $query->response_deadline->format('l, F j, Y \a\t g:i A') : 'Not specified' }}<br>
        <strong>Service Number:</strong> {{ $officer->service_number }}
    </p>
    
    <h3>Reason(s) for Query:</h3>
    <p>{{ $query->reason }}</p>
    
    <h3>Action Required:</h3>
    <p><strong>IMPORTANT:</strong> You must respond to this query before the deadline ({{ $query->response_deadline ? $query->response_deadline->format('l, F j, Y \a\t g:i A') : 'Not specified' }}).</p>
    <p>If you fail to respond by the deadline, this query will automatically be added to your disciplinary record.</p>
    <p>Please respond to this query through your dashboard. Your response will be reviewed by the Staff Officer who issued this query.</p>
    
    <p>You can view and respond to this query by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/officer/queries/{{ $query->id }}">{{ $appUrl }}/officer/queries/{{ $query->id }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>
