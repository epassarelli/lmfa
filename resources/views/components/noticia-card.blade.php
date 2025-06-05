<div>
  <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
  @props(['noticia'])

  <article
    class="bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300 flex flex-col h-full group">
    <div class="overflow-hidden">
      <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">
        <img
          src="{{ file_exists(public_path('storage/noticias/' . $noticia->foto)) && $noticia->foto ? asset('storage/noticias/' . $noticia->foto) : asset('img/album.jpg') }}"
          alt="{{ $noticia->titulo }}"
          class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105">
      </a>
    </div>
    <div class="p-4 flex flex-col flex-grow">
      <div class="mb-2 text-sm text-gray-500 uppercase font-medium">{{ $noticia->categoria->nombre }}</div>
      <h2 class="text-xl font-bold text-gray-900 leading-snug mb-2 group-hover:text-[#ff661f]">
        <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">{{ $noticia->titulo }}</a>
      </h2>
      <p class="text-sm text-gray-600 line-clamp-3">{{ strip_tags(Str::limit($noticia->cuerpo, 130)) }}</p>
      <div class="mt-auto pt-4 text-sm text-gray-500">
        {{ $noticia->created_at?->translatedFormat('d M Y') }}
      </div>
    </div>
  </article>
</div>
