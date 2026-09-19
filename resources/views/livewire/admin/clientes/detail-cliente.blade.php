@section('title', 'Clientes')

<div x-data="detailUser" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Clientes @if ($user_id)
                <small class="text-body-secondary">#{{ $user_id }}</small>

            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.clientes.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card">

                    <div class="card-body">

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">
                                {{ $user->name ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Apellido</dt>
                            <dd class="col-sm-8">
                                {{ $user->lastname ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Correo</dt>
                            <dd class="col-sm-8">
                                {{ $user->email ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Teléfono</dt>
                            <dd class="col-sm-8">
                                {{ $user->telefono ?? '—' }}
                            </dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8">
                                <x-list.status-badge :status="$user->status" />
                            </dd>
                        </dl>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card mt-4">

        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <span>Reservas pagadas y
                pendientes</span>

            <div style="width: 320px; max-width: 100%;">

                <label for="buscar-reservas" class="visually-hidden">
                    Buscar reserva
                </label>
                <input id="buscar-reservas" type="search" class="form-control" x-model.debounce.200ms="search"
                    placeholder="Buscar reserva">

            </div>

        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>Código</th>

                        <th>Empresa</th>

                        <th>Fecha de compra</th>

                        <th>Estado</th>

                        <th>Total USD</th>

                    </tr>
                </thead>

                <tbody>

                    @forelse($reservas as $reserva)

                        <tr data-search="{{ $reserva->codigo_referencia }} {{ $reserva->programacion?->viaje?->empresa?->nombre }} {{ $reserva->estado_pago }}"
                            x-show="matches($el.dataset.search)">
                            <td>

                                @if ($canReservasDetail)

                                    <a href="{{ route('admin.reservas.detail', $reserva->id) }}"
                                        wire:navigate>{{ $reserva->codigo_referencia }}</a>

                                @else

                                    {{ $reserva->codigo_referencia }}

                                @endif

                            </td>

                            <td>
                                {{ $reserva->programacion?->viaje?->empresa?->nombre }}
                            </td>

                            <td>
                                {{ $reserva->fecha_compra?->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                {{ $reserva->estado_pago === 'pagado' ? 'Pagada' : 'Pendiente' }}
                            </td>

                            <td>
                                {{ number_format($reserva->monto_total, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No hay reservas pagadas o pendientes.
                            </td>

                        </tr>

                    @endforelse

                    @if ($reservas->isNotEmpty())

                        <tr x-cloak x-show="search && !hasMatches()">
                            <td colspan="5" class="text-center py-4">
                                No hay coincidencias.
                            </td>

                        </tr>

                    @endif

                </tbody>
            </table>

        </div>

    </div>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('detailUser', () => ({
            search: '',
            normalize(value) {
                return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            },
            matches(value) {
                return this.normalize(value).includes(this.normalize(this.search.trim()));
            },
            hasMatches() {
                return [...this.$root.querySelectorAll('[data-search]')].some(row => this.matches(row.dataset.search));
            },
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
