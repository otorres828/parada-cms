{{--
    EMPRESAS — MENÚ LATERAL
    --------------------------------------------------------------------------
    Presenta los grupos y módulos empresariales según los permisos del usuario.
    La clase resuelve permisos; la vista genera enlaces y oculta módulos sin ruta.
    Reutiliza components.layout.sidebar-li para enlaces y grupos desplegables.
    --------------------------------------------------------------------------
--}}

@if (($listDashboard && Route::has('empresas.dashboard')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Dashboard',
        'icon' => 'nav-icon bi bi-speedometer2',
        'route' => route('empresas.dashboard'),
        'active' => request()->routeIs('empresas.dashboard') ? 'active' : '',
    ])
@endif

@if (($listUsuarios && Route::has('empresas.usuarios.list')) or ($editPoliticasEmbarque && Route::has('empresas.politicas-embarque.edit')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Administración',
        'icon' => 'nav-icon bi bi-shield-lock',
        'list' => [
            [
                'existe' => $listUsuarios && Route::has('empresas.usuarios.list'),
                'route' => Route::has('empresas.usuarios.list') ? route('empresas.usuarios.list') : '',
                'name' => 'Usuarios',
                'active' => request()->routeIs('empresas.usuarios.*') ? 'active' : '',
            ],
            [
                'existe' => $editPoliticasEmbarque && Route::has('empresas.politicas-embarque.edit'),
                'route' => Route::has('empresas.politicas-embarque.edit') ? route('empresas.politicas-embarque.edit') : '',
                'name' => 'Políticas de embarque',
                'active' => request()->routeIs('empresas.politicas-embarque.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($listViajes && Route::has('empresas.viajes.list')) or ($listProgramaciones && Route::has('empresas.programaciones.list')) or ($listTransportes && Route::has('empresas.transportes.list')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Operación de viajes',
        'icon' => 'nav-icon bi bi-bus-front',
        'list' => [
            [
                'existe' => $listViajes && Route::has('empresas.viajes.list'),
                'route' => Route::has('empresas.viajes.list') ? route('empresas.viajes.list') : '',
                'name' => 'Rutas de viaje',
                'active' => request()->routeIs('empresas.viajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listProgramaciones && Route::has('empresas.programaciones.list'),
                'route' => Route::has('empresas.programaciones.list') ? route('empresas.programaciones.list') : '',
                'name' => 'Programaciones',
                'active' => request()->routeIs('empresas.programaciones.*') ? 'active' : '',
            ],
            [
                'existe' => $listTransportes && Route::has('empresas.transportes.list'),
                'route' => Route::has('empresas.transportes.list') ? route('empresas.transportes.list') : '',
                'name' => 'Transportes',
                'active' => request()->routeIs('empresas.transportes.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($listReservas && Route::has('empresas.reservas.list')) or ($listPasajes && Route::has('empresas.pasajes.list')) or ($listValidacionPagos && Route::has('empresas.validacion-pagos.list')) or ($listReembolsos && Route::has('empresas.reembolsos.list')) or ($listReprogramaciones && Route::has('empresas.reprogramaciones.list')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Ventas y finanzas',
        'icon' => 'nav-icon bi bi-wallet2',
        'list' => [
            [
                'existe' => $listReservas && Route::has('empresas.reservas.list'),
                'route' => Route::has('empresas.reservas.list') ? route('empresas.reservas.list') : '',
                'name' => 'Reservas',
                'active' => request()->routeIs('empresas.reservas.*') ? 'active' : '',
            ],
            [
                'existe' => $listPasajes && Route::has('empresas.pasajes.list'),
                'route' => Route::has('empresas.pasajes.list') ? route('empresas.pasajes.list') : '',
                'name' => 'Pasajes',
                'active' => request()->routeIs('empresas.pasajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listValidacionPagos && Route::has('empresas.validacion-pagos.list'),
                'route' => Route::has('empresas.validacion-pagos.list') ? route('empresas.validacion-pagos.list') : '',
                'name' => 'Validación de pagos',
                'active' => request()->routeIs('empresas.validacion-pagos.*') ? 'active' : '',
            ],
            [
                'existe' => $listReembolsos && Route::has('empresas.reembolsos.list'),
                'route' => Route::has('empresas.reembolsos.list') ? route('empresas.reembolsos.list') : '',
                'name' => 'Reembolsos',
                'active' => request()->routeIs('empresas.reembolsos.*') ? 'active' : '',
            ],
            [
                'existe' => $listReprogramaciones && Route::has('empresas.reprogramaciones.list'),
                'route' => Route::has('empresas.reprogramaciones.list') ? route('empresas.reprogramaciones.list') : '',
                'name' => 'Reprogramaciones',
                'active' => request()->routeIs('empresas.reprogramaciones.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($listOrdenesCobro && Route::has('empresas.ordenes-cobro.list')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Cobranza',
        'icon' => 'nav-icon bi bi-receipt',
        'list' => [
            [
                'existe' => $listOrdenesCobro && Route::has('empresas.ordenes-cobro.list'),
                'route' => Route::has('empresas.ordenes-cobro.list') ? route('empresas.ordenes-cobro.list') : '',
                'name' => 'Órdenes de cobro',
                'active' => request()->routeIs('empresas.ordenes-cobro.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($listCupones && Route::has('empresas.cupones.list')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Promociones',
        'icon' => 'nav-icon bi bi-tags',
        'list' => [
            [
                'existe' => $listCupones && Route::has('empresas.cupones.list'),
                'route' => Route::has('empresas.cupones.list') ? route('empresas.cupones.list') : '',
                'name' => 'Cupones',
                'active' => request()->routeIs('empresas.cupones.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($listReporteVentas && Route::has('empresas.reportes.ventas')) or ($listReporteRutas && Route::has('empresas.reportes.rutas')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Reportes',
        'icon' => 'nav-icon bi bi-bar-chart',
        'list' => [
            [
                'existe' => $listReporteVentas && Route::has('empresas.reportes.ventas'),
                'route' => Route::has('empresas.reportes.ventas') ? route('empresas.reportes.ventas') : '',
                'name' => 'Ventas',
                'active' => request()->routeIs('empresas.reportes.ventas') ? 'active' : '',
            ],
            [
                'existe' => $listReporteRutas && Route::has('empresas.reportes.rutas'),
                'route' => Route::has('empresas.reportes.rutas') ? route('empresas.reportes.rutas') : '',
                'name' => 'Rutas',
                'active' => request()->routeIs('empresas.reportes.rutas') ? 'active' : '',
            ],
        ],
    ])
@endif

@if (($profileAccount && Route::has('empresas.account.profile')) or ($passwordAccount && Route::has('empresas.account.password')))
    @include('components.layout.sidebar-li', [
        'menu' => 'Mi cuenta',
        'icon' => 'nav-icon bi bi-person-circle',
        'list' => [
            [
                'existe' => $profileAccount && Route::has('empresas.account.profile'),
                'route' => Route::has('empresas.account.profile') ? route('empresas.account.profile') : '',
                'name' => 'Perfil',
                'active' => request()->routeIs('empresas.account.profile') ? 'active' : '',
            ],
            [
                'existe' => $passwordAccount && Route::has('empresas.account.password'),
                'route' => Route::has('empresas.account.password') ? route('empresas.account.password') : '',
                'name' => 'Contraseña',
                'active' => request()->routeIs('empresas.account.password') ? 'active' : '',
            ],
        ],
    ])
@endif
