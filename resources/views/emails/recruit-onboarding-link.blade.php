<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Welcome to NCS Employee Portal</h1>

    <p>
        @if($recruitName)
            Dear {{ $recruitName }},
        @else
            Dear Recruit,
        @endif
    </p>

    <p>Congratulations! You have been selected to join the Nigeria Customs Service. We are excited to have you on board.</p>

    <p>To complete your onboarding process, please click the link below:</p>

    <p><a href="{{ $onboardingLink }}">{{ $onboardingLink }}</a></p>

    <h2>What you need to do:</h2>
    <ol>
        <li>Click the link above to access the onboarding form</li>
        <li>Complete all 4 steps of the onboarding form:
            <ul>
                <li>Step 1: Personal Information</li>
                <li>Step 2: Employment Details</li>
                <li>Step 3: Banking Information</li>
                <li>Step 4: Next of Kin & Documents</li>
            </ul>
        </li>
        <li>Review your information in the preview page</li>
        <li>Submit for verification - The Establishment office will review your documents</li>
    </ol>

    <h2>Important:</h2>
    <ul>
        <li>Please complete all required fields accurately</li>
        <li>Upload all required documents (preferably in JPEG format)</li>
        <li>Any false information provided can lead to dismissal for forgery under the PSR Rules</li>
        <li>You can save your progress and return later if needed</li>
    </ul>

    <p>If you have any questions or encounter any issues during the onboarding process, please contact the Establishment office.</p>

    <p>Best regards,<br>
    NCS Establishment Office</p>

    <hr>
    <p><small>This is an automated message from the NCS Employee Portal. Please do not reply to this email.</small></p>
</body>
</html>
