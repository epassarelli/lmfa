<div>
  <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
  @props(['interprete', 'journey' => null])

  <a href="{{ route('artista.show', str_replace('biografia-de-', '', $interprete->slug)) }}"
    @if($journey)
      data-journey-link
      data-source-type="{{ $journey['sourceType'] }}"
      data-source-id="{{ $journey['sourceId'] }}"
      data-destination-type="artist"
      data-destination-id="{{ $interprete->id }}"
      data-module="{{ $journey['module'] }}"
      data-position="{{ $journey['position'] }}"
      aria-label="Biografía de {{ $interprete->interprete }}"
    @endif
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">

    <div class="overflow-hidden">
      <x-editorial-image
        :entity="$interprete"
        variant="card"
        class="w-full h-50 object-cover transition-transform duration-300 ease-in-out hover:scale-105"
        loading="lazy"
      />
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
