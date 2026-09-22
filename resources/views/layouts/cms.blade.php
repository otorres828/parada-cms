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

    @stack('styles')

</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

    <div class="app-wrapper">

        <x-layout.header />

        <x-layout.sidebar />

        <x-toast-alerts/>

        <x-layout.content>

            {{ $slot }}

        </x-layout.content>

    </div>

    @include('components.layout.admin-lte.footer-scripts')

    @stack('scripts')

</body>

</html>
