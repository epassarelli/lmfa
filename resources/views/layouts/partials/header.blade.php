<div>
  <!-- imagen enca top -->
  <div class="container-fluid overflow-hidden p-0">
    <img src="{{ asset('img/enca.jpg') }}" alt="Mi Folklore Argentino" class="d-none d-lg-block d-sm-none w-full">
  </div>
  <!-- NAV / menú -->
  <header class="sticky-md-top border-top border-5 border-primary">

    <nav class="container-fluid navbar navbar-expand-lg bg-white py-0 menutop shadow">
      <div class="container-md">
        <a class="navbar-brand col-6 col-md-3 col-lg-3" href="{{ route('home') }}" title="Inicio Mi Folklore Argentino">
          <!-- logo -->
          <img src="{{ asset('img/placasur.png') }}" alt=" Mi Folklore Argentino" class="img-fluid float-left">
          <h1 class="visually-hidden"> Mi Folklore Argentino</h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item {{ request()->routeIS('interpretes') ? 'active' : '' }}">
              <a href="{{ route('interpretes.index') }}" title="Página principal" class="nav-link">interpretes <span
                  class="visually-hidden">(interpretes)</span></a>
            </li>
            <li class="nav-item {{ request()->routeIS('noticias.index') ? 'active' : '' }}">
              <a href="{{ route('noticias.index') }}" title="Nuestros noticias" class="nav-link">noticias</a>
            </li>
            <li class="nav-item {{ request()->routeIS('shows.index') ? 'active' : '' }}">
              <a href="{{ route('shows.index') }}" title="Nuestros shows" class="nav-link">shows</a>
            </li>


            <li class="nav-item {{ request()->routeIS('discos.index') ? 'active' : '' }}">
              <a href="{{ route('discos.index') }}" title="Nuestras actividades" class="nav-link">discos</a>
            </li>
            <li class="nav-item {{ request()->routeIS('canciones.index') ? 'active' : '' }}">
              <a href="{{ route('canciones.index') }}" title="Nuestras actividades" class="nav-link">canciones</a>
            </li>
            <li class="nav-item {{ request()->routeIS('festivales.index') ? 'active' : '' }}">
              <a href="{{ route('festivales.index') }}" title="Nuestras actividades" class="nav-link">festivales</a>
            </li>
            <li class="nav-item {{ request()->routeIS('mitos.index') ? 'active' : '' }}">
              <a href="{{ route('mitos.index') }}" title="Nuestras actividades" class="nav-link">mitos</a>
            </li>
            <li class="nav-item {{ request()->routeIS('comidas.index') ? 'active' : '' }}">
              <a href="{{ route('comidas.index') }}" title="Nuestras actividades" class="nav-link">comidas</a>
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
