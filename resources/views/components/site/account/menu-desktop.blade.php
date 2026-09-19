<!-- Sidebar Navigation -->
<aside
    class="hidden md:flex flex-col bg-surface-container border-r border-white/5 fixed left-0 top-0 h-full w-64 z-40 p-md pt-24 gap-sm">
    <div class="px-4">
        <h2 class="font-headline-md text-[20px] font-bold text-primary">{{ $user->name }}</h2>
    </div>

    <nav class="flex flex-col gap-1">

        @include('components.site.menu-item', [
            'label' => 'En Vivo',
            'icon' => 'sensors',
            'route' => route('account.dashboard'),
            'isActive' => request()->is('cuenta/en-vivo') or request()->is('cuenta/en-vivo/*')
        ])

        @include('components.site.menu-item', [
            'label' => 'Movimientos',
            'icon' => 'add_card',
            'route' => route('account.movements'),
            'isActive' => request()->is('cuenta/movimientos') or request()->is('cuenta/movimientos/*')
        ])

        @include('components.site.menu-item', [
            'label' => 'Mis Peleas',
            'icon' => 'receipt_long',
            'route' => route('account.my_fights'),
            'isActive' => request()->is('cuenta/mis-peleas')
        ])

        @include('components.site.menu-item', [
            'label' => 'Eventos Pasados',
            'icon' => 'history',
            'route' => route('account.events'),
            'isActive' => request()->is('cuenta/eventos') or request()->is('cuenta/eventos/*')

        ])

    </nav>

    <livewire:site.menu.logout />

</aside>
