<div>
  <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
  @props(['disco'])

  {{-- <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full"> --}}
  <a href="{{ $disco->interprete
      ? route('artista.disco', [$disco->interprete->slug, $disco->slug])
      : route('discos.show', $disco->slug) }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">
    <div class="overflow-hidden">
      <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}"
        class="w-full h-48 object-cover transition-transform duration-300 ease-in-out hover:scale-105">
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h2 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">{{ $disco->album }}</h2>
      <p class="text-sm text-gray-500 mb-3">{{ $disco->interprete->interprete }}</p>
      <span class="text-sm font-semibold text-[#ff661f] mt-auto">
        {{ $disco->anio ?? 'Sin año' }}
      </span>
    </div>
  </a>

</div>
