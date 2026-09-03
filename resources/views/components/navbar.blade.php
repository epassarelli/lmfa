<div>
  <!-- Life is available only in the present moment. - Thich Nhat Hanh -->
  <nav class="bg-gray-900 text-white shadow sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between flex-wrap">

      <!-- Redes sociales y Login (solo en desktop) -->
      {{-- <div class="hidden lg:flex items-center space-x-4">
        <a href="#" class="text-white hover:text-[#ff661f]"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-white hover:text-[#ff661f]"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-white hover:text-[#ff661f]"><i class="fab fa-instagram"></i></a>
        <a href="#"
          class="bg-[#ff661f] text-black px-3 py-1 rounded hover:bg-orange-500 text-sm font-bold">Login</a>
      </div> --}}

      <!-- Logo -->
      <a href="{{ route('home') }}" title="Inicio Mi Folklore Argentino" class="flex items-center ">
        <span class="text-blue-300 text-xl font-bold">Mi Folk</span>
        <span class="text-white text-xl font-bold">lor</span>
        <span class="text-yellow-400 text-xl font-bold">e</span>
        <span class="text-white text-xl font-bold">Arg</span>
        <span class="text-blue-300 text-xl font-bold">entino</span>
      </a>

      <!-- Botón hamburguesa -->
      <button type="button" data-mobile-menu-toggle aria-expanded="false" aria-controls="site-mobile-menu" class="lg:hidden text-[#ff661f] focus:outline-none ml-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>


      <!-- Menú -->
      <div id="site-mobile-menu" data-mobile-menu
        class="w-full lg:flex lg:items-center lg:w-auto hidden mt-4 lg:mt-0">
        <ul class="lg:flex lg:items-center lg:gap-1 text-white text-sm font-medium">
          <li>
            <a href="{{ route('interpretes.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Artistas</a>
          </li>
          <li>
            <a href="{{ route('noticias.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Noticias</a>
          </li>
          <li>
            <a href="{{ route('cartelera.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Cartelera</a>
          </li>
          <li>
            <a href="{{ route('festivales.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Festivales</a>
          </li>
          <li>
            <a href="{{ route('penia-profiles.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Peñas</a>
          </li>
          <li>
            <a href="{{ route('radios.index') }}" class="block rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">Radios</a>
          </li>
          <li>
            <details class="group relative">
              <summary class="flex cursor-pointer list-none items-center gap-1 rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">
                Cultura
                <svg class="h-3.5 w-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.09 1.03l-4.25 4.51a.75.75 0 01-1.09 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
              </summary>
              <div class="mt-1 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 text-slate-800 shadow-xl lg:absolute lg:left-0 lg:z-50 lg:mt-2 lg:w-56">
                <a href="{{ route('enciclopedia.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Enciclopedia</a>
                <a href="{{ route('mitos.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Mitos y leyendas</a>
                <a href="{{ route('comidas.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Recetas tradicionales</a>
              </div>
            </details>
          </li>
          <li>
            <details class="group relative">
              <summary class="flex cursor-pointer list-none items-center gap-1 rounded-md px-3 py-2 transition hover:bg-white/10 hover:text-[#ff661f]">
                Más
                <svg class="h-3.5 w-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.09 1.03l-4.25 4.51a.75.75 0 01-1.09 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
              </summary>
              <div class="mt-1 overflow-hidden rounded-lg border border-slate-200 bg-white py-1 text-slate-800 shadow-xl lg:absolute lg:right-0 lg:z-50 lg:mt-2 lg:w-52">
                <a href="{{ route('discografias.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Discos</a>
                <a href="{{ route('canciones.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Canciones</a>
                <a href="{{ route('classifieds.index') }}" class="block px-4 py-2.5 transition hover:bg-orange-50 hover:text-orange-700">Clasificados</a>
              </div>
            </details>
          </li>
          <li class="mt-2 lg:mt-0 lg:ml-1">
            <a href="{{ route('backend.contributions.index') }}" class="inline-flex rounded-md bg-[#ff661f] px-3 py-2 font-semibold text-white transition hover:bg-orange-600">Publicar</a>
          </li>
        </ul>

        <!-- Buscador -->
        <div class="mt-4 lg:mt-0 lg:ml-6 w-full lg:w-auto">
          <form action="{{ route('buscar') }}" method="GET"
            class="flex border border-gray-300 rounded overflow-hidden bg-white">
            <input type="text" name="q" placeholder="Buscar..."
              class="px-3 py-2 text-sm text-black focus:outline-none w-full">
            <button type="submit"
              class="px-3 bg-[#ff661f] text-white text-sm hover:bg-orange-600 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
              </svg>
            </button>
          </form>

        </div>
      </div>
    </div>
  </nav>




</div>
