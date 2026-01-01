<!DOCTYPE html>
<html>

<head>
    <title>APER Form Awaiting Countersignature</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
        <h2 style="color: #2d3748;">APER Form Awaiting Countersignature</h2>
        <p>Hello {{ $recipient->name }},</p>
        <p>An Annual Performance Evaluation Report (APER) for <strong>{{ $form->officer->initials }}
                {{ $form->officer->surname }}</strong> ({{ $form->officer->service_number }}) has been completed by the
            Reporting Officer and is now awaiting countersignature.</p>
        <p>As an eligible senior officer in the <strong>{{ $form->officer->command->name ?? 'same command' }}</strong>,
            this form is now available for you to countersign.</p>

        <div style="background-color: #f7fafc; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Officer:</strong> {{ $form->officer->initials }} {{ $form->officer->surname }}
            </p>
            <p style="margin: 5px 0 0 0;"><strong>Reporting Officer:</strong>
                {{ $form->reportingOfficer->name ?? 'N/A' }}</p>
            <p style="margin: 5px 0 0 0;"><strong>Year:</strong> {{ $form->year }}</p>
        </div>

        <p>You can access and countersign this form by visiting the APER Countersigning search page:</p>
        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ route('officer.aper-forms.countersigning.search') }}"
                style="background-color: #4a5568; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">View
                Pending Forms</a>
        </p>

        <p style="margin-top: 30px; font-size: 0.8em; color: #718096;">
            Please note: This form is available to all eligible senior officers in the command. Once countersigned by
            one officer, it will no longer be available for others to act upon.
        </p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 0.8em; color: #718096;">This is an automated notification from the PIS Portal.</p>
    </div>
</body>

</html>