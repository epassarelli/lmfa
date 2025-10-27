<div>
  <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
  @props(['interprete'])

  <a href="{{ route('artista.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">

    <div class="overflow-hidden">
      <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="Foto de {{ $interprete->interprete }}"
        class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105">
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h2 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
        {{ $interprete->interprete }}
      </h2>

      <p class="text-sm text-[#ff661f] font-medium mb-2">
        Biografía destacada
      </p>

      <p class="text-sm text-gray-500 line-clamp-2">
        {!! Str::limit(strip_tags($interprete->biografia), 80) !!}
      </p>
    </div>
  </a>

</div>
