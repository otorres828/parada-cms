@section('title', 'Pagos recibidos')

<div x-data="savePago" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Pagos recibidos @if ($pago_id)
                <small class="text-body-secondary">#{{ $pago_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('pagos', 'list'))
                <x-form.cancel-button :link="route('admin.pagos.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    <x-layout.error />
    <form id="savePagoForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <input type="search" class="form-control mb-2" placeholder="Buscar opciones..." aria-label="Buscar Reserva pagada"
                    wire:model.live.debounce.500ms="search_reserva_id">

                <x-form.dropdown label="Reserva pagada" name="reserva_id" x-model="$wire.reserva_id">

                    <option value="">Seleccionar...</option>
                    @foreach ($options_reserva_id as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach

                </x-form.dropdown>

                @error('reserva_id')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="text" name="referencia" x-model="$wire.referencia">
                    Referencia bancaria /
                    pasarela
                </x-form.text-input>

                @error('referencia')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Método" name="metodo" x-model="$wire.metodo">

                    <option value="">Seleccionar...</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="pasarela">Pasarela</option>
                    <option value="efectivo">Efectivo</option>

                </x-form.dropdown>

                @error('metodo')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="datetime-local" name="fecha_pago" x-model="$wire.fecha_pago">
                    Fecha de recepción
                </x-form.text-input>

                @error('fecha_pago')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.textarea name="comentario" x-model="$wire.comentario" rows="3">
                    Observaciones
                </x-form.textarea>

                @error('comentario')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <label class="form-label" for="savePago-proof">
                Comprobante (PDF, JPG o PNG, máximo 5 MB)
            </label>
            <input id="savePago-proof" name="comprobante" class="form-control" type="file" wire:model="comprobante"
                accept=".pdf,.jpg,.jpeg,.png">

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('savePago', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="reserva_id"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="referencia"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="metodo"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="fecha_pago"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="comentario"]'), [{
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
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
