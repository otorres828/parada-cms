{{--
    RUTA DE VIAJE — ALTA Y EDICIÓN
    --------------------------------------------------------------------------
    Define un recorrido ordenado y sus tarifas base por combinación.
    Componentes utilizados:
    - <x-list.heading /> y <x-form.cancel-button />: Título y regreso.
    - <x-layout.error />: Validaciones del servidor.
    - <x-empresas.viajes.recorrido-form />: Selección y recorrido visual de paradas.
    - <x-empresas.viajes.tarifas-form />: Precios independientes por trayecto.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}

@section('title', $viajeId ? 'Editar ruta de viaje' : 'Nueva ruta de viaje')

<div class="py-3" x-data="saveViaje">

    <x-list.heading>

        <x-slot:title>
            {{ $viajeId ? 'Editar ruta #'.$viajeId : 'Nueva ruta de viaje' }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button
                :link="route('empresas.viajes.list')"
            >
                Volver al listado
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <p class="text-muted">Define las paradas en orden y el precio base de cada trayecto. La última parada será el destino final.</p>

    <x-layout.error />

    <form id="save-viaje" x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="row g-4">

            <div class="col-lg-5">

                <x-empresas.viajes.recorrido-form
                    :terminales="$terminales"
                    :paradas="$paradas"
                    :viaje-id="$viajeId"
                    :estados="$estados"
                    :terminales-origen="$terminalesOrigen"
                    :terminales-parada="$terminalesParada"
                />

            </div>

            <div class="col-lg-7">

                <x-empresas.viajes.tarifas-form
                    :terminales="$terminales"
                    :combinaciones="$combinaciones"
                />

                <div class="card card-body mt-3">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label" for="viaje-estatus">Estatus</label>

                            <select id="viaje-estatus" class="form-select" wire:model="estatus" required>
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
                            </select>

                        </div>

                        <div class="col-md-8">

                            <label class="form-label" for="viaje-comentario">Comentario (opcional)</label>

                            <textarea id="viaje-comentario" class="form-control" wire:model="comentario" maxlength="5000" rows="3"></textarea>

                        </div>

                    </div>

                    <div class="border-top pt-3 mt-3 text-end">

                        <button class="btn btn-primary" type="submit" :disabled="saving" wire:loading.attr="disabled">
                            Guardar ruta
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

    <x-layout.loader.fullpage
        wire:loading.delay.short
    />

</div>

@script
    <script>
        Alpine.data('saveViaje', () => ({
            saving: false,
            validator: null,
            init() {
                this.cleanup = Livewire.on('successEventList', data => this.$store.toast.success(data.message));
            },
            async preSave() {
                if (this.saving) return;
                this.validator?.destroy();
                this.validator = new JustValidate(this.$refs.form, {
                    errorLabelCssClass: ['invalid-feedback'],
                    errorFieldCssClass: ['is-invalid'],
                });
                this.$refs.form.querySelectorAll('[required]:not([disabled])').forEach(field => {
                    const rules = [{
                        rule: 'required',
                        errorMessage: 'Este campo es obligatorio.',
                    }];
                    if (field.type === 'number') {
                        rules.push({
                            validator: value => Number.isFinite(Number(value)) && Number(value) >= Number(field.min) && Number(value) <= Number(field.max),
                            errorMessage: 'Ingresa un valor dentro del rango permitido.',
                        });
                    }
                    this.validator.addField(field, rules);
                });
                this.validator.isSubmitted = true;
                if (!await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await this.$wire.save();
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.validator?.destroy();
                this.cleanup?.();
            },
        }));
    </script>
@endscript
