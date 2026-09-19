{{--Accessibility Meta Tags--}}
<meta name="color-scheme" content="light dark" />
<meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
<meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
<meta name="supported-color-schemes" content="light dark" />

<link rel="preload" href="{{ asset('/css/adminlte.css') }}" as="style" />

{{--Fonts--}}
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
    crossorigin="anonymous"
    media="print"
    onload="this.media='all'"
/>

{{--Third Party Plugin(OverlayScrollbars)--}}
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous"
/>


{{--Required Third Party Plugin(Bootstrap Icons)--}}
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous"
/>

 {{--Required Plugin(AdminLTE)--}}
<link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />

<link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}" />

<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/toastify.min.css') }}"/>

<style>

    @media (max-width: 991.98px) {
    .app-sidebar {
        z-index: 1040 !important;
        pointer-events: auto !important;
    }

    .sidebar-overlay {
        z-index: 1030 !important;
    }
    }
</style>
