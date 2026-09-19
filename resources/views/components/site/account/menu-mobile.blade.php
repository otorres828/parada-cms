<!-- Mobile Navigation Bar -->
<nav class="md:hidden fixed bottom-0 left-0 w-full h-16 bg-surface-container border-t border-white/5 flex items-center justify-around z-50">


    @include('components.site.account.menu-mobile-item',[
        'icon' => 'sensors',
        'title' => 'En Vivo',
        'route' => route('account.dashboard'),
        'liveActive' => request()->is('cuenta/en-vivo')
    ])

    @include('components.site.account.menu-mobile-item',[
        'icon' => 'add_card',
        'title' => 'Movimientos',
        'route' => route('account.movements'),
        'liveActive' => request()->is('cuenta/movimientos')
    ])

    @include('components.site.account.menu-mobile-item',[
        'icon' => 'receipt_long',
        'title' => 'Mis Peleas',
        'route' => route('account.my_fights'),
        'liveActive' => request()->is('cuenta/mis-peleas') or request()->is('cuenta/mis-peleas/*')
    ])

    @include('components.site.account.menu-mobile-item',[
        'icon' => 'history',
        'title' => 'Eventos Pasados',
        'route' => route('account.events'),
        'liveActive' => request()->is('cuenta/eventos') or request()->is('cuenta/eventos/*')
    ])

</nav>
