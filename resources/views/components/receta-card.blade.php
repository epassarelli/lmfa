<div>
  <!-- When there is no desire, all things are at peace. - Laozi -->
  @props(['receta'])

  <a href="{{ route('comidas.show', $receta->slug) }}"
    class="block rounded overflow-hidden bg-white shadow-sm transition duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 flex flex-col h-full">

    <div class="overflow-hidden">
      <x-editorial-image
        :entity="$receta"
        variant="card"
        class="w-full h-50 object-cover transition-transform duration-300 ease-in-out hover:scale-105"
        loading="lazy"
      />
    </div>

    <div class="p-4 flex flex-col justify-between flex-grow">
      <h3 class="text-lg font-semibold text-gray-800 mb-1 line-clamp-2">
        {{ $receta->titulo }}
      </h3>

      <p class="text-sm text-gray-500 line-clamp-2">
        {!! Str::limit(strip_tags($receta->receta), 80) !!}
      </p>
    </div>
  </a>

</div>
