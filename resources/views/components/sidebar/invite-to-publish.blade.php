<div>
  <!-- When there is no desire, all things are at peace. - Laozi -->
  @guest
    <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
      <h3 class="text-md font-semibold text-gray-800 mb-2 border-b pb-1 border-b-2 border-[#ff661f]">¿Sos artista o productor?</h3>
      <p class="text-md text-gray-600 mb-2">Publicá tus shows, discos o noticias en nuestro portal.</p>
      <a href="{{ route('register') }}"
        class="inline-block bg-[#ff661f] hover:bg-orange-600 text-white text-sm py-2 px-4 rounded-lg transition-colors duration-200">
        Crear cuenta gratuita
      </a>
    </div>
  @endguest

</div>
