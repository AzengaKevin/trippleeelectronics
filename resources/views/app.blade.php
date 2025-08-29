<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="mask-icon" href="{{ asset('maskable-icon.png') }}" color="#FFFFFF">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @routes
    @inertiaHead
</head>

<body class="font-sans antialiased min-h-screen bg-base-200">
    @inertia
</body>

</html>
