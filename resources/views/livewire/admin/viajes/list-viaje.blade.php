@section('title', 'Rutas de viajes')

<div x-data="listViaje" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Rutas de viajes
        </x-slot:title>

       

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

            <label class="form-label" for="listViaje-status">
                Estado
            </label>

            <select id="listViaje-status" class="form-select" wire:model.live="status">

                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>

            </select>

        </div>

    </div>

    <x-list.table>

        <thead>

            <tr>
                <th>ID
                    <x-list.sortable-button column="id" :$sortColumn :$sortDirection />
                </th>

                <th>Empresa </th>

                <th>Origen </th>

                <th>Destino </th>

                <th>Duración
                    <x-list.sortable-button column="duracion_estimada" :$sortColumn :$sortDirection />
                </th>

                <th>Estado
                    <x-list.sortable-button column="estatus" :$sortColumn :$sortDirection />
                </th>

                <th>Tasas de servicio USD</th>

                <th class="text-end">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($viajes as $viaje)

                <tr wire:key="listViaje-{{ $viaje->id }}">

                    <td>{{ $viaje->id }}</td>

                    <td>{{ $viaje->empresa?->nombre ?? '—' }}</td>

                    <td>{{ $viaje->origenTerminal?->nombre ?? '—' }}</td>

                    <td>{{ $viaje->destinoTerminal?->nombre ?? '—' }}</td>

                    <td>{{ $viaje->duracion_estimada ?? '—' }}</td>

                    <td>
                        <x-list.status-badge :status="$viaje->estatus" />
                    </td>

                    <td>{{ number_format($viaje->tasas_servicio_total ?? 0, 2) }}</td>
                    
                    <td class="text-end">

                        <x-list.button-group>

                            @if ($canDetail)
                                <x-list.view-button :route="route('admin.viajes.detail', ['viaje_id' => $viaje->id])" :target="false" />
                            @endif

                        </x-list.button-group>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center py-5">No se encontraron registros.</td>
                </tr>

            @endforelse

        </tbody>

    </x-list.table>

    {{ $viajes->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listViaje', () => ({
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
