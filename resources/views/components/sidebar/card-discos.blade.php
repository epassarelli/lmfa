<div>
  <!-- I begin to speak only when I am certain what I will say is not better left unsaid. - Cato the Younger -->
  <section class="bg-white p-1 rounded shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Últimos álbumes
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($discos as $disco)
        <article
          class="flex gap-4 items-start bg-white border border-gray-100 p-1 rounded shadow-sm hover:shadow transition">
          <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}" class="shrink-0">
            <img
              src="{{ file_exists(public_path('storage/albunes/' . $disco->foto)) && $disco->foto
                  ? asset('storage/albunes/' . $disco->foto)
                  : asset('img/album.jpg') }}"
              alt="{{ $disco->album }}" class="w-16 h-16 object-cover rounded-md">
          </a>
          <div class="flex-1">
            <h4 class="font-semibold leading-tight">
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="hover:text-[#ff661f] transition">
                {{ $disco->album }}
              </a>
            </h4>
            <span class="text-gray-600 text-xs">
              {{ $disco->interprete->interprete }}<br>
              {{ number_format($disco->visitas, 0, '', '.') }} visitas
            </span>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
