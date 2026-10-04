{{--
    EMPRESAS — MENÚ LATERAL
    --------------------------------------------------------------------------
    Presenta los grupos y módulos empresariales según los permisos del usuario.
    La clase resuelve permisos y la vista genera enlaces siguiendo el menú de Admin.
    Reutiliza components.layout.sidebar-li para enlaces y grupos desplegables.
    --------------------------------------------------------------------------
--}}

@if ($listDashboard)
    @include('components.layout.sidebar-li', [
        'menu' => 'Dashboard',
        'icon' => 'nav-icon bi bi-speedometer2',
        'route' => route('empresas.dashboard'),
        'active' => request()->routeIs('empresas.dashboard') ? 'active' : '',
    ])
@endif

@if ($listUsuarios or $editPoliticasEmbarque or $listDatosBancarios)
    @include('components.layout.sidebar-li', [
        'menu' => 'Administración',
        'icon' => 'nav-icon bi bi-shield-lock',
        'list' => [
            [
                'existe' => $listUsuarios ?? false,
                'route' => route('empresas.usuarios.list'),
                'name' => 'Usuarios',
                'active' => request()->routeIs('empresas.usuarios.*') ? 'active' : '',
            ],
            [
                'existe' => $editPoliticasEmbarque ?? false,
                'route' => route('empresas.politicas-embarque.edit'),
                'name' => 'Políticas de embarque',
                'active' => request()->routeIs('empresas.politicas-embarque.*') ? 'active' : '',
                'navigate' => false,
            ],
            [
                'existe' => $listDatosBancarios,
                'route' => route('empresas.datos-bancarios.list'),
                'name' => 'Datos Bancarios',
                'active' => request()->routeIs('empresas.datos-bancarios.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listViajes or $listProgramaciones or $listTransportes)
    @include('components.layout.sidebar-li', [
        'menu' => 'Operación de viajes',
        'icon' => 'nav-icon bi bi-bus-front',
        'list' => [
            [
                'existe' => $listViajes ?? false,
                'route' => route('empresas.viajes.list'),
                'name' => 'Rutas de viaje',
                'active' => request()->routeIs('empresas.viajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listProgramaciones ?? false,
                'route' => route('empresas.programaciones.list'),
                'name' => 'Programaciones',
                'active' => request()->routeIs('empresas.programaciones.*') ? 'active' : '',
            ],
            [
                'existe' => $listTransportes ?? false,
                'route' => route('empresas.transportes.list'),
                'name' => 'Transportes',
                'active' => request()->routeIs('empresas.transportes.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listReservas or $listPasajes or $listValidacionPagos or $listReembolsos or $listReprogramaciones)
    @include('components.layout.sidebar-li', [
        'menu' => 'Ventas y finanzas',
        'icon' => 'nav-icon bi bi-wallet2',
        'list' => [
            [
                'existe' => $listReservas ?? false,
                'route' => route('empresas.reservas.list'),
                'name' => 'Reservas',
                'active' => request()->routeIs('empresas.reservas.*') ? 'active' : '',
            ],
            [
                'existe' => $listPasajes ?? false,
                'route' => route('empresas.pasajes.list'),
                'name' => 'Pasajes',
                'active' => request()->routeIs('empresas.pasajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listValidacionPagos ?? false,
                'route' => route('empresas.validacion-pagos.list'),
                'name' => 'Validación de pagos',
                'active' => request()->routeIs('empresas.validacion-pagos.*') ? 'active' : '',
            ],
            [
                'existe' => $listReembolsos ?? false,
                'route' => route('empresas.reembolsos.list'),
                'name' => 'Reembolsos',
                'active' => request()->routeIs('empresas.reembolsos.*') ? 'active' : '',
            ],
            [
                'existe' => $listReprogramaciones ?? false,
                'route' => route('empresas.reprogramaciones.list'),
                'name' => 'Reprogramaciones',
                'active' => request()->routeIs('empresas.reprogramaciones.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listOrdenesCobro)
    @include('components.layout.sidebar-li', [
        'menu' => 'Cobranza',
        'icon' => 'nav-icon bi bi-receipt',
        'list' => [
            [
                'existe' => $listOrdenesCobro ?? false,
                'route' => route('empresas.ordenes-cobro.list'),
                'name' => 'Órdenes de cobro',
                'active' => request()->routeIs('empresas.ordenes-cobro.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listCupones)
    @include('components.layout.sidebar-li', [
        'menu' => 'Cupones',
        'icon' => 'nav-icon bi bi-tags',
        'existe' => $listCupones ?? false,
        'route' => route('empresas.cupones.list'),
        'active' => request()->routeIs('empresas.cupones.*') ? 'active' : '',
    ])
@endif

@if ($listReporteVentas or $listReporteRutas)
    @include('components.layout.sidebar-li', [
        'menu' => 'Reportes',
        'icon' => 'nav-icon bi bi-bar-chart',
        'list' => [
            [
                'existe' => $listReporteVentas ?? false,
                'route' => route('empresas.reportes.ventas'),
                'name' => 'Ventas',
                'active' => request()->routeIs('empresas.reportes.ventas') ? 'active' : '',
            ],
            [
                'existe' => $listReporteRutas ?? false,
                'route' => route('empresas.reportes.rutas'),
                'name' => 'Rutas',
                'active' => request()->routeIs('empresas.reportes.rutas') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($profileAccount or $passwordAccount)
    @include('components.layout.sidebar-li', [
        'menu' => 'Mi cuenta',
        'icon' => 'nav-icon bi bi-person-circle',
        'list' => [
            [
                'existe' => $profileAccount ?? false,
                'route' => route('empresas.account.profile'),
                'name' => 'Perfil',
                'active' => request()->routeIs('empresas.account.profile') ? 'active' : '',
            ],
            [
                'existe' => $passwordAccount ?? false,
                'route' => route('empresas.account.password'),
                'name' => 'Contraseña',
                'active' => request()->routeIs('empresas.account.password') ? 'active' : '',
            ],
        ],
    ])
@endif
