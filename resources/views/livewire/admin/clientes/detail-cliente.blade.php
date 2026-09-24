{{--
    CLIENTES — DETALLE
    --------------------------------------------------------------------------
    Muestra los datos del cliente y sus reservas pagadas y pendientes. Incluye búsqueda local en
    la tabla y enlaces a las reservas autorizadas.

    Componentes reutilizables utilizados:
    - <x-clientes.description />: Ficha descriptiva del cliente.
    - <x-clientes.reservas-table />: Tabla de reservas del cliente.
    - <x-form.cancel-button />: Enlace para regresar al listado anterior.
    - <x-layout.loader.fullpage />: Indicador global durante operaciones de Livewire.
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    --------------------------------------------------------------------------
--}}

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

                    <x-clientes.description :user="$user" />

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
                    placeholder="Buscar en esta página: reserva">

            </div>

        </div>

        <div class="table-responsive">

            <x-clientes.reservas-table :reservas="$reservas" :can-reservas-detail="$canReservasDetail" />

        </div>

    </div>

    {{ $reservas->links() }}

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
                return [...this.$root.querySelectorAll('[data-search]')].some(row => this.matches(row.dataset
                    .search));
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




