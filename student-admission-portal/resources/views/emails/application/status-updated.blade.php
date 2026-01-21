<x-mail::message>
# Application Status Updated

Hello {{ $application->student->first_name }},

Your application status has been updated to: **{{ ucfirst(str_replace('_', ' ', $status)) }}**.

<x-mail::button :url="config('app.url')">
View Application
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
