<div>
  <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Próximos shows
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($shows as $show)
        <article
          class="flex gap-4 items-start bg-white border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <a href="{{ route('shows.show', $show->slug) }}" class="shrink-0">
            <img
              src="{{ $show->interprete && file_exists(public_path('storage/interpretes/' . $show->interprete->foto))
                  ? asset('storage/interpretes/' . $show->interprete->foto)
                  : asset('img/interprete.jpg') }}"
              alt="{{ $show->interprete->interprete ?? 'Artista' }}" class="w-16 h-16 object-cover rounded-md">
          </a>
          <div class="flex-1">
            <h4 class="font-semibold leading-tight">
              <a href="{{ route('shows.show', $show->slug) }}" class="hover:text-[#ff661f] transition">
                {{ Str::limit($show->titulo, 60) }}
              </a>
            </h4>
            <span class="text-xs text-gray-500">{{ $show->fecha->format('d/m/Y') }}</span>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
