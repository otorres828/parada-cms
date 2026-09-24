{{--
    REEMBOLSOS — FORMULARIO
    --------------------------------------------------------------------------
    Permite registrar una solicitud de reembolso vinculada a un pago, con su motivo y
    validaciones.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-form.textarea />: Campo de texto de varias líneas.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reembolsos')

<div x-data="saveReembolso" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Reembolsos @if ($reembolso_id)
                <small class="text-body-secondary">#{{ $reembolso_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('empresas.reembolsos.list')">
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="saveReembolsoForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <input type="search" class="form-control mb-2" placeholder="Buscar opciones..."
                    aria-label="Buscar Pago recibido"
                    wire:model.live.debounce.500ms="search_pago_reserva_id">

                <x-form.dropdown label="Pago recibido" name="pago_reserva_id" x-model="$wire.pago_reserva_id">

                    <option value="">Seleccionar...</option>

                    @foreach ($options_pago_reserva_id as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach

                </x-form.dropdown>

                @error('pago_reserva_id')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.textarea name="motivo" x-model="$wire.motivo" rows="3">
                    Motivo del reembolso total
                </x-form.textarea>

                @error('motivo')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <p>El reembolso es por el total del pago y reserva el neto correspondiente a la empresa.</p>

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving"
            wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveReembolso', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="pago_reserva_id"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="motivo"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
                    }, {
                        rule: 'minLength',
                        value: 10,
                        errorMessage: 'Mínimo 10 caracteres'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('save');
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
