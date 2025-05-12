<div>
  <div class="single-most-popular-news">
    <div class="popular-news-image">
      <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">
        <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}">
      </a>
    </div>

    <div class="popular-news-content">
      <p>
        <span>{{ $noticia->categoria->nombre }}</span> |
        {{ $noticia->created_at ? $noticia->created_at->translatedFormat('d F, Y') : '' }}
      </p>

      <h3>
        <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">
          {{ $noticia->titulo }}
        </a>
      </h3>
    </div>
  </div>

</div>
