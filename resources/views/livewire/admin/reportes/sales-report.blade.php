{{--
    REPORTE DE VENTAS
    --------------------------------------------------------------------------
    Presenta el resumen de reservas pagadas por fecha, sus ventas y tasas de servicio. Permite
    seleccionar el período y descargar los resultados.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-list.table />: Contenedor reutilizable de la tabla del listado.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reporte de ventas')

<div x-data="salesReport" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reporte de ventas
        </x-slot:title>

    </x-list.heading>

    <x-layout.error />
    <form x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row g-3 mb-3 align-items-end">

            <div class="col-md-6 col-xl-2">

                <x-form.text-input margin="0" type="date" name="date_from" wire:model.live="date_from">
                    Desde
                </x-form.text-input>

            </div>

            <div class="col-md-6 col-xl-2">

                <x-form.text-input margin="0" type="date" name="date_to" wire:model.live="date_to">
                    Hasta
                </x-form.text-input>

            </div>

            <div class="col-md-12 col-xl-auto ms-xl-auto text-md-end">
                <button type="button" class="btn btn-success" @click="preSave" :disabled="saving"
                    wire:loading.attr="disabled" wire:target="export">
                    <i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Descargar Excel
                </button>
            </div>

        </div>

    </form>
    <p class="text-body-secondary">Reservas con estado actual pagado, agrupadas por fecha de compra. Importes en.
    </p>

    <x-list.table>

        <thead>

            <tr>
                <th>Fecha</th>

                <th>Reservas pagadas</th>

                <th>Ventas</th>

                <th>Tasas</th>

            </tr>
        </thead>

        <tbody>

            @forelse($rows as $row)
                <tr>
                    <td>
                        {{ $row->fecha ?? '—' }}
                    </td>

                    <td>
                        {{ $row->cantidad ?? '—' }}
                    </td>

                    <td>
                        {{ number_format($row->total ?? 0, 2) }}
                    </td>

                    <td>
                        {{ number_format($row->tasas ?? 0, 2) }}
                    </td>

            </tr>@empty

                <tr>
                    <td colspan="4" class="text-center py-5">
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
        Alpine.data('salesReport', () => ({
            validator: null,
            saving: false,
            init() {
                this.toastCleanup = [
                    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
                    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
                ];
                const savedMessage = @js(session()->pull('admin_success'));
                if (savedMessage) this.$nextTick(() => Livewire.dispatch('successEventList', {
                    message: savedMessage
                }));
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

