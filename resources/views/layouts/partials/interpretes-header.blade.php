  <style>
    .list-group-item-action {
      transition: background-color 0.3s, color 0.3s;
      background-color: #343a40;
      color: white;
    }

    .list-group-item-action:hover {
      color: #ffc107;
      background-color: #343a40;
    }

    .list-group-item-action.active {
      background-color: #ffc107;
      color: #000;
      border-color: #ffc107;
    }
  </style>

  <div class="row">
    <div class="col-md-11">
      <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" class="card-img-top"
        alt="{{ $interprete->interprete }}">
      <h3>{{ $interprete->interprete }}</h3>
      <div class="list-group">
        <a href="{{ route('interprete.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('interprete.show') ? 'disabled active' : '' }}">
          Biografía
        </a>
        <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('interprete.noticias') || request()->routeIs('interprete.noticia.show') ? 'disabled active' : '' }}">
          Noticias
        </a>
        <a href="{{ route('interprete.shows', str_replace('biografia-de-', '', $interprete->slug)) }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('interprete.shows') ? 'disabled active' : '' }}">
          Shows
        </a>
        <a href="{{ route('interprete.discografia', str_replace('biografia-de-', '', $interprete->slug)) }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('interprete.discografia') || request()->routeIs('interprete.album.show') ? 'disabled active' : '' }}">
          Discos
        </a>
        <a href="{{ route('interprete.canciones', str_replace('biografia-de-', '', $interprete->slug)) }}"
          class="list-group-item list-group-item-action {{ request()->routeIs('interprete.canciones') || request()->routeIs('canciones.show') ? 'disabled active' : '' }}">
          Canciones
        </a>
      </div>


      {{-- 
    <div class="flex flex-col items-center space-y-4">
  <div class="flex">
    <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
      class="h-auto w-auto">
  </div>
  <h1 class="text-2xl font-semibold">{{ $interprete->interprete }}</h1>
  <div class="flex flex-wrap space-x-4">
    <div class="flex items-center">
      <i class="fas fa-map-marker-alt mr-2"></i>
      <span>{{ $interprete->location }}</span>
    </div>
    <div class="flex items-center">
      <i class="fas fa-phone mr-2"></i>
      <span>{{ $interprete->phone }}</span>
    </div>
    <div class="flex items-center">
      <i class="fas fa-envelope mr-2"></i>
      <span>{{ $interprete->email }}</span>
    </div>
  </div>

  @if ($interprete->facebook || $interprete->twitter || $interprete->instagram)
    <div class="flex space-x-4">
      @if ($interprete->facebook)
        <a href="{{ $interprete->facebook }}" target="_blank" class="text-blue-600 hover:text-blue-800">
          <i class="fab fa-facebook-f"></i>
        </a>
      @endif
      @if ($interprete->twitter)
        <a href="{{ $interprete->twitter }}" target="_blank" class="text-blue-400 hover:text-blue-600">
          <i class="fab fa-twitter"></i>
        </a>
      @endif
      @if ($interprete->instagram)
        <a href="{{ $interprete->instagram }}" target="_blank" class="text-pink-600 hover:text-pink-800">
          <i class="fab fa-instagram"></i>
        </a>
      @endif
    </div>
  @endif
 --}}


    </div>
  </div>

  <!-- resources/views/partials/select-interprete.blade.php -->
  <div class="container mt-5 mb-4">

    @if ($section === 'noticias')
      <h2>Explora noticias de otros Intérpretes</h2>
      <p class="lead">
        Explora las últimas noticias de otros intérpretes del folklore argentino. Selecciona un artista de la lista a
        continuación y descubre las novedades sobre su carrera y su música.
      </p>
    @elseif($section === 'discografias')
      <h2>Explora más Discografías</h2>
      <p class="lead">
        Descubre la música de otros intérpretes del folklore argentino. Elige un artista de la lista a continuación y
        sumérgete en su discografía completa, desde sus primeras grabaciones hasta sus últimos éxitos.
      </p>
    @elseif($section === 'canciones')
      <h2>Encuentra más Letras de Canciones</h2>
      <p class="lead">
        Explora las letras de canciones de otros intérpretes del folklore argentino. Selecciona un artista de la lista
        a
        continuación y disfruta de las palabras y los mensajes que caracterizan su música.
      </p>
    @elseif($section === 'shows')
      <h2>Descubre más Shows y Eventos</h2>
      <p class="lead">
        No te pierdas la oportunidad de ver en vivo a otros intérpretes del folklore argentino. Elige un artista de
        la
        lista a continuación y consulta su cartelera de shows y eventos para disfrutar de su música en directo.
      </p>
    @elseif($section === 'biografias')
      <h2>Explora más Biografías</h2>
      <p class="lead">
        Conoce la historia y el legado de otros intérpretes del folklore argentino. Selecciona un artista de la
        lista a
        continuación y descubre los detalles de su vida y su carrera artística.
      </p>
    @endif

    <div class="card">
      <div class="card-body">
        {{-- <h5 class="card-title">Cambiar de Intérprete</h5> --}}
        <form id="change-interprete-form">
          <div class="form-group">
            {{-- <label for="interprete-select">Seleccione un intérprete:</label> --}}
            <select class="form-control" id="interprete-select">
              <option value="">-- Seleccione un interprete --</option>
              @foreach ($interpretes as $interprete)
                <option value="{{ $interprete->slug }}">{{ $interprete->interprete }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('interprete-select').addEventListener('change', function() {
      var slug = this.value;
      var currentUrl = window.location.href;
      var newUrl = currentUrl.replace(/\/[^\/]+$/, '/' + slug);
      window.location.href = newUrl;
    });
  </script>
