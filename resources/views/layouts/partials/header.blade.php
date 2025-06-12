<!-- Encabezado sticky con Tailwind -->
<header class="sticky top-0 bg-black z-50 shadow border-b border-yellow-400">
  <nav class="container mx-auto px-4 py-2 flex items-center justify-between flex-wrap">

    {{-- LOGO --}}
    <a href="{{ route('home') }}" title="Inicio Mi Folklore Argentino" class="flex items-center mb-2 mt-2">
      <span class="text-blue-300 text-xl font-bold">Mi Folk</span>
      <span class="text-white text-xl font-bold">lor</span>
      <span class="text-yellow-400 text-xl font-bold">e</span>
      <span class="text-white text-xl font-bold">Arg</span>
      <span class="text-blue-300 text-xl font-bold">entino</span>
    </a>

    {{-- Botón hamburguesa --}}
    <button @click="open = !open" class="text-yellow-400 lg:hidden focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    {{-- Menú --}}
    <div :class="{ 'block': open, 'hidden': !open }"
      class="w-full lg:flex lg:items-center lg:w-auto hidden mt-2 lg:mt-0" x-data="{ open: false }">
      <ul class="lg:flex lg:space-x-6 text-white text-sm font-medium">
        <li>
          <a href="{{ route('interpretes.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ Route::is('interpretes.index') ? 'text-yellow-400' : '' }}">
            Biografías
          </a>
        </li>
        <li>
          <a href="{{ route('noticias.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ request()->segment(1) == 'noticias-de-folklore-argentino' ? 'text-yellow-400' : '' }}">
            Noticias
          </a>
        </li>
        <li>
          <a href="{{ route('shows.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ request()->segment(1) == 'cartelera-de-eventos-folkloricos' ? 'text-yellow-400' : '' }}">
            Cartelera
          </a>
        </li>
        <li>
          <a href="{{ route('discos.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ request()->segment(1) == 'discografias-del-folklore-argentino' ? 'text-yellow-400' : '' }}">
            Discos
          </a>
        </li>
        <li>
          <a href="{{ route('canciones.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ request()->segment(1) == 'letras-de-canciones-folkloricas' ? 'text-yellow-400' : '' }}">
            Canciones
          </a>
        </li>
        <li>
          <a href="{{ route('festivales.index') }}"
            class="block py-2 px-2 hover:text-yellow-400 transition {{ request()->segment(1) == 'festivales-y-fiestas-tradicionales' ? 'text-yellow-400' : '' }}">
            Festivales
          </a>
        </li>
      </ul>
    </div>
  </nav>
</header>
