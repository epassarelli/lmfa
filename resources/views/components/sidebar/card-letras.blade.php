<div>
  <!-- Simplicity is the ultimate sophistication. - Leonardo da Vinci -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Letras recientes
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($canciones as $cancion)
        <article
          class="flex gap-4 items-start bg-white border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <a href="{{ route('cancion.show', $cancion->slug) }}" class="shrink-0">
            <img
              src="{{ $cancion->interprete && file_exists(public_path('storage/interpretes/' . $cancion->interprete->foto))
                  ? asset('storage/interpretes/' . $cancion->interprete->foto)
                  : asset('img/interprete.jpg') }}"
              alt="{{ $cancion->interprete->interprete ?? 'Intérprete' }}" class="w-16 h-16 object-cover rounded-md">
          </a>
          <div class="flex-1">
            <h4 class="font-semibold leading-tight">
              <a href="{{ route('cancion.show', $cancion->slug) }}" class="hover:text-[#ff661f] transition">
                {{ $cancion->titulo }}
              </a>
            </h4>
            <span class="text-xs text-gray-500">{{ $cancion->interprete->interprete ?? 'Desconocido' }}</span>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
