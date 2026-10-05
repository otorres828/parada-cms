{{--
    REPORTE DE RUTAS
    --------------------------------------------------------------------------
    Presenta el resumen de reservas pagadas por tramo contratado, sus ventas y tasas de servicio. Permite
    seleccionar el período y descargar los resultados.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Elemento de presentación del listado.
    - <x-slot />: Elemento de presentación del listado.
    - <x-layout.error />: Elemento de presentación del listado.
    - <x-form.text-input />: Elemento de presentación del listado.
    - <x-list.table />: Tabla propia del panel.
    - <x-money.dual />: Montos y su equivalente en bolívares.
    - <x-layout.loader.fullpage />: Elemento de presentación del listado.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reporte de rutas')

<div x-data="routesReport" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reporte de rutas
        </x-slot:title>

    </x-list.heading>

    <x-layout.error />

    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row g-3 mb-3 align-items-end">

            <div class="col-md-6 col-xl-2">

                <x-form.text-input margin="0" type="date" name="date_from" wire:model.live="date_from" min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}" >
                    Desde
                </x-form.text-input>

            </div>

            <div class="col-md-6 col-xl-2">

                <x-form.text-input margin="0" type="date" name="date_to" wire:model.live="date_to" min="{{ $this->getMinFilterDate() }}" max="{{ $this->getMaxFilterDate() }}" >
                    Hasta
                </x-form.text-input>

            </div>

            @if ($canDownload)

            <div class="col-md-12 col-xl-auto ms-xl-auto text-md-end">

                <button type="button" class="btn btn-success" @click="preSave" :disabled="saving"
                    wire:loading.attr="disabled" wire:target="export">
                    <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Descargar Excel
                </button>

            </div>

            @endif

        </div>

    </form>
    <p class="text-body-secondary">Reservas con estado actual pagado, agrupadas por origen y destino contratados. Importes en USD y Bs según la tasa histórica de cada reserva.
    </p>

    <x-list.table>

        <thead>

            <tr>
                <th>Origen</th>

                <th>Destino</th>

                <th>Reservas pagadas</th>

                <th>Ventas</th>

                @if ($viewTasaServicio)
                    <th>Tasas</th>
                @endif

            </tr>
        </thead>

        <tbody>

            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->origen }}</td>

                    <td>{{ $row->destino }}</td>

                    <td>
                        {{ $row->cantidad ?? '—' }}
                    </td>

                    @if ($viewTasaServicio)

                        <td>

                            <x-money.dual :usd="$row->total" :bs="$row->total_bs" />

                        </td>

                        <td>

                            <x-money.dual :usd="$row->tasas" :bs="$row->tasas_bs" />

                        </td>

                    @else

                        <td>

                            <x-money.dual :usd="$row->total - $row->tasas" :bs="$row->total_bs - $row->tasas_bs" />

                        </td>

                    @endif

            </tr>

            @empty

                <tr>
                    <td colspan="5" class="text-center py-5">
                        No hay datos para el período.
                    </td>

                </tr>
            @endforelse

        </tbody>

    </x-list.table>

    {{ $rows->links() }}

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('routesReport', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('empresas_routesreport_success', data => this.$store.toast.success(data.message)),
                    Livewire.on('empresas_routesreport_error', data => this.$store.toast.info(data.message)),
                ];
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    });
                    this.validator.addField(this.$refs.form.querySelector('[name="date_from"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="date_to"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('export');
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.toastCleanup?.forEach(cleanup => cleanup());
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
