<div>
  <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Marie Curie -->
  <section class="bg-white p-2 rounded-2xl shadow-sm mb-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-1 px-2">
      Últimas noticias
    </h3>

    <div class="space-y-3 text-sm text-gray-700">
      @foreach ($noticias as $noticia)
        <article class="bg-white border border-gray-100 p-2 rounded-lg shadow-sm hover:shadow transition">
          <h4 class="font-semibold leading-tight text-gray-800">
            <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}"
              class="hover:text-[#ff661f] transition">
              {{ Str::limit($noticia->titulo, 70) }}
            </a>
          </h4>
          <span class="text-xs text-gray-500">{{ $noticia->created_at->format('d/m/Y') }}</span>
        </article>
      @endforeach
    </div>
  </section>

</div>
