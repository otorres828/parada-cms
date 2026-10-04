{{--
    PROGRAMACIÓN — ALTA Y EDICIÓN
    --------------------------------------------------------------------------
    Selecciona la ruta y el transporte; copia tarifas y propone horarios.
    Solo guarda los trayectos habilitados, sin modificar la plantilla del viaje.
    Componentes utilizados:
    - <x-list.heading /> y <x-form.cancel-button />: Encabezado y regreso.
    - <x-layout.error />: Validaciones del servidor.
    - <x-empresas.programaciones.tramos-form />: Tarifas y horarios por trayecto.
    - <x-layout.loader.fullpage />: Indicador de carga.
--}}

@section('title', $programacionId ? 'Editar programación' : 'Nueva programación')

<div class="py-3" x-data="saveProgramacion">

    <x-list.heading>

        <x-slot:title>
            {{ $programacionId ? 'Editar programación #'.$programacionId : 'Nueva programación' }}
        </x-slot:title>

        @if ($canList)
            <x-slot:button>
                <x-form.cancel-button :link="route('empresas.programaciones.list')">Volver al listado</x-form.cancel-button>
            </x-slot:button>
        @endif

    </x-list.heading>

    <x-layout.error />

    <form id="save-programacion" x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="card card-body mb-3">

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label" for="programacion-viaje">Ruta de viaje</label>

                    <select id="programacion-viaje" class="form-select" wire:model.live="viajeId" required>
                        <option value="">Seleccionar ruta</option>
                        @foreach ($viajes as $viaje)
                            <option value="{{ $viaje->id }}">{{ $viaje->origenTerminal?->nombre }} → {{ $viaje->destinoTerminal?->nombre }}</option>
                        @endforeach
                    </select>

                </div>

                <div class="col-md-6">

                    <label class="form-label" for="programacion-transporte">Transporte</label>

                    <select id="programacion-transporte" class="form-select" wire:model="transporteId" required>
                        <option value="">Seleccionar transporte</option>
                        @foreach ($transportes as $transporte)
                            <option value="{{ $transporte->id }}">{{ $transporte->modelo }} · {{ $transporte->placa }} · {{ $transporte->total_asientos }} puestos</option>
                        @endforeach
                    </select>

                </div>

                <div class="col-md-4">

                    <label class="form-label" for="programacion-fecha">Fecha inicial</label>
                    <input id="programacion-fecha" type="date" class="form-control" wire:model="fechaSalida" min="{{ today()->format('Y-m-d') }}" max="2100-12-31" required>

                </div>

                <div class="col-md-3">

                    <label class="form-label" for="programacion-hora">Hora inicial</label>
                    <input id="programacion-hora" type="time" class="form-control" wire:model="horaSalida" required>

                </div>

                <div class="col-md-5 d-flex align-items-end">

                    <button type="button" class="btn btn-outline-primary" wire:click="cargarTramos" wire:loading.attr="disabled" @disabled($viajeId === '')>
                        Recalcular horarios sugeridos
                    </button>

                </div>

            </div>

            <p class="text-muted small mb-0 mt-3">Los horarios se calculan desde la salida inicial y las duraciones de la ruta. Puedes ajustarlos en cada trayecto. Recalcular reemplaza esos horarios y conserva los precios.</p>

        </div>

        <x-empresas.programaciones.tramos-form :trayectos="$trayectos" :tramos="$tramos" />

        <div class="card card-body mt-3">

            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">

                <div>

                    <label class="form-label" for="programacion-estatus">Estatus</label>
                    <select id="programacion-estatus" class="form-select" wire:model="estatus">
                        <option value="1">Programada</option>
                        <option value="2">Inactiva</option>
                    </select>

                </div>

                <button type="submit" class="btn btn-primary" :disabled="saving" wire:loading.attr="disabled">Guardar programación</button>

            </div>

        </div>

    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveProgramacion', () => ({
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
                    const rules = [{ rule: 'required', errorMessage: 'Este campo es obligatorio.' }];
                    if (field.type === 'number') {
                        rules.push({ validator: value => Number.isFinite(Number(value)) && Number(value) >= Number(field.min) && Number(value) <= Number(field.max), errorMessage: 'Ingresa un valor dentro del rango permitido.' });
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
