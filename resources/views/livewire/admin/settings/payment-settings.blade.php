@section('title', 'Métodos de pago')

<div x-data="paymentSettings" class="py-3">

    <x-form.title>
        Métodos de pago
    </x-form.title>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif
    <form id="paymentSettingsForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <x-form.dropdown label="Permitir transferencia" name="transferencia" x-model="$wire.transferencia">

                    <option value="">Seleccionar...</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>

                </x-form.dropdown>

                @error('transferencia')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Permitir tarjeta" name="tarjeta" x-model="$wire.tarjeta">

                    <option value="">Seleccionar...</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>

                </x-form.dropdown>

                @error('tarjeta')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Permitir pasarela" name="pasarela" x-model="$wire.pasarela">

                    <option value="">Seleccionar...</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>

                </x-form.dropdown>

                @error('pasarela')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Permitir efectivo" name="efectivo" x-model="$wire.efectivo">

                    <option value="">Seleccionar...</option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>

                </x-form.dropdown>

                @error('efectivo')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </x-form.container-sm>

        <hr><button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar
            configuración</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('paymentSettings', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="transferencia"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="tarjeta"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="pasarela"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="efectivo"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
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
