@if ($listDashboard)
    @include('components.layout.sidebar-li', [
        'menu' => 'Dashboard',
        'icon' => 'nav-icon bi bi-speedometer2',
        'route' => route('admin.dashboard'),
        'active' => request()->routeIs('admin.dashboard') ? 'active' : '',
    ])
@endif

@if ($listAdmins or $listAuditoria or $listTasasServicio)
    @include('components.layout.sidebar-li', [
        'menu' => 'Administración',
        'icon' => 'nav-icon bi bi-shield-lock',
        'list' => [
            [
                'existe' => $listAdmins ?? false,
                'route' => route('admin.admins.list'),
                'name' => 'Administradores',
                'active' => request()->routeIs('admin.admins.*') ? 'active' : '',
            ],
            [
                'existe' => $listTasasServicio,
                'route' => route('admin.tasas-servicio.list'),
                'name' => 'Tasa de Servicio',
                'active' => request()->routeIs('admin.tasas-servicio.*') ? 'active' : '',
            ],
            [
                'existe' => $listAuditoria ?? false,
                'route' => route('admin.auditoria.list'),
                'name' => 'Auditoría',
                'active' => request()->routeIs('admin.auditoria.*') ? 'active' : '',
            ],
        ],
    ])
@endif



@if ($listEmpresas or $listUsers)
    @include('components.layout.sidebar-li', [
        'menu' => 'Empresas y clientes',
        'icon' => 'nav-icon bi bi-buildings',
        'list' => [
            [
                'existe' => $listEmpresas ?? false,
                'route' => route('admin.empresas.list'),
                'name' => 'Empresas',
                'active' => request()->routeIs('admin.empresas.*') ? 'active' : '',
            ],
            [
                'existe' => $listUsers ?? false,
                'route' => route('admin.clientes.list'),
                'name' => 'Clientes',
                'active' => request()->routeIs('admin.clientes.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listLegales)
    @include('components.layout.sidebar-li', [
        'menu' => 'Legales',
        'icon' => 'nav-icon bi bi-file-earmark-text',
        'list' => [
            [
                'existe' => $listLegales,
                'route' => route('admin.legales.list'),
                'name' => 'Legales',
                'active' => request()->routeIs('admin.legales.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listViajes or $listProgramaciones or $listAutobuses)
    @include('components.layout.sidebar-li', [
        'menu' => 'Operación de viajes',
        'icon' => 'nav-icon bi bi-bus-front',
        'list' => [
            [
                'existe' => $listViajes ?? false,
                'route' => route('admin.viajes.list'),
                'name' => 'Rutas de viajes',
                'active' => request()->routeIs('admin.viajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listProgramaciones ?? false,
                'route' => route('admin.programaciones.list'),
                'name' => 'Programaciones',
                'active' => request()->routeIs('admin.programaciones.*') ? 'active' : '',
            ],
            [
                'existe' => $listAutobuses ?? false,
                'route' => route('admin.autobuses.list'),
                'name' => 'Autobuses',
                'active' => request()->routeIs('admin.autobuses.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listReservas or $listPasajes or $listReembolsos)
    @include('components.layout.sidebar-li', [
        'menu' => 'Ventas y finanzas',
        'icon' => 'nav-icon bi bi-wallet2',
        'list' => [
            [
                'existe' => $listReservas ?? false,
                'route' => route('admin.reservas.list'),
                'name' => 'Reservas',
                'active' => request()->routeIs('admin.reservas.*') ? 'active' : '',
            ],
            [
                'existe' => $listPasajes ?? false,
                'route' => route('admin.pasajes.list'),
                'name' => 'Pasajes',
                'active' => request()->routeIs('admin.pasajes.*') ? 'active' : '',
            ],
            [
                'existe' => $listReembolsos ?? false,
                'route' => route('admin.reembolsos.list'),
                'name' => 'Reembolsos',
                'active' => request()->routeIs('admin.reembolsos.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listcupones)
    @include('components.layout.sidebar-li', [
        'menu' => 'Promociones',
        'icon' => 'nav-icon bi bi-tags',
        'list' => [
            [
                'existe' => $listcupones ?? false,
                'route' => route('admin.cupones.list'),
                'name' => 'Cupones',
                'active' => request()->routeIs('admin.cupones.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listTerminales or $listAmenidades)
    @include('components.layout.sidebar-li', [
        'menu' => 'Catálogos',
        'icon' => 'nav-icon bi bi-collection',
        'list' => [
            [
                'existe' => $listTerminales ?? false,
                'route' => route('admin.terminales.list'),
                'name' => 'Terminales',
                'active' => request()->routeIs('admin.terminales.*') ? 'active' : '',
            ],
            [
                'existe' => $listAmenidades ?? false,
                'route' => route('admin.amenidades.list'),
                'name' => 'Amenidades',
                'active' => request()->routeIs('admin.amenidades.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($salesReportes or $companiesReportes)
    @include('components.layout.sidebar-li', [
        'menu' => 'Reportes',
        'icon' => 'nav-icon bi bi-bar-chart',
        'list' => [
            [
                'existe' => $salesReportes ?? false,
                'route' => route('admin.reportes.sales'),
                'name' => 'Ventas',
                'active' => request()->routeIs('admin.reportes.sales') ? 'active' : '',
            ],
            [
                'existe' => $companiesReportes ?? false,
                'route' => route('admin.reportes.companies'),
                'name' => 'Empresas',
                'active' => request()->routeIs('admin.reportes.companies') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listSolicitudes)
    @include('components.layout.sidebar-li', [
        'menu' => 'Soporte',
        'icon' => 'nav-icon bi bi-headset',
        'list' => [
            [
                'existe' => $listSolicitudes ?? false,
                'route' => route('admin.solicitudes.list'),
                'name' => 'Solicitudes',
                'active' => request()->routeIs('admin.solicitudes.*') ? 'active' : '',
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
                'route' => route('admin.account.profile'),
                'name' => 'Perfil',
                'active' => request()->routeIs('admin.account.profile') ? 'active' : '',
            ],
            [
                'existe' => $passwordAccount ?? false,
                'route' => route('admin.account.password'),
                'name' => 'Contraseña',
                'active' => request()->routeIs('admin.account.password') ? 'active' : '',
            ],
        ],
    ])
@endif
