<div class="d-flex align-items-center">

    <x-layout.header.name>
        Bienvenido <strong>{{ auth('admin')->user()->name ?? '' }}</strong>
    </x-layout.header.name>


    <x-layout.header.logout-button />

</div>
