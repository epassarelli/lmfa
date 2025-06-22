<div>
  <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Últimos festivales
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($festivales as $festival)
        <article class="bg-white border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <h4 class="font-semibold leading-tight text-gray-800">
            <a href="{{ route('festival.show', $festival->slug) }}" class="hover:text-[#ff661f] transition">
              {{ Str::limit($festival->nombre, 70) }}
            </a>
          </h4>
          <span class="text-xs text-gray-500">{{ $festival->provincia->nombre ?? 'Provincia' }}</span>
        </article>
      @endforeach
    </div>
  </section>

</div>
