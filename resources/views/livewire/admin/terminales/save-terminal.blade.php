{{--
    TERMINALES — FORMULARIO
    --------------------------------------------------------------------------
    Permite crear o editar una terminal con su ubicación, dirección, coordenadas y estado.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Cabecera del módulo con título y acciones.
    - <x-form.cancel-button />: Enlace para regresar o cancelar la edición.
    - <x-layout.error />: Resumen de los errores de validación de Livewire.
    - <x-form.dropdown />: Selector con etiqueta para las opciones del formulario.
    - <x-form.text-input />: Campo de entrada con etiqueta.
    - <x-form.textarea />: Campo de texto de varias líneas.
    - <x-layout.loader.fullpage />: Indicador de carga durante las operaciones de Livewire.
    - Leaflet: Mapa interactivo y marcador sincronizado con las coordenadas.
    --------------------------------------------------------------------------
--}}

@section('title', 'Terminales')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" data-leaflet-styles="true"
        integrity="sha256-p4NxAoJBhIINfQ3ynhtpV3qLSjMZQ7QLUP6M24CclFo=" crossorigin="">
@endpush

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

    <x-layout.error />

    <form id="saveTerminalForm" x-ref="form" @submit.prevent="preSave" novalidate>

        <div class="container-fluid px-0">

            <div class="row g-4">

                <div class="col-lg-6">

                    <div class="mb-3">

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

                </div>

                <div class="col-lg-6">

                    <div class="card h-100">

                        <div class="card-body">

                            <label class="form-label" for="buscar-direccion">
                                Buscar dirección en Venezuela
                            </label>

                            <div class="position-relative mb-3">
                                <input id="buscar-direccion" type="search" class="form-control"
                                    x-model="mapSearch" @input.debounce.500ms="searchAddress"
                                    @keydown.escape="searchResults = []"
                                    placeholder="Escribe una terminal, ciudad o dirección" autocomplete="off">

                                <div x-cloak x-show="searching" class="form-text">
                                    Buscando ubicaciones...
                                </div>

                                <div x-cloak x-show="searchResults.length"
                                    class="list-group position-absolute start-0 end-0 shadow"
                                    style="z-index: 1001; max-height: 240px; overflow-y: auto;">
                                    <template x-for="result in searchResults" :key="result.place_id">
                                        <button type="button" class="list-group-item list-group-item-action"
                                            @click="selectAddress(result)" x-text="result.display_name"></button>
                                    </template>
                                </div>
                            </div>

                            <div x-ref="map" class="rounded border" style="height: 480px;" wire:ignore></div>

                            <p class="form-text mb-0 mt-2">
                                Puedes buscar una dirección, cambiar las coordenadas o hacer clic directamente en el mapa.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <hr>
        <button class="btn btn-primary" type="submit" :disabled="saving"
            wire:loading.attr="disabled">Guardar</button>
    </form>

    <x-layout.loader.fullpage wire:loading.delay.short />

</div>

@script
    <script>
        Alpine.data('saveTerminal', () => ({
            validator: null,
            saving: false,
            map: null,
            marker: null,
            mapSearch: '',
            searchResults: [],
            searching: false,
            searchController: null,
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
                    this.initializeMap();
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
            async loadLeaflet() {
                if (!document.querySelector('link[data-leaflet-styles]')) {
                    const stylesheet = document.createElement('link');
                    stylesheet.rel = 'stylesheet';
                    stylesheet.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    stylesheet.integrity = 'sha256-p4NxAoJBhIINfQ3ynhtpV3qLSjMZQ7QLUP6M24CclFo=';
                    stylesheet.crossOrigin = '';
                    stylesheet.dataset.leafletStyles = 'true';
                    document.head.appendChild(stylesheet);
                }
                if (window.L) return;
                if (!window.leafletLoader) {
                    window.leafletLoader = new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                        script.crossOrigin = '';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                }
                await window.leafletLoader;
            },
            async initializeMap() {
                try {
                    await this.loadLeaflet();
                    const latitude = this.coordinate($wire.latitud, 6.4238);
                    const longitude = this.coordinate($wire.longitud, -66.5897);
                    const hasCoordinates = this.hasCoordinates();
                    this.map = L.map(this.$refs.map).setView([latitude, longitude], hasCoordinates ? 15 : 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.map);
                    this.marker = L.marker([latitude, longitude], {
                        draggable: true,
                    }).addTo(this.map);
                    this.marker.on('dragend', event => this.updateCoordinates(event.target.getLatLng()));
                    this.map.on('click', event => this.updateCoordinates(event.latlng));
                    this.$watch('$wire.latitud', () => this.syncMarker());
                    this.$watch('$wire.longitud', () => this.syncMarker());
                    setTimeout(() => this.map?.invalidateSize(), 100);
                } catch (error) {
                    this.$store.toast.info('No fue posible cargar el mapa. Puedes ingresar las coordenadas manualmente.');
                }
            },
            coordinate(value, fallback) {
                const coordinate = Number(value);
                return Number.isFinite(coordinate) && String(value).trim() !== '' ? coordinate : fallback;
            },
            hasCoordinates() {
                return String($wire.latitud ?? '').trim() !== '' && String($wire.longitud ?? '').trim() !== '';
            },
            syncMarker() {
                if (!this.map || !this.marker || !this.hasCoordinates()) return;
                const position = [Number($wire.latitud), Number($wire.longitud)];
                if (!position.every(Number.isFinite)) return;
                this.marker.setLatLng(position);
                this.map.panTo(position);
            },
            updateCoordinates(position) {
                $wire.latitud = Number(position.lat).toFixed(7);
                $wire.longitud = Number(position.lng).toFixed(7);
                this.marker?.setLatLng(position);
            },
            async searchAddress() {
                const query = this.mapSearch.trim();
                this.searchController?.abort();
                this.searchResults = [];
                if (query.length < 3) return;
                this.searchController = new AbortController();
                this.searching = true;
                try {
                    const params = new URLSearchParams({
                        countrycode: 'VE',
                        limit: '6',
                        lang: 'en',
                        bbox: '-73.4,0.6,-59.7,12.3',
                        q: query,
                    });
                    const response = await fetch(`https://photon.komoot.io/api/?${params}`, {
                        signal: this.searchController.signal,
                    });
                    if (!response.ok) throw new Error('Search failed');
                    const data = await response.json();
                    this.searchResults = data.features
                        .filter(result => result.properties.countrycode?.toUpperCase() === 'VE')
                        .map(result => ({
                            place_id: `${result.properties.osm_type}-${result.properties.osm_id}`,
                            display_name: [
                                result.properties.name,
                                result.properties.street,
                                result.properties.city,
                                result.properties.state,
                            ].filter((value, index, values) => value && values.indexOf(value) === index).join(', '),
                            lat: result.geometry.coordinates[1],
                            lon: result.geometry.coordinates[0],
                        }));
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        this.$store.toast.info('No fue posible consultar direcciones en este momento.');
                    }
                } finally {
                    this.searching = false;
                }
            },
            selectAddress(result) {
                this.mapSearch = result.display_name;
                this.searchResults = [];
                $wire.direccion = result.display_name;
                this.updateCoordinates({
                    lat: Number(result.lat),
                    lng: Number(result.lon),
                });
                this.map?.setView([Number(result.lat), Number(result.lon)], 17);
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
                this.searchController?.abort();
                this.map?.remove();
                this.toastCleanup?.forEach(cleanup => cleanup());
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
