<div class="flex flex-col items-center">

  <div class="w-full">
    {{-- Imagen del intérprete --}}
    <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" loading="lazy" width="400" height="400"
      class="rounded shadow-md object-cover" alt="{{ $interprete->interprete }}" title="{{ $interprete->interprete }}">

    {{-- Menú de navegación por secciones --}}
    <nav class="flex flex-col border border-gray-300 rounded overflow-hidden shadow-md">

      @php
        function activeItem($routeName)
        {
            return request()->routeIs($routeName)
                ? 'bg-[#ff661f] text-white font-semibold'
                : 'bg-white text-gray-800 hover:bg-[#fff4ee] hover:text-[#ff661f]';
        }
      @endphp

      <a href="{{ route('artista.biografia', $interprete->slug) }}"
        class="px-4 py-3 border-b border-gray-200 transition-colors {{ activeItem('artista.biografia') }}">
        Biografía de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('artista.noticias', $interprete->slug) }}"
        class="px-4 py-3 border-b border-gray-200 transition-colors {{ activeItem('artista.noticias') }}">
        Noticias de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('artista.shows', $interprete->slug) }}"
        class="px-4 py-3 border-b border-gray-200 transition-colors {{ activeItem('artista.shows') }}">
        Shows de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('artista.discografia', $interprete->slug) }}"
        class="px-4 py-3 border-b border-gray-200 transition-colors {{ activeItem('artista.discografia') }}">
        Discografía de {{ $interprete->interprete }}
      </a>

      <a href="{{ route('artista.canciones', $interprete->slug) }}"
        class="px-4 py-3 transition-colors {{ activeItem('artista.canciones') }}">
        Canciones por {{ $interprete->interprete }}
      </a>

    </nav>
  </div>

  {{-- Texto contextual según sección --}}
  <div class="w-full my-4 shadow-md">
    <div class="bg-white rounded p-4">
      @php $segment = request()->segment(2); @endphp

      @if ($segment === 'noticias')
        <h2 class="text-md font-semibold mb-2">Noticias de otros artistas</h2>
        <p class="text-gray-700">
          Explora las últimas noticias de otros intérpretes del folklore argentino...
        </p>
      @elseif($segment === 'discografia')
        <h2 class="text-md font-semibold mb-2">Discografía de otros artistas</h2>
        <p class="text-gray-700">
          Descubre la música de otros intérpretes del folklore argentino...
        </p>
      @elseif($segment === 'letras')
        <h2 class="text-md font-semibold mb-2">Letras de otros artistas</h2>
        <p class="text-gray-700">
          Explora las letras de canciones de otros intérpretes del folklore argentino...
        </p>
      @elseif($segment === 'shows')
        <h2 class="text-md font-semibold mb-2">Shows de otros artistas</h2>
        <p class="text-gray-700">
          No te pierdas la oportunidad de ver en vivo a otros intérpretes...
        </p>
      @elseif($segment === 'biografia')
        <h2 class="text-md font-semibold mb-2">Biografías de otros artistas</h2>
        <p class="text-gray-700">
          Conoce la historia y el legado de otros intérpretes del folklore argentino...
        </p>
      @endif


      {{-- Selector de intérpretes --}}

      <h3 class="text-md font-semibold text-gray-800 mt-4 mb-2 pb-1 border-b-2 border-[#ff661f]">
        Cambiar de Intérprete
      </h3>
      <select id="interprete-select"
        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-yellow-400">
        <option value="">Otro intérprete</option>
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
