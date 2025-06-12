<div>
  <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Marie Curie -->
  @props(['letra'])

  <a href="{{ route('canciones.show', [$letra->interprete->slug, $letra->slug]) }}"
    class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 p-4 h-full">
    <div class="flex items-center space-x-4">
      <div class="flex-shrink-0 text-[#ff661f] text-3xl">
        🎵
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-800 hover:text-[#ff661f] transition-colors duration-300">
          {{ $letra->cancion }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">{{ number_format($letra->visitas, 0, '', '.') }} visitas</p>
      </div>
    </div>
  </a>

</div>
