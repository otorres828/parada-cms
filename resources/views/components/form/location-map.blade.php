{{--
    FORM — MAPA DE UBICACIÓN
    --------------------------------------------------------------------------
    Permite buscar una dirección venezolana y seleccionar coordenadas mediante
    un marcador arrastrable o un clic sobre el mapa. Mantiene dirección, latitud
    y longitud sincronizadas con propiedades Livewire mediante entangle.
    --------------------------------------------------------------------------
--}}

@props([
    'address' => 'direccion',
    'latitude' => 'latitud',
    'longitude' => 'longitud',
])

<div
    x-data="locationMap({
        address: $wire.entangle(@js($address)),
        latitude: $wire.entangle(@js($latitude)),
        longitude: $wire.entangle(@js($longitude)),
    })"
    class="card h-100 overflow-hidden"
>

    <div class="card-body overflow-hidden">

        <label class="form-label" for="buscar-direccion">
            Buscar dirección en Venezuela
        </label>

        <div class="position-relative mb-3">
            <input id="buscar-direccion" type="search" class="form-control"
                x-model="search" @input.debounce.500ms="searchAddress"
                @keydown.escape="results = []"
                placeholder="Escribe una terminal, ciudad o dirección" autocomplete="off">

            <div x-cloak x-show="searching" class="form-text">
                Buscando ubicaciones...
            </div>

            <div x-cloak x-show="results.length"
                class="list-group position-absolute start-0 end-0 shadow overflow-auto"
                style="z-index: 1001; max-height: 240px;">
                <template x-for="result in results" :key="result.place_id">
                    <button type="button" class="list-group-item list-group-item-action"
                        @click="selectAddress(result)" x-text="result.display_name"></button>
                </template>
            </div>
        </div>

        <div x-ref="map" class="rounded border position-relative overflow-hidden w-100"
            style="height: 480px; max-width: 100%; isolation: isolate;" wire:ignore></div>

        <p class="form-text mb-0 mt-2">
            Puedes buscar una dirección, cambiar las coordenadas o hacer clic directamente en el mapa.
        </p>

    </div>

</div>

@script
    <script>
        Alpine.data('locationMap', config => ({
            address: config.address,
            latitude: config.latitude,
            longitude: config.longitude,
            map: null,
            marker: null,
            search: '',
            results: [],
            searching: false,
            searchController: null,
            resizeObserver: null,
            async init() {
                this.search = this.address || '';
                await this.$nextTick();
                await this.initializeMap();
            },
            async loadStyles() {
                if (document.querySelector('link[data-leaflet-styles]')) return;

                return new Promise((resolve) => {
                    const stylesheet = document.createElement('link');
                    stylesheet.rel = 'stylesheet';
                    stylesheet.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    stylesheet.dataset.leafletStyles = 'true';
                    stylesheet.onload = resolve;
                    stylesheet.onerror = () => {
                        console.warn('Fallo la carga del CSS desde unpkg, intentando cdnjs...');
                        stylesheet.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
                        stylesheet.onload = resolve;
                        stylesheet.onerror = resolve; // Continuar aunque falle el CSS
                    };
                    document.head.appendChild(stylesheet);
                });
            },
            async loadScript() {
                if (window.L) return;

                if (!window.leafletLoader) {
                    window.leafletLoader = new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.onload = () => resolve();
                        script.onerror = () => {
                            console.warn('Fallo la carga del JS desde unpkg, intentando cdnjs...');
                            const fallbackScript = document.createElement('script');
                            fallbackScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
                            fallbackScript.onload = () => resolve();
                            fallbackScript.onerror = (err) => {
                                window.leafletLoader = null; // Liberar para reintentar si es necesario
                                reject(err);
                            };
                            document.head.appendChild(fallbackScript);
                        };
                        document.head.appendChild(script);
                    });
                }

                await window.leafletLoader;
            },
            async initializeMap() {
                try {
                    await this.loadStyles();
                    await this.loadScript();

                    if (!window.L) {
                        throw new Error('Leaflet no está disponible en window.L');
                    }

                    const latitude = this.coordinate(this.latitude, 6.4238);
                    const longitude = this.coordinate(this.longitude, -66.5897);
                    const hasCoordinates = this.hasCoordinates();

                    this.map = L.map(this.$refs.map).setView(
                        [latitude, longitude],
                        hasCoordinates ? 15 : 6,
                    );

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(this.map);

                    this.marker = L.marker([latitude, longitude], {
                        draggable: true,
                    }).addTo(this.map);

                    this.marker.on('dragend', event => {
                        if (event?.target?.getLatLng) {
                            this.updateCoordinates(event.target.getLatLng());
                        }
                    });

                    this.map.on('click', event => {
                        if (event?.latlng) {
                            this.updateCoordinates(event.latlng);
                        }
                    });

                    this.$watch('latitude', () => this.syncMarker());
                    this.$watch('longitude', () => this.syncMarker());

                    this.resizeObserver = new ResizeObserver(() => this.map?.invalidateSize());
                    this.resizeObserver.observe(this.$refs.map);
                    requestAnimationFrame(() => this.map?.invalidateSize());
                } catch (error) {
                    console.error('Error al inicializar el mapa (causa exacta):', error);
                    this.$store.toast?.info('No fue posible cargar el mapa. Puedes ingresar las coordenadas manualmente.');
                }
            },
            coordinate(value, fallback) {
                const coordinate = Number(value);

                return Number.isFinite(coordinate) && String(value).trim() !== '' ? coordinate : fallback;
            },
            hasCoordinates() {
                return String(this.latitude ?? '').trim() !== '' && String(this.longitude ?? '').trim() !== '';
            },
            syncMarker() {
                if (!this.map || !this.marker || !this.hasCoordinates()) return;

                const position = [Number(this.latitude), Number(this.longitude)];
                if (!position.every(Number.isFinite)) return;

                this.marker.setLatLng(position);
                this.map.panTo(position);
            },
            updateCoordinates(position) {
                if (!position || typeof position.lat === 'undefined' || typeof position.lng === 'undefined') return;

                this.latitude = Number(position.lat).toFixed(7);
                this.longitude = Number(position.lng).toFixed(7);
                this.marker?.setLatLng(position);
            },
            async searchAddress() {
                const query = this.search.trim();
                this.searchController?.abort();
                this.results = [];

                if (query.length < 3) return;

                const controller = new AbortController();
                this.searchController = controller;
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
                        signal: controller.signal,
                    });

                    if (!response.ok) throw new Error('Search failed');

                    const data = await response.json();
                    this.results = data.features
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
                        console.error('Error al buscar dirección:', error);
                        this.$store.toast?.info('No fue posible consultar direcciones en este momento.');
                    }
                } finally {
                    if (this.searchController === controller) this.searching = false;
                }
            },
            selectAddress(result) {
                this.search = result.display_name;
                this.results = [];
                this.address = result.display_name;
                this.updateCoordinates({
                    lat: Number(result.lat),
                    lng: Number(result.lon),
                });
                this.map?.setView([Number(result.lat), Number(result.lon)], 17);
            },
            destroy() {
                this.searchController?.abort();
                this.resizeObserver?.disconnect();
                this.map?.remove();
            },
        }));
    </script>
@endscript