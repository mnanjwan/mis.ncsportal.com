<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Duty Roster Assignment</h2>
    
    <p>Dear {{ $officer->initials }} {{ $officer->surname }},</p>
    
    <p>You have been assigned to the duty roster for {{ $commandName }}.</p>
    
    <h3>Assignment Details:</h3>
    <p>
        <strong>Role:</strong> {{ $role }}<br>
        <strong>Roster Period:</strong> {{ $periodStart }} to {{ $periodEnd }}<br>
        <strong>Service Number:</strong> {{ $officer->service_number }}
    </p>
    
    @if($role === 'Officer in Charge (OIC)')
        <p>You are the Officer in Charge for this roster period.</p>
    @elseif($role === 'Second In Command (2IC)')
        <p>You are the Second In Command for this roster period.</p>
        @if($oicName)
            <p><strong>Officer in Charge:</strong> {{ $oicName }}</p>
        @endif
    @else
        @if($oicName)
            <p><strong>Officer in Charge:</strong> {{ $oicName }}</p>
        @endif
        @if($secondInCommandName)
            <p><strong>Second In Command:</strong> {{ $secondInCommandName }}</p>
        @endif
    @endif
    
    <h3>Your Assignments:</h3>
    @if($assignments && $assignments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Duty Date</th>
                    <th>Shift</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->duty_date ? $assignment->duty_date->format('M d, Y') : 'Not specified' }}</td>
                        <td>{{ $assignment->shift ?? 'N/A' }}</td>
                        <td>{{ $assignment->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No specific assignments have been made yet.</p>
    @endif
    
    <p>You can view the complete roster details by clicking the link below:</p>
    <p><a href="{{ $appUrl }}/staff-officer/roster/{{ $rosterId }}">{{ $appUrl }}/staff-officer/roster/{{ $rosterId }}</a></p>
    
    <p>Thank you.</p>
    
    <p>Best regards,<br>
    NCS Employee Portal</p>
    
    <hr>
    <p><small>This is an automated notification from the NCS Employee Portal.</small></p>
</body>
</html>

