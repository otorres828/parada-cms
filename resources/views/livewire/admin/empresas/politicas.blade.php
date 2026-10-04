@section('title', 'Políticas de la empresa')

<div class="py-3">

    <x-list.heading>

        <x-slot:title>
            Políticas de {{ $empresa->nombre }}
        </x-slot:title>

        <x-slot:button>

            <a class="btn btn-outline-secondary" href="{{ route('admin.empresas.detail', $empresa->id) }}"
                wire:navigate>
                Volver a la empresa
            </a>

        </x-slot:button>

    </x-list.heading>

    <div class="card card-body">

        <h2 class="h5">
            Condiciones para pasajeros: embarque y desembarque
        </h2>

        <div style="">

            {!! $empresa->politicas ?: 'La empresa aún no ha registrado sus políticas.' !!}

        </div>

    </div>

</div>
