<div class="flex flex-col items-center gap-6">

  {{-- Imagen del intérprete --}}
  <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" loading="lazy" width="400" height="400"
    class="rounded shadow-md object-cover" alt="{{ $interprete->interprete }}" title="{{ $interprete->interprete }}">

  {{-- Menú de navegación por secciones --}}
  <div class="w-full">
    <nav class="flex flex-col border border-gray-700 rounded overflow-hidden">
      @php
        $slugLimpio = str_replace('biografia-de-', '', $interprete->slug);
      @endphp

      @php
        function itemClass($condition)
        {
            return $condition
                ? 'bg-yellow-400 text-black pointer-events-none font-semibold px-4 py-3'
                : 'bg-gray-800 text-white hover:text-yellow-400 px-4 py-3 transition-colors';
        }
      @endphp

      <a href="{{ route('interprete.show', $slugLimpio) }}"
        class="{{ itemClass(request()->routeIs('interprete.show')) }}">
        Biografía de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('interprete.noticias', $slugLimpio) }}"
        class="{{ itemClass(request()->routeIs('interprete.noticias') || request()->routeIs('interprete.noticia.show')) }}">
        Noticias de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('interprete.shows', $slugLimpio) }}"
        class="{{ itemClass(request()->routeIs('interprete.shows')) }}">
        Shows de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('interprete.discografia', $slugLimpio) }}"
        class="{{ itemClass(request()->routeIs('interprete.discografia') || request()->routeIs('interprete.album.show')) }}">
        Discografía de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('interprete.canciones', $slugLimpio) }}"
        class="{{ itemClass(request()->routeIs('interprete.canciones') || request()->routeIs('canciones.show')) }}">
        Canciones por {{ $interprete->interprete }}
      </a>
    </nav>
  </div>

  {{-- Texto contextual según sección --}}
  <div class="w-full">
    @php $segment = request()->segment(1); @endphp

    @if ($segment === 'noticias-de-folklore-argentino')
      <h2 class="text-xl font-bold mb-2">Explora noticias de otros Intérpretes</h2>
      <p class="text-gray-700">
        Explora las últimas noticias de otros intérpretes del folklore argentino...
      </p>
    @elseif($segment === 'discografias-del-folklore-argentino')
      <h2 class="text-xl font-bold mb-2">Explora más Discografías</h2>
      <p class="text-gray-700">
        Descubre la música de otros intérpretes del folklore argentino...
      </p>
    @elseif($segment === 'letras-de-canciones-folkloricas')
      <h2 class="text-xl font-bold mb-2">Encuentra más Letras de Canciones</h2>
      <p class="text-gray-700">
        Explora las letras de canciones de otros intérpretes del folklore argentino...
      </p>
    @elseif($segment === 'cartelera-de-eventos-folkloricos')
      <h2 class="text-xl font-bold mb-2">Descubre más Shows y Eventos</h2>
      <p class="text-gray-700">
        No te pierdas la oportunidad de ver en vivo a otros intérpretes...
      </p>
    @elseif($segment === 'biografias-de-artistas-folkloricos')
      <h2 class="text-xl font-bold mb-2">Explora más Biografías</h2>
      <p class="text-gray-700">
        Conoce la historia y el legado de otros intérpretes del folklore argentino...
      </p>
    @endif
  </div>

  {{-- Selector de intérpretes --}}
  <div class="w-full">
    <div class="bg-white shadow rounded p-4">
      <label for="interprete-select" class="block text-sm font-medium text-gray-700 mb-1">
        Cambiar de Intérprete
      </label>
      <select id="interprete-select"
        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-yellow-400">
        <option value="">► Seleccione otro intérprete</option>
        @foreach ($interpretes as $item)
          <option value="{{ $item->slug }}">{{ $item->interprete }}</option>
        @endforeach
      </select>
    </div>
  </div>

</div>

<script>
  const select = document.getElementById('interprete-select');
  if (select) {
    select.addEventListener('change', function() {
      if (this.value) {
        window.location.href = '/' + this.value;
      }
    });
  }
</script>
