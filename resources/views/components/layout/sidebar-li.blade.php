@php
    $show = '';
    $isActive = false;

    if (isset($list)) {
        foreach ($list as $item) {
            if (($item['active'] ?? '') === 'active') {
                $isActive = true;
                $show = 'menu-open';
                break;
            }
        }
    } else {
        $isActive = !empty($active);
    }
@endphp

<li class="nav-item {{ $show }}">

    <a href="{{ isset($list) ? '#' : $route }}" class="nav-link {{ $isActive ? 'active-li' : '' }}" >
        <i class="{{ $icon }}"></i>
        <p>
            {{ $menu }}
            @if (isset($list))
            <i class="nav-arrow bi bi-chevron-right"></i>
            @endif
        </p>
    </a>

    @if (isset($list))

        <ul class="nav nav-treeview">


            @foreach ($list as $item)

                @if (isset($item['existe']) and $item['existe'])

                    <li class="nav-item">

                        <a href="{{ $item['route'] }}" class="nav-link  {{ $item['active'] }}" wire:navigate>

                            <p>{{ $item['name'] }}</p>

                        </a>

                    </li>

                @endif

            @endforeach


        </ul>

    @endif
</li>
