<x-mail::message>
# {{ config('app.name') }} Password Updated

Your <a href="{{ route('welcome') }}">{{ config('app.name') }}</a> account password has been updated.

<x-mail::table>
| Field         | Value                      |
|:-------------:|:--------------------------:|
| Email         | {{ $user->email }}         |
| New Password  | {{ $password }}            |
</x-mail::table>

<x-mail::button :url="route('login')">
Authenticate
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>