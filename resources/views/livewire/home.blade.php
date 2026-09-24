{{--
    HOME PÚBLICO — PLATAFORMA PARADA
    --------------------------------------------------------------------------
    Presenta la solución comercial para agencias de autobuses, resume las herramientas
    operativas disponibles y captura solicitudes de empresas interesadas mediante Livewire.

    Componentes reutilizables utilizados:
    - <x-layout.error />: Resumen de errores de validación del formulario comercial.
    - <x-layout.loader.fullpage />: Indicador durante el envío de la solicitud.
    --------------------------------------------------------------------------
--}}

@section('title', 'Operación digital para agencias de autobuses')

<div>

    <header class="home-header">

        <div class="home-container home-nav">

            <a class="brand" href="#inicio" aria-label="Parada, inicio">
                <span class="brand-mark"><i class="bi bi-bus-front-fill"></i></span>
                <span>Parada</span>
            </a>

            <nav class="nav-links" aria-label="Navegación principal">
                <a href="#plataforma">Plataforma</a>
                <a href="#operacion">Cómo funciona</a>
                <a href="#contacto">Para agencias</a>
            </nav>

            <a class="button button-small button-outline" href="{{ route('admin.login') }}">
                Iniciar sesión
                <i class="bi bi-arrow-up-right"></i>
            </a>

        </div>

    </header>

    <main>

        <section id="inicio" class="hero-section">

            <div class="hero-glow hero-glow-one"></div>
            <div class="hero-glow hero-glow-two"></div>

            <div class="home-container hero-grid">

                <div class="hero-copy">

                    <div class="eyebrow">
                        <span></span>
                        La nueva parada de tu operación digital
                    </div>

                    <h1>
                        Tu agencia vende viajes.
                        <em>Parada mueve el negocio.</em>
                    </h1>

                    <p class="hero-lead">
                        Una plataforma para publicar rutas, vender pasajes y controlar cada salida desde un solo lugar.
                        Tu equipo opera con claridad y tus pasajeros compran de forma simple.
                    </p>

                    <div class="hero-actions">
                        <a class="button button-primary" href="#contacto">
                            Quiero vender en Parada
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a class="button button-ghost" href="#plataforma">
                            Explorar la plataforma
                        </a>
                    </div>

                    <div class="hero-trust">
                        <div class="trust-item">
                            <i class="bi bi-shield-check"></i>
                            Operación segura
                        </div>
                        <div class="trust-item">
                            <i class="bi bi-qr-code-scan"></i>
                            Embarque con QR
                        </div>
                        <div class="trust-item">
                            <i class="bi bi-graph-up-arrow"></i>
                            Datos en tiempo real
                        </div>
                    </div>

                </div>

                <div class="hero-product" aria-label="Vista previa del panel de operación">

                    <div class="product-window">

                        <div class="window-bar">
                            <div class="window-dots"><span></span><span></span><span></span></div>
                            <span>Centro de operaciones</span>
                            <i class="bi bi-bell"></i>
                        </div>

                        <div class="product-body">

                            <aside class="product-sidebar">
                                <div class="mini-logo"><i class="bi bi-bus-front"></i></div>
                                <i class="bi bi-grid-1x2-fill active"></i>
                                <i class="bi bi-signpost-split"></i>
                                <i class="bi bi-ticket-perforated"></i>
                                <i class="bi bi-people"></i>
                                <i class="bi bi-bar-chart"></i>
                            </aside>

                            <div class="product-content">

                                <div class="product-heading">
                                    <div>
                                        <small>HOY, 24 SEPTIEMBRE</small>
                                        <strong>Buenos días, Expresos Central</strong>
                                    </div>
                                    <span class="live-pill"><i></i> En línea</span>
                                </div>

                                <div class="metric-grid">
                                    <div class="metric-card accent">
                                        <span>Ventas de hoy</span>
                                        <strong>1.284</strong>
                                        <small><i class="bi bi-arrow-up"></i> 18% esta semana</small>
                                    </div>
                                    <div class="metric-card">
                                        <span>Pasajes vendidos</span>
                                        <strong>86</strong>
                                        <small>12 salidas activas</small>
                                    </div>
                                    <div class="metric-card">
                                        <span>Ocupación</span>
                                        <strong>78%</strong>
                                        <small>Promedio de rutas</small>
                                    </div>
                                </div>

                                <div class="operations-card">
                                    <div class="operations-title">
                                        <strong>Próximas salidas</strong>
                                        <span>Ver todas</span>
                                    </div>
                                    <div class="trip-row">
                                        <span class="trip-time">08:30</span>
                                        <span class="trip-route"><b>Caracas</b><i class="bi bi-arrow-right"></i><b>Valencia</b></span>
                                        <span class="trip-capacity"><i style="width: 82%"></i></span>
                                        <span class="trip-status">Embarcando</span>
                                    </div>
                                    <div class="trip-row">
                                        <span class="trip-time">10:45</span>
                                        <span class="trip-route"><b>Maracay</b><i class="bi bi-arrow-right"></i><b>Maracaibo</b></span>
                                        <span class="trip-capacity"><i style="width: 64%"></i></span>
                                        <span class="trip-status pending">Programado</span>
                                    </div>
                                    <div class="trip-row">
                                        <span class="trip-time">13:10</span>
                                        <span class="trip-route"><b>Valencia</b><i class="bi bi-arrow-right"></i><b>Barquisimeto</b></span>
                                        <span class="trip-capacity"><i style="width: 46%"></i></span>
                                        <span class="trip-status pending">Programado</span>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="floating-ticket">
                        <div class="ticket-icon"><i class="bi bi-qr-code"></i></div>
                        <div><small>Pasaje validado</small><strong>Asiento 18</strong></div>
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                </div>

            </div>

            <div class="home-container proof-strip">
                <span>UNA OPERACIÓN CONECTADA DE PRINCIPIO A FIN</span>
                <div><i class="bi bi-buildings"></i> Agencias</div>
                <div><i class="bi bi-bus-front"></i> Flota</div>
                <div><i class="bi bi-signpost-2"></i> Rutas</div>
                <div><i class="bi bi-person-check"></i> Pasajeros</div>
                <div><i class="bi bi-wallet2"></i> Finanzas</div>
            </div>

        </section>

        <section id="plataforma" class="section features-section">

            <div class="home-container">

                <div class="section-heading">
                    <div>
                        <span class="section-kicker">Todo bajo control</span>
                        <h2>La operación completa,<br>en una sola plataforma.</h2>
                    </div>
                    <p>Desde la publicación del viaje hasta el último pasajero que aborda. Cada herramienta comparte la misma información y trabaja en tiempo real.</p>
                </div>

                <div class="feature-grid">

                    <article class="feature-card feature-large dark-card">
                        <div class="feature-icon"><i class="bi bi-signpost-split"></i></div>
                        <span class="feature-number">01</span>
                        <h3>Planifica rutas y salidas</h3>
                        <p>Configura terminales, paradas, tramos, tarifas y horarios. Controla el cupo real por cada tramo del recorrido.</p>
                        <div class="route-visual">
                            <div><span>A</span><b>Caracas</b></div>
                            <i></i><small>2 paradas</small><i></i>
                            <div><span>Z</span><b>Maracaibo</b></div>
                        </div>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon coral"><i class="bi bi-bus-front"></i></div>
                        <span class="feature-number">02</span>
                        <h3>Administra tu flota</h3>
                        <p>Registra autobuses, distribución de asientos y amenidades. Asigna cada unidad a sus próximas salidas.</p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon yellow"><i class="bi bi-ticket-perforated"></i></div>
                        <span class="feature-number">03</span>
                        <h3>Vende pasajes digitales</h3>
                        <p>Recibe reservas, controla pagos y genera pasajes listos para presentar desde el teléfono o imprimir.</p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon blue"><i class="bi bi-people"></i></div>
                        <span class="feature-number">04</span>
                        <h3>Conoce a tus pasajeros</h3>
                        <p>Consulta viajeros, documentos, reservas e historial. Gestiona reprogramaciones y reembolsos desde una misma ficha.</p>
                    </article>

                    <article class="feature-card feature-wide coupon-card">
                        <div>
                            <div class="feature-icon purple"><i class="bi bi-megaphone"></i></div>
                            <span class="feature-number">05</span>
                            <h3>Activa campañas que sí puedes medir</h3>
                            <p>Crea cupones por monto o porcentaje, para reservas completas o para cada pasaje, y consulta dónde se utilizaron.</p>
                        </div>
                        <div class="coupon-visual">
                            <small>CAMPAÑA ACTIVA</small>
                            <strong>RUTA10</strong>
                            <span>10% de descuento</span>
                            <div><i style="width: 68%"></i></div>
                            <em>68 de 100 canjeados</em>
                        </div>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon green"><i class="bi bi-cash-stack"></i></div>
                        <span class="feature-number">06</span>
                        <h3>Entiende tu dinero</h3>
                        <p>Revisa ventas, tasas de servicio, pagos y resultados por empresa, ruta o período desde reportes claros.</p>
                    </article>

                </div>

            </div>

        </section>

        <section id="operacion" class="section journey-section">

            <div class="home-container journey-grid">

                <div class="journey-copy">
                    <span class="section-kicker light">El día del viaje</span>
                    <h2>Del teléfono al autobús, sin fricción.</h2>
                    <p>Cada pasaje pagado genera un código QR único. Tu equipo puede validarlo al abordar y registrar el ingreso en segundos.</p>

                    <ol class="journey-list">
                        <li><span>1</span><div><strong>Compra digital</strong><small>El pasajero elige tramo, asiento y forma de pago.</small></div></li>
                        <li><span>2</span><div><strong>Pasaje QR</strong><small>Recibe un pase único, seguro y fácil de presentar.</small></div></li>
                        <li><span>3</span><div><strong>Embarque ágil</strong><small>La aplicación lectora confirma el pasaje al instante.</small></div></li>
                        <li><span>4</span><div><strong>Impresión térmica</strong><small>También puedes entregar un comprobante físico.</small></div></li>
                    </ol>
                </div>

                <div class="qr-stage">
                    <div class="phone-frame">
                        <div class="phone-top"></div>
                        <div class="phone-screen">
                            <span class="scan-label">ESCANEAR PASAJE</span>
                            <div class="scan-box">
                                <i class="corner top-left"></i><i class="corner top-right"></i>
                                <i class="corner bottom-left"></i><i class="corner bottom-right"></i>
                                <i class="bi bi-qr-code"></i>
                                <span class="scan-line"></span>
                            </div>
                            <div class="scan-result"><i class="bi bi-check-circle-fill"></i><div><small>Pasaje válido</small><strong>Caracas → Valencia</strong></div></div>
                        </div>
                    </div>
                    <div class="print-card"><i class="bi bi-printer"></i><div><small>Impresión térmica</small><strong>Lista en segundos</strong></div></div>
                </div>

            </div>

        </section>

        <section class="section visibility-section">

            <div class="home-container visibility-card">
                <div>
                    <span class="section-kicker">Más alcance</span>
                    <h2>Tu agencia visible donde los pasajeros ya están buscando.</h2>
                    <p>Publica tus salidas en el portal de compra de Parada y convierte tu inventario disponible en nuevas ventas digitales.</p>
                    <a class="text-link" href="#contacto">Quiero sumar mi agencia <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="search-preview">
                    <div class="search-bar"><span>Caracas</span><i class="bi bi-arrow-right"></i><span>Maracaibo</span><b>Buscar</b></div>
                    <div class="result-card"><span class="bus-avatar"><i class="bi bi-bus-front"></i></span><div><strong>Expresos Central</strong><small>Ejecutivo · 7 h 20 min</small></div><div class="result-time"><strong>10:00 PM</strong><small>Directo</small></div><span class="result-price"><small>desde</small><b>25</b></span></div>
                    <div class="result-card faded"><span class="bus-avatar"><i class="bi bi-bus-front"></i></span><div><strong>Rápidos del Lago</strong><small>Semi-cama · 8 h</small></div><div class="result-time"><strong>11:30 PM</strong><small>1 parada</small></div><span class="result-price"><small>desde</small><b>22</b></span></div>
                </div>
            </div>

        </section>

        <section id="contacto" class="section contact-section">

            <div class="home-container contact-grid">

                <div class="contact-copy">
                    <span class="section-kicker light">Para agencias</span>
                    <h2>¿Representas una empresa de autobuses?</h2>
                    <p>Conversemos sobre tu operación y cómo comenzar a vender de forma digital. Nuestro equipo te acompañará durante la incorporación.</p>

                    <div class="contact-benefits">
                        <div><i class="bi bi-check2"></i><span><strong>Configuración acompañada</strong><small>Organizamos contigo rutas, flota y equipo.</small></span></div>
                        <div><i class="bi bi-check2"></i><span><strong>Accesos por responsabilidades</strong><small>Cada colaborador trabaja con sus permisos.</small></span></div>
                        <div><i class="bi bi-check2"></i><span><strong>Operación lista para crecer</strong><small>Centraliza información y abre un nuevo canal de ventas.</small></span></div>
                    </div>
                </div>

                <div class="contact-form-card">

                    @if ($solicitudEnviada)

                        <div class="form-success" role="status">
                            <span><i class="bi bi-check-lg"></i></span>
                            <h3>Recibimos tu solicitud</h3>
                            <p>Gracias por considerar Parada. Nuestro equipo revisará los datos y se pondrá en contacto contigo.</p>
                            <button type="button" wire:click="$set('solicitudEnviada', false)">Enviar otra solicitud</button>
                        </div>

                    @else

                        <div class="form-heading">
                            <span>Hablemos de tu agencia</span>
                            <small>Completa los datos y te contactaremos.</small>
                        </div>

                        <x-layout.error />

                        <form x-data="contactAgency" x-ref="form" @submit.prevent="preSave" novalidate>

                            <div class="honeypot" aria-hidden="true">
                                <label for="website">Sitio web</label>
                                <input id="website" name="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="form-grid">
                                <div class="field full-field">
                                    <label for="nombre">Nombre y apellido</label>
                                    <input id="nombre" name="nombre" type="text" wire:model="nombre" placeholder="¿Cómo podemos llamarte?" autocomplete="name">
                                    @error('nombre') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field">
                                    <label for="empresa">Agencia o empresa</label>
                                    <input id="empresa" name="empresa" type="text" wire:model="empresa" placeholder="Nombre comercial" autocomplete="organization">
                                    @error('empresa') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field">
                                    <label for="cargo">Tu responsabilidad</label>
                                    <input id="cargo" name="cargo" type="text" wire:model="cargo" placeholder="Dueño, apoderado, gerente…" autocomplete="organization-title">
                                    @error('cargo') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field">
                                    <label for="telefono">Teléfono</label>
                                    <input id="telefono" name="telefono" type="tel" wire:model="telefono" placeholder="+58 412 000 0000" autocomplete="tel">
                                    @error('telefono') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field">
                                    <label for="email">Correo</label>
                                    <input id="email" name="email" type="email" wire:model="email" placeholder="nombre@agencia.com" autocomplete="email">
                                    @error('email') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field full-field">
                                    <label for="ciudad">Ciudad principal</label>
                                    <input id="ciudad" name="ciudad" type="text" wire:model="ciudad" placeholder="Ciudad donde opera la agencia" autocomplete="address-level2">
                                    @error('ciudad') <small>{{ $message }}</small> @enderror
                                </div>
                                <div class="field full-field">
                                    <label for="mensaje">Cuéntanos sobre tu operación <span>Opcional</span></label>
                                    <textarea id="mensaje" name="mensaje" wire:model="mensaje" rows="3" placeholder="Rutas principales, cantidad de unidades o cualquier dato que debamos conocer"></textarea>
                                    @error('mensaje') <small>{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <button class="button button-primary form-submit" type="submit" :disabled="saving"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="enviarSolicitud">Solicitar información <i class="bi bi-arrow-right"></i></span>
                                <span wire:loading wire:target="enviarSolicitud">Enviando solicitud…</span>
                            </button>

                            <p class="privacy-note"><i class="bi bi-lock"></i> Usaremos tus datos únicamente para atender esta solicitud.</p>

                        </form>

                    @endif

                </div>

            </div>

        </section>

    </main>

    <footer class="home-footer">
        <div class="home-container footer-content">
            <a class="brand brand-light" href="#inicio"><span class="brand-mark"><i class="bi bi-bus-front-fill"></i></span><span>Parada</span></a>
            <p>Infraestructura digital para mover personas y hacer crecer agencias.</p>
            <span>© {{ date('Y') }} Parada</span>
        </div>
    </footer>

    <x-layout.loader.fullpage wire:loading.delay.longer wire:target="enviarSolicitud" />

</div>

@script
    <script>
        Alpine.data('contactAgency', () => ({
            validator: null,
            saving: false,
            init() {
                this.$nextTick(() => {
                    this.validator = new JustValidate(this.$refs.form, {
                        errorLabelCssClass: ['invalid-feedback'],
                        errorFieldCssClass: ['is-invalid'],
                        successFieldCssClass: ['is-valid'],
                    })
                    .addField(this.$refs.form.querySelector('[name="nombre"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="empresa"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="cargo"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 150,
                        errorMessage: 'Máximo 150 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="telefono"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'maxLength',
                        value: 50,
                        errorMessage: 'Máximo 50 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="email"]'), [{
                        rule: 'required',
                        errorMessage: 'Este campo es requerido'
                    }, {
                        rule: 'email',
                        errorMessage: 'Ingresa un correo válido'
                    }, {
                        rule: 'maxLength',
                        value: 255,
                        errorMessage: 'Máximo 255 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="ciudad"]'), [{
                        rule: 'maxLength',
                        value: 150,
                        errorMessage: 'Máximo 150 caracteres'
                    }])
                    .addField(this.$refs.form.querySelector('[name="mensaje"]'), [{
                        rule: 'maxLength',
                        value: 1500,
                        errorMessage: 'Máximo 1500 caracteres'
                    }]);
                });
            },
            async preSave() {
                if (this.saving || !this.validator || !await this.validator.revalidate()) return;

                this.saving = true;

                try {
                    await $wire.call('enviarSolicitud');
                } finally {
                    this.saving = false;
                }
            },
            destroy() {
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
