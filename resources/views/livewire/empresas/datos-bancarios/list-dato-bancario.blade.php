{{--
    DATOS BANCARIOS — EMPRESAS
    Cuentas y pagos móviles propios, con búsqueda, estatus y paginación.
    Componentes: x-list.heading, x-list.actions, x-list.search-input, x-layout.loader.fullpage.
--}}
@section('title', 'Datos Bancarios')

<div class="py-3" x-data="listDatos">

    <x-list.heading>

        <x-slot:title>
            Datos Bancarios
        </x-slot:title>

    </x-list.heading>

    <x-list.actions>

        <x-slot:search>

            <x-list.search-input wire:model.live.debounce.1200ms="search" />

        </x-slot:search>

        <x-slot:group>

            <select aria-label="Estatus" class="form-select w-auto" wire:model.live="status">
                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
            </select>

            @if ($canAdd)

                <a class="btn btn-primary text-nowrap"
                    href="{{ route('empresas.datos-bancarios.add') }}"
                    wire:navigate>Nuevo registro
                </a>

             @endif

        </x-slot:group>

    </x-list.actions>

    <div class="card table-responsive">

        <table class="table mb-0">

            <thead>
                <tr>
                    <th>
                        Tipo
                    </th>

                    <th>
                        Banco
                    </th>

                    <th>
                        Titular
                    </th>

                    <th>
                        Documento
                    </th>

                    <th>
                        Cuenta o teléfono
                    </th>

                    <th>
                        Estatus
                    </th>

                    <th></th>

                </tr>

            </thead>

            <tbody>

                @forelse ($cuentas as $cuenta)

                    <tr>
                        <td>{{ $cuenta->getTipo() }}</td>

                        <td>{{ $cuenta->banco }}</td>

                        <td>{{ $cuenta->nombre_titular }}</td>

                        <td>{{ $cuenta->numero_documento }}</td>

                        <td>{{ $cuenta->numero_cuenta_telefono }}</td>

                        <td>{{ $cuenta->estatus === 1 ? 'Activo' : 'Inactivo' }}</td>

                        <td class="text-end">
                            @if ($canEdit)
                                <a class="btn btn-outline-secondary btn-sm"
                                    href="{{ route('empresas.datos-bancarios.edit', $cuenta->id) }}" wire:navigate
                                    aria-label="Editar"><i class="bi bi-pencil"></i></a>
                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7">No hay cuentas registradas.</td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{ $cuentas->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('listDatos', () => ({
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
            },
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('datos_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
            },

        }));
    </script>
@endscript
