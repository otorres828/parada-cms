@section('title', 'Terminales')

<div x-data="saveTerminal" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Terminales @if ($terminal_id)
                <small class="text-body-secondary">#{{ $terminal_id }}</small>
            @endif

        </x-slot:title>

        <x-slot:button>

            @if (\App\Services\Admin\Access::allows('terminales', 'list'))
                <x-form.cancel-button :link="route('admin.terminales.list')">
                    Volver al listado
                </x-form.cancel-button>
            @endif

        </x-slot:button>

    </x-list.heading>

    @if ($errors->any())

        <div class="alert alert-danger" role="alert">

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif
    <form id="saveTerminalForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <x-form.container-sm>

            <div class="mb-3">

                <input type="search" class="form-control mb-2" placeholder="Buscar opciones..." aria-label="Buscar Estado"
                    wire:model.live.debounce.500ms="search_estado_id">

                <x-form.dropdown label="Estado" name="estado_id" x-model="$wire.estado_id">

                    <option value="">Seleccionar...</option>
                    @foreach ($options_estado_id as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach

                </x-form.dropdown>

                @error('estado_id')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="text" name="nombre" x-model="$wire.nombre">
                    Nombre
                </x-form.text-input>

                @error('nombre')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.textarea name="direccion" x-model="$wire.direccion" rows="3">
                    Dirección
                </x-form.textarea>

                @error('direccion')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="latitud" x-model="$wire.latitud" step="0.0000001">
                    Latitud
                </x-form.text-input>

                @error('latitud')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.text-input type="number" name="longitud" x-model="$wire.longitud" step="0.0000001">
                    Longitud
                </x-form.text-input>

                @error('longitud')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="mb-3">

                <x-form.dropdown label="Estado" name="estatus" x-model="$wire.estatus">

                    <option value="">Seleccionar...</option>
                    <option value="0">Inactivo</option>
                    <option value="1">Activo</option>

                </x-form.dropdown>

                @error('estatus')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </x-form.container-sm>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveTerminal', () => ({
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
                    this.validator.addField(this.$refs.form.querySelector('[name="estado_id"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="nombre"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="direccion"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 2000,
                        errorMessage: 'Máximo 2000 caracteres'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="latitud"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="longitud"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }]);
                    this.validator.addField(this.$refs.form.querySelector('[name="estatus"]'), [{
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
