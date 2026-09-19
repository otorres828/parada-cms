{{--
|--------------------------------------------------------------------------
| BETTING SLIP / MOTOR DE JUEGO CRUZADO COMPONENT
|--------------------------------------------------------------------------
| Controla la interfaz de configuración, validación y colocación de apuestas
| para la pelea activa. Maneja la reactividad local del monto a arriesgar,
| las cuotas calculadas y las modalidades del libro mediante Alpine.js (`bettingSlip`).
| Consume eventos globales de Livewire (`successApuesta`, `errorApuesta`) para
| coordinar notificaciones flotantes asíncronas vía SweetAlert2, asegurando que
| no se altere el flujo vertical del scroll global (`heightAuto: false`).
|
| Reusable UI Components:
|   - <x-site.geral.button /> -> Botón homologado para acciones prioritarias (Confirmar/Cancelar).
|--------------------------------------------------------------------------
--}}
@props(['apuestaAbierta','modalidades', 'balanceUsuario', 'eventoEnVivo'])

<div x-data="bettingSlip({{ (float) $balanceUsuario }}, {{ json_encode($modalidades->pluck('nombre', 'id')->toArray() ?? []) }})"
    {{ $attributes->merge(['class' => 'glass-panel rounded-3xl p-6 border border-primary/20 bg-gradient-to-b from-surface-container-low/80 to-transparent shadow-xl relative']) }}>

    {{-- Cabecera del Estado de Apuestas --}}
    <div class="flex items-center justify-between mb-6">

        <h4 class="font-headline-md text-base md:text-headline-md text-on-surface-variant">
            {{ $apuestaAbierta ? 'Apuesta al compromiso ganador' : 'Apuestas Cerradas' }}
        </h4>
    </div>

    {{-- Seleccionador de Cuotas --}}
   {{-- <x-site.dashboard.bet-selector :modalidades="$modalidades" /> --}}

    @if ($apuestaAbierta)

        {{-- Seleccionador de Cuotas --}}
        <x-site.dashboard.bet-selector :modalidades="$modalidades" />

    @else
        <div
            class="text-center p-6 text-on-surface-variant text-sm bg-surface-container-lowest/50 rounded-2xl border border-white/5 mb-4">
            <span class="material-symbols-outlined text-2xl mb-1 block text-amber-400">lock</span>
            Apuestas cerradas.<br>
        </div>
    @endif


    @if ($apuestaAbierta)

            {{-- Panel de Mis Apuestas en Vivo (N Apuestas) --}}
        @php
            $misApuestasActivas = $this->apuestasEquipoUsuario ?? collect();
        @endphp

        <x-site.dashboard.list-fight-in-live :misApuestasActivas="$misApuestasActivas" :isDerby="false" />

    @endif

    

</div>

@script
    <script>
        Alpine.data('bettingSlip', (balanceInicial, modalidadesMap) => ({
            betAmount: 100.00,
            tipoGallo: null, // 1: Rojo, 2: Verde
            modalidadApuesta: null, // 1: Doy,  2: Agarro
            modalidadId: null,
            balanceMax: parseFloat(balanceInicial) || 0,
            porcentajeActivo: 0,
            bandoTexto: '',
            porcentajeCasa:  10,
            modalidades: modalidadesMap || {
                1: 'Parejo',
                2: '90',
                3: '80',
                4: '70'
            },

            init() {
                this.$watch('$wire.balanceUsuario', value => {
                    this.balanceMax = parseFloat(value);
                });

                this.$watch('betAmount', value => {
                    const numVal = parseFloat(value) || 0;
                    if (numVal > this.balanceMax) {
                        this.betAmount = this.balanceMax;
                    }
                });

                Livewire.on('successApuesta', data => {
                    this.resetSelection();
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos enviados',
                        text: data.message || '',
                        timer: 2000,
                        showConfirmButton: false,
                        heightAuto: false
                    });
                });

                Livewire.on('errorApuesta', data => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Problema',
                        text: data.message || '',
                        timer: 2000,
                        showConfirmButton: false,
                        heightAuto: false
                    });
                });
            },

            addAmount(val) {
                const actual = parseFloat(this.betAmount) || 0;
                if ((actual + val) <= this.balanceMax) {
                    this.betAmount = actual + val;
                } else {
                    this.betAmount = this.balanceMax;
                }
            },

            resetSelection() {
                this.tipoGallo = null;
                this.modalidadApuesta = null;
                this.modalidadId = null;
                this.porcentajeActivo = 0;
                this.bandoTexto = '';
            },

            seleccionarCuota(gallo, modalidad, id, porcentaje, bando) {
                this.tipoGallo = gallo;
                this.modalidadApuesta = modalidad;
                this.modalidadId = id;
                this.porcentajeActivo = porcentaje;
                this.bandoTexto = bando;

                console.log(
                    `Seleccionado: Gallo ${gallo}, Modalidad ${modalidad}, ID ${id}, Porcentaje ${porcentaje}, Bando ${bando}`
                    );
            },

            calcularValorBoton() {

                let valor = 0;
                //Si la modalidad es parejo, la ganancia es lo que apuestas
                if(this.modalidadId == 1) {
                    valor= this.betAmount.toFixed(2);
                }

                const monto = parseFloat(this.betAmount) || 0;
                if (this.modalidadApuesta == '1') { //doy
                    valor = (monto * (this.porcentajeActivo / 100)).toFixed(2);
                } else {
                    valor = monto.toFixed(2);
                }

                return (valor * (1 - (this.porcentajeCasa / 100))).toFixed(2);
            },

            confirmarApuesta() {
                if (!this.tipoGallo || !this.modalidadApuesta || !this.modalidadId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selección Requerida',
                        text: 'Por favor, selecciona una cuota antes de confirmar.',
                        heightAuto: false
                    });
                    return;
                }

                if (this.betAmount <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Monto Inválido',
                        text: 'La cantidad a apostar debe ser mayor a 0.',
                        heightAuto: false
                    });
                    return;
                }

                const colorGallo = this.tipoGallo === 1 ? 'ROJO' : 'VERDE';
                const accion = this.modalidadId == '1' ? '' : this.modalidadApuesta === 1 ? 'DOY' : 'AGARRO';
                const nombreModalidad = this.modalidades[this.modalidadId] || '';
                const tipoApuesta = `${colorGallo} - ${accion} ${nombreModalidad}`;
                const gananciaEstimada = this.calcularValorBoton();

                const html = `
                    <p>Estás a punto de realizar la siguiente apuesta:</p>
                    <ul>
                        <li><strong>Tipo:</strong> ${tipoApuesta}</li>
                        <li><strong>Monto a Apostar:</strong> $ MXN ${parseFloat(this.betAmount).toFixed(2)}</li>
                        <li><strong>Ganancia Estimada:</strong> $ MXN ${gananciaEstimada}</li>
                    </ul>
                `;

                Swal.fire({
                    title: '¿Confirmar Apuesta?',
                    html: html,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, colocar apuesta',
                    cancelButtonText: 'Cancelar',
                    heightAuto: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviamos los 4 parámetros a Livewire
                        this.$wire.procesarPeleaEquipo(
                            this.betAmount,
                            this.tipoGallo,
                            this.modalidadApuesta,
                            this.modalidadId
                        );
                    }
                });
            },

            cancelarApuesta(apuestaId) {
                Swal.fire({
                    title: '¿Cancelar Apuesta?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar apuesta',
                    cancelButtonText: 'Cancelar',
                    heightAuto: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.$wire.cancelarPeleaEquipo(apuestaId);
                    }
                });
            }
        }));
    </script>
@endscript
