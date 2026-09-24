<!DOCTYPE html>
<html lang="es-VE">

<head>
    @php
        $seoTitle = 'Parada | Venta digital de pasajes para agencias de autobuses';
        $seoDescription = 'Digitaliza tu agencia de autobuses con venta de pasajes en línea, gestión de rutas, flota, pasajeros, pagos, cupones y validación de boletos QR.';
        $seoKeywords = implode(', ', [
            'compra de boletos de autobús',
            'venta de pasajes de autobús',
            'venta de boletos online',
            'pasajes de autobús en línea',
            'monta tu agencia de autobuses',
            'registra tu agencia de autobuses',
            'publica tu agencia de transporte',
            'digitaliza tu agencia de autobuses',
            'plataforma para agencias de autobuses',
            'software para empresas de transporte',
            'sistema para venta de pasajes',
            'sistema de reservas de autobuses',
            'CRM para agencias de transporte',
            'CMS para agencias de autobuses',
            'gestión de rutas de autobús',
            'gestión de viajes y salidas',
            'gestión de flota de autobuses',
            'gestión de pasajeros',
            'control de ventas de pasajes',
            'administración de empresas de transporte',
            'reservas de pasajes online',
            'boletos de autobús con código QR',
            'validación de pasajes QR',
            'aplicación para escanear boletos',
            'pasajes en Google Wallet',
            'pasajes en Apple Wallet',
            'cupones para pasajes de autobús',
            'pagos de pasajes en línea',
            'reportes de ventas de pasajes',
            'impresión térmica de boletos',
            'terminales de autobuses Venezuela',
            'agencias de autobuses Venezuela',
            'transporte terrestre Venezuela',
            'vender pasajes por internet',
            'Parada',
        ]);
        $canonicalUrl = url('/');
        $structuredData = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $canonicalUrl . '#organization',
                    'name' => config('app.name', 'Parada'),
                    'url' => $canonicalUrl,
                    'logo' => asset('assets/img/email/logo.svg'),
                    'description' => $seoDescription,
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Venezuela',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $canonicalUrl . '#website',
                    'name' => config('app.name', 'Parada'),
                    'url' => $canonicalUrl,
                    'inLanguage' => 'es-VE',
                    'publisher' => [
                        '@id' => $canonicalUrl . '#organization',
                    ],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => $canonicalUrl . '#platform',
                    'name' => 'Parada para agencias de autobuses',
                    'url' => $canonicalUrl,
                    'description' => $seoDescription,
                    'applicationCategory' => 'BusinessApplication',
                    'applicationSubCategory' => 'Gestión y venta de pasajes de autobús',
                    'operatingSystem' => 'Web',
                    'inLanguage' => 'es-VE',
                    'audience' => [
                        '@type' => 'BusinessAudience',
                        'audienceType' => 'Agencias y empresas de transporte terrestre',
                    ],
                    'provider' => [
                        '@id' => $canonicalUrl . '#organization',
                    ],
                    'featureList' => [
                        'Venta digital de pasajes',
                        'Gestión de rutas, salidas y autobuses',
                        'Administración de pasajeros y reservas',
                        'Campañas de cupones',
                        'Pasajes con código QR',
                        'Validación de boletos al abordar',
                        'Reportes de ventas y tasas de servicio',
                    ],
                ],
            ],
        ];
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ config('app.name', 'Parada') }}">
    <meta name="application-name" content="{{ config('app.name', 'Parada') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#010409">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_VE">
    <meta property="og:site_name" content="{{ config('app.name', 'Parada') }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">

    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="es-VE" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>@yield('title', $seoTitle)</title>

    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

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
