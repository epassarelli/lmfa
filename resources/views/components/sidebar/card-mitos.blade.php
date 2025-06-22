<div>
  <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Mitos y leyendas
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($mitos as $mito)
        <article class="flex items-center gap-3 border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <img src="{{ asset('img/mito-default.svg') }}" alt="{{ $mito->titulo }}" class="w-12 h-12 object-contain">
          <div class="flex-1">
            <a href="{{ route('mito.show', $mito->slug) }}" class="hover:text-[#ff661f] font-semibold">
              {{ Str::limit($mito->titulo, 50) }}
            </a>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
