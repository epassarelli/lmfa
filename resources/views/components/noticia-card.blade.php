@props(['noticia'])

@php
  use Illuminate\Support\Str;

  $interpretePrincipal = $noticia->interprete ?? null;
  $url = $interpretePrincipal
      ? route('artista.noticia', [$interpretePrincipal->slug, $noticia->slug])
      : route('noticias.show', ['slug' => $noticia->slug]);
@endphp

<article class="bg-white shadow-md rounded overflow-hidden hover:shadow-lg transition-all duration-200 mb-4">
  <a href="{{ $url }}">
    <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
      class="w-full h-48 object-cover">
  </a>
  <div class="p-4">
    <h3 class="text-lg font-semibold text-gray-800 hover:text-[#ff661f] transition-colors mb-2">
      <a href="{{ $url }}">{{ $noticia->titulo }}</a>
    </h3>

    <p class="text-gray-600 text-sm mb-2">
      {{ Str::limit(strip_tags($noticia->noticia), 120) }}
    </p>

    <div class="text-xs text-gray-500 flex justify-between items-center">
      <span>{{ $noticia->fecha_publicacion ? $noticia->fecha_publicacion->format('d M Y') : '' }}</span>
      <span class="italic">{{ $noticia->categoria->nombre ?? 'Noticias' }}</span>
    </div>
  </div>
</article>
