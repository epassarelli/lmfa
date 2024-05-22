<style>
  /* Ajuste del espacio superior para evitar superposiciones */
  main {
    padding-top: 70px;
    /* Altura del menú */
  }

  /* Ajuste del z-index para asegurar que el menú sea visible */
  .menutop {
    z-index: 1000;
  }

  /* Estilos para los elementos del menú */
  .navbar-nav .nav-item {
    margin-right: 15px;
  }

  /* Color de fondo y texto para el elemento activo */
  .navbar-nav .nav-item.active a.nav-link {
    background-color: #8B4513;
    /* Marrón */
    color: white;
    pointer-events: none;
    /* Deshabilita el link del elemento activo */
  }

  /* Color de fondo para el efecto de hover */
  .navbar-nav .nav-item a.nav-link:hover {
    background-color: #f5d6c0;
    /* Color de fondo de Bootstrap */
    color: #8B4513;
    /* Naranja */
  }
</style>

<div>

  <!-- NAV / menú -->
  <header class="sticky-top">
    <nav class="container-fluid navbar navbar-expand-lg bg-white py-0 menutop shadow">
      <div class="container-md">
        <a class="navbar-brand col-6 col-md-3 col-lg-3" href="{{ route('home') }}" title="Inicio Mi Folklore Argentino">
          <!-- logo -->
          <img src="{{ asset('img/mfa.jpg') }}" width="60" height="60" alt="Mi Folklore Argentino"
            class="img-fluid float-left">
          <h1 class="visually-hidden">Mi Folklore Argentino</h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item {{ request()->routeIS('interpretes.index') ? 'active' : '' }}">
              <a href="{{ route('interpretes.index') }}" title="Página principal" class="nav-link">Interpretes</a>
            </li>
            <li class="nav-item {{ request()->routeIS('noticias.index') ? 'active' : '' }}">
              <a href="{{ route('noticias.index') }}" title="Nuestros noticias" class="nav-link">Noticias</a>
            </li>
            <li class="nav-item {{ request()->routeIS('shows.index') ? 'active' : '' }}">
              <a href="{{ route('shows.index') }}" title="Nuestros shows" class="nav-link">Shows</a>
            </li>
            <li class="nav-item {{ request()->routeIS('discos.index') ? 'active' : '' }}">
              <a href="{{ route('discos.index') }}" title="Nuestras actividades" class="nav-link">Discos</a>
            </li>
            <li class="nav-item {{ request()->routeIS('canciones.index') ? 'active' : '' }}">
              <a href="{{ route('canciones.index') }}" title="Nuestras actividades" class="nav-link">Canciones</a>
            </li>
            <li class="nav-item {{ request()->routeIS('festivales.index') ? 'active' : '' }}">
              <a href="{{ route('festivales.index') }}" title="Nuestras actividades" class="nav-link">Festivales</a>
            </li>
            <li class="nav-item {{ request()->routeIS('mitos.index') ? 'active' : '' }}">
              <a href="{{ route('mitos.index') }}" title="Nuestras actividades" class="nav-link">Mitos</a>
            </li>
            <li class="nav-item {{ request()->routeIS('comidas.index') ? 'active' : '' }}">
              <a href="{{ route('comidas.index') }}" title="Nuestras actividades" class="nav-link">Comidas</a>
            </li>
            <!-- contacto -->
            {{-- <li class="nav-item {{ request()->routeIS('contacto.index') ? 'active' : '' }}">
              <a href="{{ route('contacto.index') }}" title="Contactanos" class="nav-link">Contacto</a>
            </li> --}}
            <!-- buscador -->
            {{-- TODO: Se deja comentado hasta que se tenga una definición de la búsqueda --}}
            {{-- <li class="nav-item search">
              <form>
                <div class="animated-search m-md-0 mb-sm-4">
                  <input type="search" id="animated-input">
                  <a href="#">
                    <i class="fas fa-search" id="searchBtn"></i>
                  </a>
                </div>
              </form>
            </li> --}}

          </ul>
          {{-- <li class="nav-item dropdown {{ request()->routeIS('productos') ? 'active' : '' }}">
                      <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Productos
                      </a>
                      <div class="dropdown-menu bg-light mt-1 shadow-sm" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('productos') }}">Placas</a>
                        <a class="dropdown-item" href="#">Adhesivos y Barnices</a>
                        <a class="dropdown-item" href="#">Molduras</a>
                        <a class="dropdown-item" href="#">Enchapados</a>
                        <a class="dropdown-item" href="#">Construcción</a>
                        <a class="dropdown-item" href="#">Tapacantos</a>
                        <a class="dropdown-item" href="#">Herrajes</a>
                      </div>
                    </li> --}}
        </div>
        <!-- </li>
      </ul>
    </div> -->
      </div>
    </nav>
  </header>
</div>
