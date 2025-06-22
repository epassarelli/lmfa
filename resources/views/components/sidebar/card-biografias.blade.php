<div>
  <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
  <section class="bg-white p-1 rounded shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Últimas biografías
    </h3>

    <div class="space-y-3">
      @foreach ($interpretes as $artista)
        <article
          class="flex gap-4 items-start bg-white border border-gray-200 rounded shadow-md hover:shadow transition p-1">
          <a href="{{ route('interprete.show', $artista->slug) }}" class="shrink-0">
            <img
              src="{{ file_exists(public_path('storage/interpretes/' . $artista->foto)) && $artista->foto
                  ? asset('storage/interpretes/' . $artista->foto)
                  : asset('img/interprete.jpg') }}"
              alt="{{ $artista->interprete }}" class="w-16 h-16 object-cover rounded">
          </a>
          <div class="flex-1 text-base md:text-sm text-gray-700">
            <h4 class="font-semibold leading-tight">
              <a href="{{ route('interprete.show', $artista->slug) }}" class="hover:text-[#ff661f] transition">
                {{ $artista->interprete }}
              </a>
            </h4>
            <span class="text-base md:text-xs text-gray-500">
              {{ number_format($artista->visitas, 0, '', '.') }} visitas
            </span>
          </div>
        </article>
      @endforeach
    </div>
  </section>


</div>
