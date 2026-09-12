<div>
  <!-- When there is no desire, all things are at peace. - Laozi -->
  @props(['receta'])

  <a href="{{ route('comidas.show', $receta->slug) }}"
    class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 p-4 h-full">
    <div class="flex items-center space-x-4">
      <div class="flex-shrink-0 text-[#ff661f] text-3xl">
        <x-editorial-image
          :entity="$receta"
          variant="card"
          :minimal="true"
          class="rounded w-[50px] h-[50px] object-cover"
          loading="lazy"
        />
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-800 hover:text-[#ff661f] transition-colors duration-300">
          {{ $receta->titulo }}
          @if ($receta->created_at && $receta->created_at->gt(now()->subDays(14)))
            <span class="ml-1 align-middle bg-[#ff661f] text-white text-xs font-semibold px-2 py-0.5 rounded-full">Nuevo</span>
          @endif
        </h3>
        <p class="text-sm text-gray-500 mt-1">{{ number_format($receta->visitas, 0, '', '.') }} visitas</p>
      </div>
    </div>
  </a>

</div>
