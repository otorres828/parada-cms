{{-- Logo compartido: dirige al dashboard del panel actual. --}}

@php
    $dashboardRoute = null;

    if (request()->is('admin', 'admin/*')) {
        $dashboardRoute = 'admin.dashboard';
    } elseif (request()->is('empresa', 'empresa/*')) {
        $dashboardRoute = 'empresas.dashboard';
    }
@endphp

@if ($dashboardRoute)
    <div class="sidebar-brand">

        <a href="{{ route($dashboardRoute) }}" class="brand-link">

            <img src="{{ asset('assets/img/logo/icon-header.png') }}" alt="Logo de la plataforma"
                class="w-160px" />

        </a>

    </div>
@endif
