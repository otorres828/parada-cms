{{--
    LEGALES — LISTADO
    --------------------------------------------------------------------------
    Muestra las empresas en tarjetas para localizar y abrir sus expedientes documentales.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-list.actions />: Contenedor del buscador y las acciones del listado.
    - <x-list.search-input />: Buscador vinculado al estado del listado.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Legales')

<div x-data="listLegal" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Legales
        </x-slot:title>

        <x-slot:button>

        </x-slot:button>

    </x-list.heading>

    <p class="text-body-secondary">Selecciona una empresa para consultar sus contratos, acuerdos, renovaciones y licencias.</p>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-4">

        @forelse($empresas as $empresa)

            <div class="col-md-6 col-xl-4" wire:key="legal-empresa-{{ $empresa->id }}">

                <div class="card h-100">

                    <div class="card-body d-flex flex-column gap-2">

                        <div class="d-flex justify-content-between align-items-start">
                            <i class="bi bi-building fs-2 text-primary" aria-hidden="true"></i>
                            <x-list.status-badge :status="$empresa->estatus" />

                        </div>

                        <h2 class="h5 mb-0 text-break">{{ $empresa->nombre }}</h2>

                        <div class="text-body-secondary">
                            RIF: {{ $empresa->rif }}
                        </div>

                        <div class="text-body-secondary">
                            {{ $empresa->documentos_legales_count }} documento(s)
                        </div>

                        @if ($canDetail)

                            <a class="btn btn-outline-primary mt-auto align-self-start"
                                href="{{ route('admin.legales.detail', $empresa->id) }}" wire:navigate><i
                                    class="bi bi-folder2-open me-1"></i>Ver documentos</a>

                        @endif

                    </div>

                </div>

            </div>

        @empty

            <div class="col-12">

                <div class="card card-body text-center py-5">
                    No se encontraron empresas.
                </div>

            </div>

        @endforelse

    </div>

    {{ $empresas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listLegal', () => ({}));
    </script>
@endscript
