<div>
  <!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
  @props(['noticia', 'interprete'])

  @php
    use Illuminate\Support\Str;

    $principal = $noticia->interprete_id ? \App\Models\Interprete::find($noticia->interprete_id) : null;

    $url = $principal
        ? route('artista.noticia', [$principal->slug, $noticia->slug])
        : route('noticias.show', ['slug' => $noticia->slug]);

    $mostrarAutorExterno = $interprete && $principal && $interprete->id !== $principal->id;
  @endphp

  <article class="bg-white shadow-md rounded overflow-hidden hover:shadow-lg transition-all duration-200 mb-4">
    <a href="{{ $url }}">
      <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
        class="w-full h-48 object-cover" loading="lazy">
    </a>

    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-800 hover:text-[#ff661f] transition-colors mb-2 line-clamp-2">
        <a href="{{ $url }}">
          {{ $noticia->titulo }}
        </a>
      </h3>

      <p class="text-gray-600 text-sm mb-2 line-clamp-3">
        {{ Str::limit(strip_tags($noticia->noticia), 120) }}
      </p>

      <div class="text-xs text-gray-500 flex justify-between items-center">
        <span>
          {{ $noticia->fecha_publicacion ? $noticia->fecha_publicacion->format('d M Y') : '' }}
        </span>
        <span class="italic">
          {{ $noticia->categoria->nombre ?? 'Noticias' }}
        </span>
      </div>

      <div class="text-xs text-gray-600 italic mt-1 min-h-[1.5rem]">
        @if ($mostrarAutorExterno)
          Principal: <a href="{{ route('artista.noticias', $principal->slug) }}"
            class="text-[#ff661f] hover:underline">{{ $principal->interprete }}</a>
        @else
          &nbsp;
        @endif
      </div>

    </div>
  </article>
</div>
