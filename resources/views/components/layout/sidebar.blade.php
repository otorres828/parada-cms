{{--
    SIDEBAR — SELECTOR DE PANEL
    Comparte el logo entre paneles y selecciona los menús
    de Admin en /admin y de Empresas en /empresa.
    Componentes: x-layout.sidebar.logo,
    x-layout.sidebar.administration-menu-admin y x-layout.sidebar.administration-menu-empresa.
--}}

<aside class="app-sidebar shadow" data-bs-theme="dark" onclick="event.stopPropagation();">

    <x-layout.sidebar.logo />

    <div class="sidebar-wrapper">

        <nav class="mt-2">

            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Navegación principal" data-accordion="false" id="navigation">

                @if (request()->is('admin', 'admin/*'))
                    <x-layout.sidebar.administration-menu-admin />
                @elseif (request()->is('empresa', 'empresa/*'))
                    <x-layout.sidebar.administration-menu-empresa />
                @endif

            </ul>

        </nav>

    </div>

</aside>
