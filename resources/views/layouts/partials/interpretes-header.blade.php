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
    {{-- <div class="col-md-11"> --}}
    <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" width="400" height="400" loading="lazy"
      class="card-img-top img-fluid" alt="{{ $interprete->interprete }}" title="{{ $interprete->interprete }}">

    {{-- <h3>{{ $interprete->interprete }}</h3> --}}
    {{-- <div class="pl-8"> --}}
    <div class="list-group">
      <a href="{{ route('interprete.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('interprete.show') ? 'disabled active' : '' }}"
        title="Biografía de {{ $interprete->interprete }}">
        Biografía de {{ $interprete->interprete }}
      </a>
      <a href="{{ route('interprete.noticias', str_replace('biografia-de-', '', $interprete->slug)) }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('interprete.noticias') || request()->routeIs('interprete.noticia.show') ? 'disabled active' : '' }}"
        title="Noticias de {{ $interprete->interprete }}">
        Noticias de {{ $interprete->interprete }}
      </a>
      <a href="{{ route('interprete.shows', str_replace('biografia-de-', '', $interprete->slug)) }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('interprete.shows') ? 'disabled active' : '' }}"
        title="Shows de {{ $interprete->interprete }}">
        Shows de {{ $interprete->interprete }}
      </a>
      <a href="{{ route('interprete.discografia', str_replace('biografia-de-', '', $interprete->slug)) }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('interprete.discografia') || request()->routeIs('interprete.album.show') ? 'disabled active' : '' }}"
        title="Discografía de {{ $interprete->interprete }}">
        Discografía de {{ $interprete->interprete }}
      </a>
      <a href="{{ route('interprete.canciones', str_replace('biografia-de-', '', $interprete->slug)) }}"
        class="list-group-item list-group-item-action {{ request()->routeIs('interprete.canciones') || request()->routeIs('canciones.show') ? 'disabled active' : '' }}"
        title="Letras de canciones de {{ $interprete->interprete }}">
        Canciones por {{ $interprete->interprete }}
      </a>
      {{-- </div> --}}
    </div>

    {{-- </div> --}}
    {{-- </div> --}}

    <!-- resources/views/partials/select-interprete.blade.php -->
    {{-- <div class="container mt-5 mb-4"> --}}
    <div class="my-4">
      @if (request()->segment(1) === 'noticias-de-folklore-argentino')
        <h2>Explora noticias de otros Intérpretes</h2>
        <p class="lead">
          Explora las últimas noticias de otros intérpretes del folklore argentino. Selecciona un artista de la lista a
          continuación y descubre las novedades sobre su carrera y su música.
        </p>
      @elseif(request()->segment(1) === 'discografias-del-folklore-argentino')
        <h2>Explora más Discografías</h2>
        <p class="lead">
          Descubre la música de otros intérpretes del folklore argentino. Elige un artista de la lista a continuación y
          sumérgete en su discografía completa, desde sus primeras grabaciones hasta sus últimos éxitos.
        </p>
      @elseif(request()->segment(1) === 'letras-de-canciones-folkloricas')
        <h2>Encuentra más Letras de Canciones</h2>
        <p class="lead">
          Explora las letras de canciones de otros intérpretes del folklore argentino. Selecciona un artista de la lista
          a
          continuación y disfruta de las palabras y los mensajes que caracterizan su música.
        </p>
      @elseif(request()->segment(1) === 'cartelera-de-eventos-folkloricos')
        <h2>Descubre más Shows y Eventos</h2>
        <p class="lead">
          No te pierdas la oportunidad de ver en vivo a otros intérpretes del folklore argentino. Elige un artista de
          la
          lista a continuación y consulta su cartelera de shows y eventos para disfrutar de su música en directo.
        </p>
      @elseif(request()->segment(1) === 'biografias-de-artistas-folkloricos')
        <h2>Explora más Biografías</h2>
        <p class="lead">
          Conoce la historia y el legado de otros intérpretes del folklore argentino. Selecciona un artista de la
          lista a
          continuación y descubre los detalles de su vida y su carrera artística.
        </p>
      @endif
    </div>
    <div class="card">
      {{-- <div class="card-body"> --}}
      {{-- <h5 class="card-title">Cambiar de Intérprete</h5> --}}
      {{-- <form id="change-interprete-form">
        <div class="form-group">
          <select class="form-control" id="interprete-select">
            <option value="">► Seleccione otro interprete -</option>
            @foreach ($interpretes as $interprete)
              <option value="{{ $interprete->slug }}">{{ $interprete->interprete }}</option>
            @endforeach
          </select>
        </div>
      </form> --}}
      {{-- </div> --}}
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
