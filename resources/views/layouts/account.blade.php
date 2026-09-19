{{--
    Master Layout Template
    Principal entry point for the Parada e-commerce application.
    This layout uses a Slot-based architecture (<x-layout>) rather than @extends.
--}}

{{-- --- HTML Boilerplate & Localization --- --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    {{-- Viewport: Configured for mobile-first responsiveness and UI consistency --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">

    {{-- Page Title: Defined in child views via @section('title') --}}
    <title>Parada | @yield('title')</title>

    {{-- --- Header Asset Injection --- --}}
    @section('header-assets')

        {{-- Google Fonts --}}
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700;800&amp;family=Anybody:wght@700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet" />

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />

        {{-- Base styles for Toastify notification system --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/toastify.min.css') }}"/>

        {{-- Critical library for aesthetic UI alerts --}}
        <script type="text/javascript" src="{{ asset('assets/js/sweetalert.js') }}"></script>

        {{-- Flatpickr Datepicker Styles --}}
        <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">

        {{-- Vite Assets --}}
        @vite(['resources/js/app.js','resources/css/account.css'])

    @show

    {{-- Favicon: Asset pathing and versioning handled by Vite --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/favicon.ico') }}" sizes="64x64">

</head>

<body class="bg-background text-on-background overflow-hidden h-screen flex flex-col">

    {{-- ---Global Toast Notification System  --- --}}
    <x-toast-alerts/>

    {{-- Header --}}
    <livewire:site.component.header/>

    {{-- Sidebar Desktop --}}
    <x-site.account.menu-desktop />

    {{-- Sidebar Mobile --}}
    <x-site.account.menu-mobile />

    {{-- (Blade Component Slot) --}}
    <main class="flex flex-1 pt-16 h-full overflow-hidden">

        {{ $slot }}

    </main>


    {{-- --- Footer Asset Injection --- --}}
    {{-- Using @show allows child views to use @parent to keep app.js while adding local scripts --}}
    @section('footer-assets')

        {{-- Toastify Notification System Scripts --}}
        <script type="text/javascript" src="{{ asset('assets/js/toastify.min.js') }}"></script>

        {{-- Flatpickr Datepicker Scripts --}}
        <script type="text/javascript"  src="{{ asset('js/flatpickr.js') }}"></script>

        <script type="text/javascript"  src="{{ asset('js/flatpickr/es.js') }}"></script>

    @show

</body>

</html>
