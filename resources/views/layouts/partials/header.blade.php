<!-- Encabezado sticky -->
<header class="sticky-top bg-black">

  <nav class="container-fluid navbar navbar-expand-lg bg-black py-0 menutop shadow border-bottom border-warning">
    <div class="container-md">

      <a class="navbar-brand col-6 col-md-3 col-lg-3 mb-2 mt-2" href="{{ route('home') }}"
        title="Inicio Mi Folklore Argentino">
        {{-- <img src="{{ asset('img/mfa.jpg') }}" width="50" alt="Mi Folklore Argentino" class="img-fluid float-left"> --}}
        <b><span class="fs-4 text-primary">Mi Folk</span><span class="fs-4 text-white">lor</span><span
            class="fs-4 text-warning">e</span>
          <span class="fs-4 text-white">Arg</span><span class="fs-4 text-primary">entino</span></b>
      </a>

      <button class="navbar-toggler btn btn-warning" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <!-- Opciones de navegación -->
          <li class="nav-item {{ request()->segment(1) == 'biografias' ? 'active' : '' }}">
            <a href="{{ route('interpretes.index') }}" title="Biografias de interpretes"
              class="nav-link">Interpretes</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'noticias' ? 'active' : '' }}">
            <a href="{{ route('noticias.index') }}" title="Noticias del folklore argentino"
              class="nav-link">Noticias</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'cartelera' ? 'active' : '' }}">
            <a href="{{ route('shows.index') }}" title="Cartelera de shows folkloricos" class="nav-link">Shows</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'discografias' ? 'active' : '' }}">
            <a href="{{ route('discos.index') }}" title="Discografias del folklore" class="nav-link">Discos</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'letras-de-canciones' ? 'active' : '' }}">
            <a href="{{ route('canciones.index') }}" title="Letras de canciones" class="nav-link">Canciones</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'fiestas-tradicionales-argentina' ? 'active' : '' }}">
            <a href="{{ route('festivales.index') }}" title="Festivales y fiestas tradicionales"
              class="nav-link">Festivales</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'mitos' ? 'active' : '' }}">
            <a href="{{ route('mitos.index') }}" title="Mitos y leyendas urbanas" class="nav-link">Mitos</a>
          </li>
          <li class="nav-item {{ request()->segment(1) == 'comidas' ? 'active' : '' }}">
            <a href="{{ route('comidas.index') }}" title="Recetas de comidas tipicas" class="nav-link">Comidas</a>
          </li>

          <!-- Opciones de autenticación -->
          {{-- 
          @guest
            <li class="nav-item">
              <a href="{{ route('login') }}" class="btn btn-warning"><span class="fas fa-sign-in-alt ml-8"></span>
                Ingresar</a>
            </li>
          @else
            <li class="nav-item dropdown">
              <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ Auth::user()->name }}
              </a>

              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ url('/admin/misdatos') }}">Mis datos</a>
                <a class="dropdown-item" href="{{ url('/admin') }}">Mis contenidos</a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  Salir
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </li>
          @endguest 
          --}}
        </ul>
      </div>
    </div>
  </nav>
</header>

<!-- Agrega los estilos personalizados -->
<style>
  .navbar {
    background-color: #000000;
    /* Fondo negro para el header */
  }

  .navbar-nav .nav-link {
    color: #ffffff;
    /* Letra blanca para los enlaces */
    font-weight: bold;

  }

  .navbar-nav .nav-link:hover {
    color: #ffc107;
    /* Amarillo en el hover */
  }

  .navbar-nav .nav-item.active .nav-link {
    background-color: #ffc107;
    /* Fondo amarillo para el enlace activo */
    color: #000000;
    /* Letra blanca para el enlace activo */
    border-radius: 0.25rem;
  }

  .btn-warning {
    color: #000000;
    /* Botón con color de fondo de advertencia y letra negra */
    border-color: #ffc107;
  }

  .btn-warning:hover {
    background-color: #ffca2c;
    border-color: #ffbf00;
  }
</style>
