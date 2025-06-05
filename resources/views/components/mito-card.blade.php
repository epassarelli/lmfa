<div>
  <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
  @props(['mito'])

  <a href="{{ route('mitos.show', $mito->slug) }}"
    class="block bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 p-4 h-full">
    <div class="flex items-center space-x-4">
      <div class="flex-shrink-0 text-[#ff661f] text-3xl">
        🧙‍♀️
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-800 hover:text-[#ff661f] transition-colors duration-300">
          {{ $mito->titulo }}
        </h3>
        <p class="text-sm text-gray-500 mt-1">{{ number_format($mito->visitas, 0, '', '.') }} visitas</p>
      </div>
    </div>
  </a>

</div>
