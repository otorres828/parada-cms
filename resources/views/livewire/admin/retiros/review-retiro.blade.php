@section('title', 'Retiros')

<div x-data="reviewRetiro" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Retiros @if ($retiro_id)
                <small class="text-body-secondary">#{{ $retiro_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('retiros', 'list'))
                <x-form.cancel-button :link="route('admin.retiros.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />

    <div class="card row col-12">

        <div class="card-body col-md-6">

            <dl class="row mb-0">
                <dt class="col-sm-4">Empresa</dt>
                <dd class="col-sm-8">
                    {{ $retiro->empresa?->nombre ?? '—' }}
                </dd>
                <dt class="col-sm-4">Monto USD</dt>
                <dd class="col-sm-8">
                    {{ number_format($retiro->monto ?? 0, 2) }}
                </dd>
                <dt class="col-sm-4">Estado</dt>
                <dd class="col-sm-8">
                    <x-list.status-badge :status="$retiro->estatus" />
                </dd>
                <dt class="col-sm-4">Solicitado</dt>
                <dd class="col-sm-8">
                    {{ $retiro->created_at?->format('d/m/Y H:i') ?? '—' }}
                </dd>
                <dt class="col-sm-4">Referencia</dt>
                <dd class="col-sm-8">
                    {{ $retiro->referencia ?? '—' }}
                </dd>
                <dt class="col-sm-4">Datos bancarios</dt>
                <dd class="col-sm-8">{{ $retiro->datos_bancarios ?? '—' }}</dd>
                <dt class="col-sm-4">Observaciones</dt>
                <dd class="col-sm-8">{{ $retiro->comentario ?? '—' }}</dd>
                <dt class="col-sm-4">Fecha de resolución</dt>
                <dd class="col-sm-8">{{ $retiro->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
            </dl>

        </div>

    </div>

    <form x-ref="form" @submit.prevent="preSave" novalidate>
        <h4 class="h5">Resolución administrativa</h4>
        <p>Confirmar transferencia registra una operación bancaria ya realizada.</p>

        <x-form.container-sm>

            <x-form.dropdown label="Resolución" name="decision" x-model="$wire.decision">

                <option value="">Seleccionar...</option>
                @if ($retiro->estatus === 'pendiente')
                    <option value="aprobado">Aprobar solicitud</option>
                @endif
                @if ($retiro->estatus === 'aprobado')
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

                <input id="review-proof" name="comprobante" type="file" class="form-control mb-3" wire:model="comprobante"
                    accept=".pdf,.jpg,.jpeg,.png">

            </div>

        </x-form.container-sm>

        @if (in_array($retiro->estatus, ['pendiente', 'aprobado']))
            <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Registrar resolución</button>
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
        Alpine.data('reviewRetiro', () => ({
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
