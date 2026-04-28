<div>
  @props(['show'])

  @php $interprete = $show->interprete; @endphp

  <a href="{{ $show->slug ? route('cartelera.show', $show->slug) : '#' }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">
    <div class="overflow-hidden">
      @if ($show->images->isNotEmpty())
        <x-optimized-image :image="$show->images->first()" variant="card" class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105" :alt="$interprete->interprete ?? $show->titulo" />
      @elseif ($interprete && $interprete->images->isNotEmpty())
        <x-optimized-image :image="$interprete->images->first()" variant="card" class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105" :alt="$interprete->interprete ?? $show->titulo" />
      @elseif ($show->imagen_destacada && file_exists(public_path('storage/' . $show->imagen_destacada)))
        <img src="{{ asset('storage/' . $show->imagen_destacada) }}" alt="{{ $interprete->interprete ?? $show->titulo }}"
            class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105" loading="lazy">
      @elseif ($interprete && $interprete->foto && file_exists(public_path('storage/interpretes/' . $interprete->foto)))
        <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
            class="w-full h-96 object-cover transition-transform duration-300 ease-in-out hover:scale-105" loading="lazy">
      @else
        <x-image-placeholder class="w-full h-96" />
      @endif
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h3 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
        {{ $interprete->interprete ?? $show->titulo }}
      </h3>

      <p class="text-sm text-gray-500">{{ $show->lugar }}</p>

      <div class="text-sm text-gray-700 mt-2">
        <span class="font-medium text-[#ff661f]">
          {{ \Carbon\Carbon::parse($show->fecha)->format('d M Y') }}
        </span>
      </div>

      <p class="text-sm text-gray-500 mt-2 line-clamp-2">
        {!! Str::limit(strip_tags($show->detalle), 80) !!}
      </p>
    </div>
  </a>

</div>
