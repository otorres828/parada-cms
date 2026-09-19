@section('title', 'Documentos legales')

<div x-data="empresaLegal" class="py-3">

    <x-list.heading>

        <x-slot:title>
            Legales · {{ $empresa->nombre }}
        </x-slot:title>

        <x-slot:button>

            <x-form.cancel-button :link="route('admin.legales.list')">
                Volver a empresas
            </x-form.cancel-button>

        </x-slot:button>

    </x-list.heading>

    <div class="container-fluid px-0 mb-4">

        <div class="row">

            <div class="col-md-6">

                <div class="card card-body">

                    <dl class="row mb-0">
                        <dt class="col-sm-4">Empresa</dt>
                        <dd class="col-sm-8">{{ $empresa->nombre }}</dd>
                        <dt class="col-sm-4">RIF</dt>
                        <dd class="col-sm-8">{{ $empresa->rif }}</dd>
                        <dt class="col-sm-4">Correo</dt>
                        <dd class="col-sm-8 text-break">{{ $empresa->email }}</dd>
                    </dl>

                </div>

            </div>

        </div>

    </div>

    @if ($canAdd)

        <div class="card mb-4">

            <div class="card-header">
                Subir documento
            </div>

            <div class="card-body">

                <form x-ref="form" @submit.prevent="preSave" novalidate>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <x-form.text-input name="titulo" x-model="$wire.titulo">
                                Título del
                                documento
                            </x-form.text-input>

                            @error('titulo')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6">

                            <x-form.dropdown label="Tipo de documento" name="tipo" x-model="$wire.tipo">

                                @foreach ($tipos as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach

                            </x-form.dropdown>

                            @error('tipo')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6">

                            <label for="legal-archivo" class="form-label">
                                Archivo
                            </label>
                            <input id="legal-archivo" name="archivo" x-ref="archivo" type="file" class="form-control"
                                wire:model="archivo" accept="application/pdf,image/jpeg,image/png,image/webp"
                                x-on:livewire-upload-start="uploading=true" x-on:livewire-upload-finish="uploading=false"
                                x-on:livewire-upload-error="uploading=false; $store.toast.info('No se pudo cargar el archivo. Revisa el formato y el tamaño.')"
                                x-on:livewire-upload-cancel="uploading=false">

                            <div class="form-text">
                                PDF, JPG, PNG o WebP. Máximo 10 MB.
                            </div>

                            @error('archivo')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-md-6">

                            <label for="legal-observaciones" class="form-label">
                                Observaciones
                            </label>

                            <textarea id="legal-observaciones" name="observaciones" class="form-control" rows="3" x-model="$wire.observaciones"></textarea>
                            @error('observaciones')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" :disabled="saving || uploading" wire:loading.attr="disabled"><i
                                class="bi bi-upload me-1"></i>Guardar documento</button><span x-show="uploading" x-cloak
                            class="text-body-secondary ms-2">Cargando archivo…</span>
                    </div>

                </form>

            </div>

        </div>

    @endif

    <div class="card">

        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <span>Documentos de la empresa</span>

            <div style="width:320px;max-width:100%">

                <label class="visually-hidden" for="legal-search">
                    Buscar documentos
                </label>
                <input id="legal-search" type="search" class="form-control" placeholder="Buscar documentos"
                    wire:model.live.debounce.500ms="search">
            </div>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">

                    <label class="form-label" for="legal-tipo">
                        Tipo de documento
                    </label>

                    <select id="legal-tipo" class="form-select" wire:model.live="tipo_filtro">

                        <option value="">Todos</option>
                        @foreach ($tipos as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach

                    </select>

                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead>

                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Archivo</th>
                        <th>Cargado por</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($documentos as $documento)

                        <tr wire:key="legal-doc-{{ $documento->id }}">
                            <td class="text-break"><strong>{{ $documento->titulo }}</strong>
                                @if ($documento->observaciones)
                                    <div class="text-body-secondary small">
                                        {{ $documento->observaciones }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $tipos[$documento->tipo] ?? $documento->tipo }}</td>
                            <td class="text-break"><i
                                    class="bi {{ $documento->mime === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }} me-1"></i>{{ $documento->nombre_original }}

                                <div class="small text-body-secondary">
                                    {{ number_format($documento->tamano / 1024, 1) }} KB
                                </div>

                            </td>
                            <td>{{ $documento->admin?->name ?? 'Administrador no disponible' }}</td>
                            <td class="text-nowrap">{{ $documento->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">

                                <x-list.button-group>

                                    @if ($canFile)
                                        <x-list.view-button :route="route('admin.legales.file', [$empresa_id, $documento->id])" :target="true" />
                                        <a class="btn btn-outline-secondary"
                                            href="{{ route('admin.legales.file', [$empresa_id, $documento->id, 'download' => 1]) }}"
                                            title="Descargar documento" aria-label="Descargar documento"><i class="bi bi-download"></i></a>
                                    @endif

                                </x-list.button-group>

                            </td>
                        </tr>
                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-4">No hay documentos para esta búsqueda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </div>

    <div class="mt-3">
        {{ $documentos->links() }}
    </div>

    <x-layout.loader.fullpage wire:loading.delay.short wire:target="save" />

</div>

@script
    <script>
        Alpine.data('empresaLegal', () => ({
            saving: false,
            uploading: false,
            validator: null,
            init() {
                this.cleanups = [Livewire.on('successEventList', data => this.$store.toast.success(data.message)), Livewire.on(
                    'errorEventList', data => this.$store.toast.info(data.message)), Livewire.on('legalSaved', () => {
                    if (this.$refs.archivo) this.$refs.archivo.value = '';
                    this.validator?.refresh();
                })];
                this.$nextTick(() => {
                    if (!this.$refs.form) return;
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid']
                    });
                    this.validator.addField('[name="titulo"]', [{
                        rule: 'required',
                        errorMessage: 'Ingresa un título'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }]);
                    this.validator.addField('[name="archivo"]', [{
                        validator: () => Boolean($wire.archivo),
                        errorMessage: 'Selecciona un archivo y espera a que termine de cargar'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || this.uploading || !this.validator || !await this.validator.revalidate()) return;
                this.saving = true;
                try {
                    await $wire.call('save');
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.validator?.destroy();
                this.cleanups?.forEach(cleanup => cleanup());
            }
        }));
    </script>
@endscript
