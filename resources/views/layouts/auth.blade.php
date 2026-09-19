<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/favicon.ico') }}" sizes="64x64">

    <title>Parada | @yield('title')</title>

    <meta name="robots" content="noindex, nofollow">

    @include('components.layout.admin-lte.styles')

    @include('components.layout.admin-lte.header-scripts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="login-page bg-auth">

    {{ $slot }}

    @include('components.layout.admin-lte.footer-scripts')

    <script src="https://www.google.com/recaptcha/api.js?render={{ env('GOOGLE_RECAPTCHA_ID') }}"></script>

</body>

</html>
