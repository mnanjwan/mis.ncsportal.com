<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Duty Roster Submitted - Requires Approval</h2>
    
    <p>Dear {{ $user->name ?? $user->email }},</p>
    
    <p>A duty roster has been submitted and requires your approval.</p>
    
    <h3>Roster Details:</h3>
    <p>
        <strong>Command:</strong> {{ $commandName }}<br>
        <strong>Period:</strong> {{ $periodStart }} to {{ $periodEnd }}<br>
        <strong>Prepared By:</strong> {{ $preparedByName }}<br>
        <strong>Total Assignments:</strong> {{ $assignmentsCount }}
    </p>
    
    @if($oicName)
        <p><strong>Officer in Charge:</strong> {{ $oicName }}</p>
    @endif
    
    @if($secondInCommandName)
        <p><strong>Second In Command:</strong> {{ $secondInCommandName }}</p>
    @endif
    
    <p>Please review and approve or reject this roster by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/{{ $approvalRoute }}/{{ $rosterId }}">{{ $appUrl }}/{{ $approvalRoute }}/{{ $rosterId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

