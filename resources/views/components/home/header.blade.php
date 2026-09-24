{{-- HOME — HEADER --}}

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
