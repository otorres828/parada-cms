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

    {{-- SEO & OpenGraph Configuration: Managed via dedicated component --}}
    <x-meta-tags/>

    {{-- Page Title: Defined in child views via @section('title') --}}
    <title>Parada | @yield('title')</title>

    {{-- --- Header Asset Injection --- --}}
    @section('header-assets')

        {{-- Base styles for Toastify notification system --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/toastify.min.css') }}"/>

        {{-- Critical library for aesthetic UI alerts --}}
        <script type="text/javascript" src="{{ asset('assets/js/sweetalert.js') }}"></script>

        <script type="text/javascript"  src="{{ asset('js/just-validate.min.js') }}"></script>

        {{-- Google Fonts --}}
        <link href="https://fonts.googleapis.com/css2?family=Anybody:wght@700;800&amp;family=Lexend:wght@400;600;700&amp;display=swap" rel="stylesheet">

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">

        {{-- Vite Assets --}}
        @vite(['resources/js/app.js','resources/css/site.css'])

    @show

    {{-- Favicon: Asset pathing and versioning handled by Vite --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/favicon.ico') }}" sizes="64x64">

    {{-- Livewire: Required styles for reactive components --}}
    @livewireStyles

</head>

<body>

    {{-- (Blade Component Slot) --}}
    <main>
        {{ $slot }}
    </main>

    {{-- ---Global Loader System  --- --}}
    <x-loader/>

    {{-- ---Global Toast Notification System  --- --}}
    <x-toast-alerts/>

    {{-- --- Footer Asset Injection --- --}}
    {{-- Using @show allows child views to use @parent to keep app.js while adding local scripts --}}
    @section('footer-assets')

        <script type="text/javascript" src="{{ asset('assets/js/toastify.min.js') }}"></script>

        {{--Third Party Plugin(OverlayScrollbars)--}}
        <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>

        {{--Required Plugin(popperjs for Bootstrap 5)--}}
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>

        {{--Required Plugin(Bootstrap 5)--}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>


    @show

    {{-- Livewire: Core scripts for component reactivity --}}
    @livewireScripts

</body>

</html>
