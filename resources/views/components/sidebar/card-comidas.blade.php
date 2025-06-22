<div>
  <!-- When there is no desire, all things are at peace. - Laozi -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Comidas típicas
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($comidas as $comida)
        <article class="flex items-center gap-3 border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <img src="{{ asset('img/comida-default.svg') }}" alt="{{ $comida->titulo }}" class="w-12 h-12 object-contain">
          <div class="flex-1">
            <a href="{{ route('comida.show', $comida->slug) }}" class="hover:text-[#ff661f] font-semibold">
              {{ Str::limit($comida->titulo, 50) }}
            </a>
          </div>
        </article>
      @endforeach
    </div>
  </section>

</div>
