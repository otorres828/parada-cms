@section('title', 'Cambiar contraseña')

<div x-data="password" class="py-3">

    <x-form.title>
        Cambiar contraseña
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
    <form id="passwordForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <x-form.text-input type="password" name="password" x-model="$wire.password" autocomplete="new-password">
                    Nueva
                    contraseña
                </x-form.text-input>

                @error('password')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="password" name="password_confirmation" x-model="$wire.password_confirmation"
                    autocomplete="new-password">
                    Confirmar contraseña
                </x-form.text-input>

                @error('password_confirmation')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="password" name="current_password" x-model="$wire.current_password" autocomplete="new-password">
                    Contraseña actual
                </x-form.text-input>

                @error('current_password')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </x-form.container-sm>

        <hr><button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar cambios</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('password', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="password"]'), [{
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }, {
                        rule: 'minLength',
                        value: 10,
                        errorMessage: 'Mínimo 10 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="password_confirmation"]'), [{
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }, {
                        rule: 'minLength',
                        value: 10,
                        errorMessage: 'Mínimo 10 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="current_password"]'), [{
                        validator: () => true,
                        errorMessage: 'Revisa este campo'
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
