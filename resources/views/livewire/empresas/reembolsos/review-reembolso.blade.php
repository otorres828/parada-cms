{{--
    REEMBOLSOS — REVISIÓN
    --------------------------------------------------------------------------
    Permite revisar una solicitud de reembolso y registrar la decisión, observaciones y datos de
    comprobación requeridos.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-list.status-badge />: Etiqueta visual del estado del registro.
    - <x-form.container-sm />: Contenedor de ancho limitado para los campos.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-form.textarea />: Campo de texto de varias líneas.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    --------------------------------------------------------------------------
--}}

@section('title', 'Reembolsos')

<div x-data="reviewReembolso" class="py-3">

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

    <div class="card">

        <div class="card-body">

            <dl class="row mb-0">
                <dt class="col-sm-4">Pago</dt>
                <dd class="col-sm-8">
                    {{ $reembolso->pagoReserva?->referencia_pago ?? '—' }}
                </dd>
                <dt class="col-sm-4">Empresa</dt>
                <dd class="col-sm-8">
                    {{ $reembolso->empresa?->nombre ?? '—' }}
                </dd>
                <dt class="col-sm-4">Monto</dt>
                <dd class="col-sm-8">
                    {{ number_format($reembolso->monto ?? 0, 2) }}
                </dd>
                <dt class="col-sm-4">Estado</dt>
                <dd class="col-sm-8">
                    <x-list.status-badge :status="$reembolso->estatus" />
                </dd>
                <dt class="col-sm-4">Solicitado</dt>
                <dd class="col-sm-8">
                    {{ $reembolso->created_at?->format('d/m/Y H:i') ?? '—' }}
                </dd>
                <dt class="col-sm-4">Motivo</dt>
                <dd class="col-sm-8">{{ $reembolso->motivo ?? '—' }}</dd>
                <dt class="col-sm-4">Observaciones</dt>
                <dd class="col-sm-8">{{ $reembolso->comentario ?? '—' }}</dd>
                <dt class="col-sm-4">Fecha de resolución</dt>
                <dd class="col-sm-8">{{ $reembolso->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
            </dl>

        </div>

    </div>

    <form x-ref="form" @submit.prevent="preSave" novalidate>
        <h4 class="h5">Resolución administrativa</h4>
        <p>Confirmar transferencia registra una operación bancaria ya realizada.</p>

        <x-form.container-sm>

            <x-form.dropdown label="Resolución" name="decision" x-model="$wire.decision">

                <option value="">Seleccionar...</option>

                @if ($reembolso->estatus === 'pendiente')
                    <option value="aprobado">Aprobar solicitud</option>
                @endif

                @if ($reembolso->estatus === 'aprobado')
                    <option value="pagado">Confirmar transferencia realizada</option>
                @endif

                <option value="rechazado">Rechazar solicitud</option>

            </x-form.dropdown>

            <x-form.textarea name="comentario" x-model="$wire.comentario">
                Motivo / observaciones
            </x-form.textarea>

            <div x-show="$wire.decision === 'pagado'">

                <x-form.text-input name="referencia" x-model="$wire.referencia">
                    Referencia de la transferencia
                </x-form.text-input>

                <label class="form-label" for="review-proof">
                    Comprobante PDF o imagen (máximo 5 MB)
                </label>

                <input id="review-proof" name="comprobante" type="file" class="form-control mb-3"
                    wire:model="comprobante"
                    accept=".pdf,.jpg,.jpeg,.png">

            </div>

        </x-form.container-sm>

        @if (in_array($reembolso->estatus, ['pendiente', 'aprobado']))
            <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Registrar
                resolución</button>
        @else
            <div class="alert alert-info">
                Esta solicitud ya fue resuelta.
            </div>
        @endif

    </form>
    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('reviewReembolso', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="decision"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="comentario"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
                    }, {
                        rule: 'minLength',
                        value: 5,
                        errorMessage: 'Mínimo 5 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="referencia"]'), [{
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('resolve');
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

