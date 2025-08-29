<x-mail::message>
# {{ config('app.name') }} Account Details Mail

An account has been created for you on <a href="{{ route('welcome') }}">{{ config('app.name') }}</a>.

<x-mail::table>
| Field         | Value                      |
|:-------------:|:--------------------------:|
| Email         | {{ $user->email }}         |
| New Password  | {{ $password }}            |
</x-mail::table>

<x-mail::button :url="route('login')">
Get Started
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>