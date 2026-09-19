<div class="pt-16">
    <!-- Hero Section -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden px-container-margin">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 hero-gradient"></div>
        </div>
        <div class="relative z-10 w-full max-w-7xl grid grid-cols-1 lg:grid-cols-12 gap-gutter items-center">
            <!-- Image/Atmosphere Column -->
            <div class="lg:col-span-7 flex flex-col gap-stack-gap">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 border border-primary/20 rounded-full w-fit">
                    <span class="w-2 h-2 rounded-full bg-primary-container live-dot"></span>
                    <span class="font-label-md text-label-md text-primary uppercase tracking-widest">Arena en
                        Vivo</span>
                </div>
                <h1 class="font-display-lg text-display-lg text-white leading-none">
                    DOMINA EL <br> <span class="text-primary-container italic">PALENQUE DIGITAL</span>
                </h1>
             
                <!-- Abstract Image Holder -->
                <div class="mt-8 relative group">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-primary-container to-secondary opacity-20 blur-xl group-hover:opacity-40 transition duration-1000">
                    </div>
                    <img class="object-cover glass-panel w-full h-96  rounded-xl grayscale-[0.2] hover:grayscale-0 transition-all duration-700 shadow-2xl"
                        alt="img"
                        src="{{ asset('assets/img/misc/gallonew.png') }}">
                </div>
            </div>
            <!-- Auth/Login Column -->

            @livewire('site.auth.login-form')

        </div>
    </section>
    <!-- Features Bento Grid -->
    <section class="py-24 px-container-margin bg-surface-container-lowest">
        <div class="max-w-7xl mx-auto flex flex-col gap-gutter">
            {{-- <div class="mb-12">
                <h3 class="font-headline-lg text-headline-lg text-white mb-2">Poder sin Compromisos</h3>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">Diseñado para el
                    apostador serio. Velocidad, seguridad y claridad en cada segundo de la pelea.</p>
            </div> --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter h-auto lg:h-[500px]">
                <!-- Live Streaming -->
                <div
                    class="md:col-span-8 glass-panel rounded-xl overflow-hidden relative group p-panel-padding flex flex-col justify-end min-h-[300px]">
                    <div class="absolute inset-0 z-0">
                        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-60"
                            data-alt="A vibrant, high-definition action shot of a live sports broadcast interface on a futuristic monitor. The screen displays a intense, blurred background of a sports arena with emerald and crimson ambient lighting. Overlaid are sharp, translucent glass statistics and odds widgets that pulse with real-time updates. The style is ultra-modern and tech-focused, representing professional live streaming capabilities."
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuBAkZROOrAEWm4yRwz6zfvjKzNt52VV6ptvNqQjK8ib8Yq3cuB14rVCRYrOYWB3Pak4x6Lf2ehkAIaQY1cdLiu4hqWwCC34HIhQSRgu4k0xJ25m3mLWW-0mZ7xztUU5joPdWSWsw_r1RdzST56-3dUOwOHyXvhCc70_smqKdhByfMm8lUBdMCjs8nzYTMggw3Ma4WI8rULeaSXczgGNoiapN3VTXE8DOSxgMM4Z_HDs5O1907mm9lcnoPX-kWBQnosPNj_CCHVk5w">
                        <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent">
                        </div>
                    </div>
                    <div class="relative z-10">
                        <span class="material-symbols-outlined text-primary-container text-4xl mb-4">sensors</span>
                        <h4 class="font-headline-md text-headline-md text-white mb-2">Streaming de Alta
                            Fidelidad</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-md">No te pierdas ni
                            un solo golpe. Nuestras transmisiones en HD tienen una latencia menor a 1 segundo
                            para decisiones críticas.</p>
                    </div>
                </div>
                <!-- Real-time Betting -->
                {{-- <div
                    class="md:col-span-4 glass-panel rounded-xl p-panel-padding border-primary/20 flex flex-col gap-stack-gap">
                    <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary-container">receipt_long</span>
                    </div>
                    <h4 class="font-headline-md text-headline-md text-white">Apuestas en Tiempo Real</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant">Mercados dinámicos que se
                        ajustan al pulso de la arena. Cambia tu estrategia al instante según el desarrollo del
                        combate.</p>
                    <div class="mt-auto pt-8">
                        <div
                            class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-primary-container mb-2">
                            <span class="">Mercado Abierto</span>
                            <span class="material-symbols-outlined text-sm">trending_up</span>
                        </div>
                        <div class="h-2 bg-surface-container-highest rounded-full overflow-hidden">
                            <div class="h-full bg-primary-container w-3/4 rounded-full"></div>
                        </div>
                    </div>
                </div> --}}
                <!-- Secure Withdrawals -->
                {{-- <div class="md:col-span-4 glass-panel rounded-xl p-panel-padding flex flex-col gap-stack-gap">
                    <div class="h-12 w-12 rounded-lg bg-tertiary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-tertiary">payments</span>
                    </div>
                    <h4 class="font-headline-md text-headline-md text-white">Retiros Seguros</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant">Tu capital está protegido con
                        encriptación de nivel bancario. Retiros procesados en minutos, no días.</p>
                </div> --}}
                <!-- Global Reach / Stats -->
                <div
                    class="md:col-span-8 glass-panel rounded-xl p-panel-padding flex items-center justify-between overflow-hidden relative">
                    <div class="flex flex-col gap-2 relative z-10">
                        <div class="text-4xl font-bold text-white font-headline-lg">+10K</div>
                        <div class="text-on-surface-variant font-label-md">Usuarios Activos Diarios</div>
                    </div>
                    <div class="flex flex-col gap-2 relative z-10">
                        <div class="text-4xl font-bold text-white font-headline-lg">99.9%</div>
                        <div class="text-on-surface-variant font-label-md">Uptime del Servidor</div>
                    </div>
                    <div class="flex flex-col gap-2 relative z-10">
                        <div class="text-4xl font-bold text-white font-headline-lg">24/7</div>
                        <div class="text-on-surface-variant font-label-md">Soporte Especializado</div>
                    </div>
                    <div class="absolute right-0 top-0 bottom-0 w-1/2 opacity-10 pointer-events-none"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Promotional/CTA Footer Banner -->
    <section class="py-16 px-container-margin">
        <div
            class="max-w-7xl mx-auto glass-panel rounded-2xl p-12 text-center relative overflow-hidden border-primary/30">
            <div class="absolute inset-0 bg-primary/5"></div>
            <div class="relative z-10">
                <h2 class="font-display-lg text-display-lg text-white mb-6">¿LISTO PARA EL MANDO?</h2>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-2xl mx-auto">
                    Únete a la élite de los deportes de combate. Regístrate hoy y obtén acceso exclusivo a
                    Parada.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-gutter">
                    <button
                        class="bg-primary-container text-on-primary-container font-bold px-10 py-4 rounded-xl text-lg hover:brightness-110 glow-primary transition-all">Empieza
                        Ahora</button>
                    <button
                        class="border border-white/20 text-white font-bold px-10 py-4 rounded-xl text-lg hover:bg-white/5 transition-all">Ver
                        Demostración</button>
                </div>
            </div>
        </div>
    </section>
</div>
