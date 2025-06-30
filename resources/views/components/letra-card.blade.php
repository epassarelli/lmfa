<div>
  <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Marie Curie -->
  @props(['letra'])

  <div class="bg-white rounded shadow-md hover:shadow-lg transition-all duration-300 flex p-1 gap-2 items-start h-full">

    {{-- Imagen del intérprete --}}
    <div class="flex-shrink-0 w-16 h-16 rounded overflow-hidden bg-gray-100 border">
      @if ($letra->interprete && $letra->interprete->foto)
        <img src="{{ asset('storage/interpretes/' . $letra->interprete->foto) }}"
          alt="{{ $letra->interprete->interprete }}" class="w-full h-full object-cover">
      @else
        <div class="w-full h-full flex items-center justify-center text-gray-400 text-2xl">🎵</div>
      @endif
    </div>

    {{-- Contenido --}}
    <div class="flex-1">

      {{-- Intérprete --}}
      @if ($letra->interprete)
        <p class="text-sm font-semibold text-[#ff661f] mb-1">{{ $letra->interprete->interprete }}</p>
      @endif

      {{-- Título de la canción --}}
      <a href="{{ $letra->interprete
          ? route('artista.cancion', [$letra->interprete->slug, $letra->slug])
          : route('canciones.show', $letra->slug) }}"
        class="text-sm font-semibold text-gray-800 hover:text-[#ff661f] transition-colors duration-300 block mb-1">
        {{ $letra->cancion }}
      </a>

      {{-- Discos relacionados --}}
      @if ($letra->albunes && $letra->albunes->count())
        <div class="flex flex-wrap gap-2 mt-2">
          @foreach ($letra->albunes as $disco)
            <a href="{{ route('artista.disco', [$disco->interprete->slug, $disco->slug]) }}"
              class="text-xs font-medium bg-[#ff661f]/10 text-[#ff661f] px-2 py-1 rounded-full hover:bg-[#ff661f]/20 transition">
              {{ $disco->album }}
            </a>
          @endforeach
        </div>
      @endif

    </div>

  </div>


</div>
