@section('title', 'Pasajes')

<div x-data="listPasaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pasajes
        </x-slot:title>

        <x-slot:button>

            @if (Route::has('admin.pasajes.add') && $canAdd)
                <x-list.add-button :route="route('admin.pasajes.add')">
                    Nuevo registro
                </x-list.add-button>
            @endif

        </x-slot:button>

    </x-list.heading>



    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

        </x-slot:group>

    </x-list.actions>

    <div class="row g-3 mb-3">

        <div class="col-md-3">

            <label class="form-label" for="filtro-empresa">
                Empresa
            </label>

            <select id="filtro-empresa" class="form-select" wire:model.live="empresa_id">

                <option value="">Todas las empresas</option>
                @foreach ($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach

            </select>

        </div>

        <div class="col-md-3">

            <label class="form-label" for="listPasaje-from">
                Desde
            </label>
            <input id="listPasaje-from" type="date" class="form-control" wire:model.live="date_from">
        </div>

        <div class="col-md-3">

            <label class="form-label" for="listPasaje-to">
                Hasta
            </label>
            <input id="listPasaje-to" type="date" class="form-control" wire:model.live="date_to">
        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>
                <th>Reserva </th>
                <th>Viajero </th>
                <th>Documento </th>
                <th>Asiento
                    <x-list.sortable-button column="numero_asiento" :$sortColumn :$sortDirection />
                </th>
                <th>Precio USD
                    <x-list.sortable-button column="precio_final" :$sortColumn :$sortDirection />
                </th>
                <th>Abordado
                    <x-list.sortable-button column="abordado" :$sortColumn :$sortDirection />
                </th>
                <th>Tasa de servicio USD</th>
                <th>Pago </th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pasajes as $pasaje)

                <tr wire:key="listPasaje-{{ $pasaje->id }}">
                    <td>{{ $pasaje->id }}</td>
                    <td>{{ $pasaje->reserva?->codigo_referencia ?? '—' }}</td>
                    <td>{{ $pasaje->viajero?->nombre ?? '—' }}</td>
                    <td>{{ $pasaje->viajero?->documento_identidad ?? '—' }}</td>
                    <td>{{ $pasaje->numero_asiento ?? '—' }}</td>
                    <td>{{ number_format($pasaje->precio_final ?? 0, 2) }}</td>
                    <td>{{ $pasaje->abordado ? 'Sí' : 'No' }}</td>
                    <td>{{ number_format($pasaje->tasa_servicio, 2) }}</td>
                    <td>
                        <x-list.status-badge :status="$pasaje->reserva->estado_pago" />
                    </td>
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($capabilities['detail'])
                                <x-list.view-button :route="route('admin.pasajes.detail', ['pasaje_id' => $pasaje->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>
                </tr>
            @empty

                <tr>
                    <td colspan="10" class="text-center py-5">No se encontraron registros.</td>
                </tr>
            @endforelse
        </tbody>

    </x-list.table>

    {{ $pasajes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listPasaje', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
