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

@if ($listReservas or $listPasajes or $listRetiros or $listReembolsos or $listMovimientos)
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
                'existe' => $listRetiros ?? false,
                'route' => route('admin.retiros.list'),
                'name' => 'Retiros',
                'active' => request()->routeIs('admin.retiros.*') ? 'active' : '',
            ],
            [
                'existe' => $listReembolsos ?? false,
                'route' => route('admin.reembolsos.list'),
                'name' => 'Reembolsos',
                'active' => request()->routeIs('admin.reembolsos.*') ? 'active' : '',
            ],
            [
                'existe' => $listMovimientos ?? false,
                'route' => route('admin.movimientos.list'),
                'name' => 'Movimientos',
                'active' => request()->routeIs('admin.movimientos.*') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($listCampanas)
    @include('components.layout.sidebar-li', [
        'menu' => 'Promociones',
        'icon' => 'nav-icon bi bi-tags',
        'list' => [
            [
                'existe' => $listCampanas ?? false,
                'route' => route('admin.campanas.list'),
                'name' => 'Campañas',
                'active' => request()->routeIs('admin.campanas.*') ? 'active' : '',
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

@if ($salesReportes or $companiesReportes or $financeReportes or $listPagos)
    @include('components.layout.sidebar-li', [
        'menu' => 'Reportes',
        'icon' => 'nav-icon bi bi-bar-chart',
        'list' => [
            [
                'existe' => $listPagos ?? false,
                'route' => route('admin.pagos.list'),
                'name' => 'Pagos',
                'active' => request()->routeIs('admin.pagos.*') ? 'active' : '',
            ],
    
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
            [
                'existe' => $financeReportes ?? false,
                'route' => route('admin.reportes.finance'),
                'name' => 'Finanzas',
                'active' => request()->routeIs('admin.reportes.finance') ? 'active' : '',
            ],
        ],
    ])
@endif

@if ($generalSettings or $paymentsSettings or $withdrawalsSettings)
    @include('components.layout.sidebar-li', [
        'menu' => 'Configuración',
        'icon' => 'nav-icon bi bi-gear',
        'list' => [
            [
                'existe' => $generalSettings ?? false,
                'route' => route('admin.settings.general'),
                'name' => 'General',
                'active' => request()->routeIs('admin.settings.general') ? 'active' : '',
            ],
            [
                'existe' => $paymentsSettings ?? false,
                'route' => route('admin.settings.payments'),
                'name' => 'Pagos',
                'active' => request()->routeIs('admin.settings.payments') ? 'active' : '',
            ],
            [
                'existe' => $withdrawalsSettings ?? false,
                'route' => route('admin.settings.withdrawals'),
                'name' => 'Retiros',
                'active' => request()->routeIs('admin.settings.withdrawals') ? 'active' : '',
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
