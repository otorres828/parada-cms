{{--
    HOME PÚBLICO — PLATAFORMA PARADA
    --------------------------------------------------------------------------
    Presenta la solución comercial para agencias de autobuses, resume las herramientas
    operativas disponibles y captura solicitudes de empresas interesadas mediante Livewire.

    Componentes reutilizables utilizados:
    - <x-home.header />: Navegación principal y acceso administrativo.
    - <x-home.hero />: Presentación de la plataforma y vista previa del dashboard.
    - <x-home.features />: Herramientas operativas y comerciales.
    - <x-home.journey />: Flujo de compra, QR, embarque e impresión.
    - <x-home.visibility />: Presencia de las agencias en el portal público.
    - <x-home.contact />: Formulario comercial con validación Alpine y Livewire.
    - <x-home.footer />: Pie de página público.
    - <x-layout.loader.fullpage />: Indicador durante el envío de la solicitud.
    --------------------------------------------------------------------------
--}}

@section('title', 'Operación digital para agencias de autobuses')

<div>

    <x-home.header />

    <main>

        <x-home.hero />

        <x-home.features />

        <x-home.journey />

        <x-home.visibility />

        <x-home.contact :solicitud-enviada="$solicitudEnviada" />

    </main>

    <x-home.footer />

    <x-layout.loader.fullpage wire:loading.delay.longer wire:target="enviarSolicitud" />

</div>
