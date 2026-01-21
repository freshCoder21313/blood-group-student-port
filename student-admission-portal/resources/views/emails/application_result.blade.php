<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Admission Result Notification') }}</title>
</head>
<body>
    <h1>{{ __('Dear') }} {{ $name }},</h1>

    @if($status === 'approved')
        <p style="color: green; font-weight: bold;">
            {{ __('Congratulations! Your application for the :program program has been APPROVED.', ['program' => $program]) }}
        </p>
        <p>{{ __('Please log in to the portal to complete the enrollment process.') }}</p>
    @else
        <p style="color: red;">
            {{ __('We regret to inform you that your application for the :program program has not met the requirements or requires additional information.', ['program' => $program]) }}
        </p>
        <p>{{ __('Please check your information or contact the Admissions Office.') }}</p>
    @endif

    <p>{{ __('Sincerely') }},<br>{{ __('Admissions Office') }}</p>
</body>
</html>
