<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plataforma para digitalizar la venta y operación de agencias de autobuses.">
    <meta name="theme-color" content="#010409">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>Parada | @yield('title', 'Operación digital para agencias de autobuses')</title>

    @include('components.layout.admin-lte.styles')

    <script type="text/javascript" src="{{ asset('js/just-validate.min.js') }}"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    @livewireStyles
</head>

<body class="public-home">

    {{ $slot }}

    @livewireScripts

</body>

</html>
