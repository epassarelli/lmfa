<!-- Encabezado sticky -->
<header class="sticky-top bg-light">
  <nav class="container-fluid navbar navbar-expand-lg bg-white py-0 menutop shadow">
    <div class="container-md">
      <a class="navbar-brand col-6 col-md-3 col-lg-3" href="{{ route('home') }}" title="Inicio Mi Folklore Argentino">
        <!-- logo -->
        <img src="{{ asset('img/mfa.jpg') }}" width="50" alt="Mi Folklore Argentino" class="img-fluid float-left">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <!-- Opciones de navegación -->
          <li class="nav-item {{ request()->routeIS('interpretes.index') ? 'active' : '' }}">
            <a href="{{ route('interpretes.index') }}" title="Biografias de interpretes"
              class="nav-link">Interpretes</a>
          </li>
          <li class="nav-item {{ request()->routeIS('noticias.index') ? 'active' : '' }}">
            <a href="{{ route('noticias.index') }}" title="Noticias del folklore argentino"
              class="nav-link">Noticias</a>
          </li>
          <li class="nav-item {{ request()->routeIS('shows.index') ? 'active' : '' }}">
            <a href="{{ route('shows.index') }}" title="Cartelera de shows folkloricos" class="nav-link">Shows</a>
          </li>
          <li class="nav-item {{ request()->routeIS('discos.index') ? 'active' : '' }}">
            <a href="{{ route('discos.index') }}" title="Discografias del folklore" class="nav-link">Discos</a>
          </li>
          <li class="nav-item {{ request()->routeIS('canciones.index') ? 'active' : '' }}">
            <a href="{{ route('canciones.index') }}" title="Letras de canciones" class="nav-link">Canciones</a>
          </li>
          <li class="nav-item {{ request()->routeIS('festivales.index') ? 'active' : '' }}">
            <a href="{{ route('festivales.index') }}" title="Festivales y fiestas tradicionales"
              class="nav-link">Festivales</a>
          </li>
          <li class="nav-item {{ request()->routeIS('mitos.index') ? 'active' : '' }}">
            <a href="{{ route('mitos.index') }}" title="Mitos y leyendas urbanas" class="nav-link">Mitos</a>
          </li>
          <li class="nav-item {{ request()->routeIS('comidas.index') ? 'active' : '' }}">
            <a href="{{ route('comidas.index') }}" title="Recetas de comidas tipicas" class="nav-link">Comidas</a>
          </li>
        </ul>

      </div>
    </div>
  </nav>
</header>
