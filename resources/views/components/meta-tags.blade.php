{{--
    SEO Dynamic Metadata
    Injected from child views using @yield.
    Ensure @section('meta-title', '...') is defined in the specific page template.
--}}
<meta name="title" content="@yield('meta-title')">
<meta name="description" content="@yield('meta-description')">
<meta name="keywords" content="@yield('meta-keywords')">

{{--
    Open Graph (OG) Social Media Tags
    Configures how the site appears when shared on Facebook, LinkedIn, and WhatsApp.
--}}
<meta property="og:url" content="https://Parada.com" />
<meta property="og:type" content="website" />
<meta property="og:title" content="Parada" />
<meta property="og:description"
    content="@yield('meta-title')" />
<meta property="og:image" content="{{ asset('assets/img/logo/icon-roster.png') }}" />

{{--
    Twitter Card Metadata
    Optimized for X (Twitter) sharing.
--}}
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="Parada" />
<meta name="twitter:description"
    content="@yield('meta-title')" />
<meta name="twitter:image" content="{{ asset('assets/img/logo/icon-roster.png') }}" />
