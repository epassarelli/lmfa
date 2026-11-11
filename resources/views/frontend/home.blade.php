@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section>
    <section class="mb-6 rounded bg-white p-2 shadow-sm">
      <h1 class="mb-4 border-b-2 border-[#ff661f] text-xl font-semibold text-gray-900">{{ $h1 }}</h1>
      <p class="text-base text-gray-700">
        Noticias, artistas, discos, festivales y recetas del folklore argentino, en un archivo que crece todos los días.
      </p>
    </section>

    @php
      $bloques = [
        'El portal del folklore argentino' => $ultimasNoticias,
      ];
    @endphp

    @foreach ($bloques as $titulo => $noticias)
      <div class="mb-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          @foreach ($noticias as $noticia)
            <x-noticia-card
              :noticia="$noticia"
              :image-loading="$loop->first ? 'eager' : 'lazy'"
              :image-fetchpriority="$loop->first ? 'high' : 'auto'"
              :image-sizes="$loop->first ? '(max-width: 768px) 100vw, 50vw' : '(max-width: 768px) 100vw, 50vw'"
            />
          @endforeach
        </div>
      </div>
    @endforeach

    <div class="mb-10 text-center">
      <a href="{{ route('noticias.index') }}"
        class="inline-flex items-center rounded-lg bg-[#ff661f] px-5 py-3 font-semibold text-white transition hover:bg-orange-700">
        Ver todas las noticias
      </a>
    </div>

    @php
      $secciones = [
        ['clave' => 'eventos', 'titulo' => 'Próximos eventos', 'ruta' => route('cartelera.index'), 'enlace' => 'Ver la cartelera completa'],
        ['clave' => 'interpretes', 'titulo' => 'Últimos intérpretes', 'ruta' => route('interpretes.index'), 'enlace' => 'Ver todos los intérpretes'],
        ['clave' => 'festivales', 'titulo' => 'Festivales más visitados', 'ruta' => route('festivales.index'), 'enlace' => 'Ver todos los festivales'],
        ['clave' => 'discos', 'titulo' => 'Últimos discos', 'ruta' => route('discografias.index'), 'enlace' => 'Ver todas las discografías'],
      ];
    @endphp

    @foreach ($secciones as $seccion)
      @continue($catalogo[$seccion['clave']]->isEmpty())
      <section class="mb-8" data-home-block="{{ $seccion['clave'] }}">
        <div class="mb-3 flex items-baseline justify-between gap-4 border-b-2 border-[#ff661f]">
          <h2 class="text-lg font-semibold text-gray-800">{{ $seccion['titulo'] }}</h2>
          <a href="{{ $seccion['ruta'] }}" class="text-sm text-[#ff661f] hover:underline">{{ $seccion['enlace'] }}</a>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
          @foreach ($catalogo[$seccion['clave']] as $item)
            @switch($seccion['clave'])
              @case('eventos')
                <x-show-card :show="$item" :event-title="true" />
                @break
              @case('interpretes')
                <x-biografia-card :interprete="$item" />
                @break
              @case('festivales')
                <x-festival-card :festival="$item" />
                @break
              @case('discos')
                <x-disco-card :disco="$item" />
                @break
            @endswitch
          @endforeach
        </div>
      </section>
    @endforeach

    <section class="mb-4 rounded bg-white p-2 shadow-sm cv-auto">
      <p class="text-lg text-gray-700">{{ $metaDescription }}</p>
    </section>
  </section>
@endsection

@push('json-ld')
  <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@graph": [
          {
            "@type": "WebSite",
            "name": "Mi Folklore Argentino",
            "url": "{{ url('/') }}",
            "potentialAction": {
              "@type": "SearchAction",
              "target": "{{ route('buscar') }}?q={search_term_string}",
              "query-input": "required name=search_term_string"
            }
          },
          {
            "@type": "Organization",
            "name": "Mi Folklore Argentino",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('img/logo.png') }}",
            "sameAs": [
              "https://www.facebook.com/MiFolkloreArgentino/",
              "https://www.instagram.com/mifolkloreargentino/",
              "https://x.com/MiFolkloreArg"
            ]
          }
        ]
      }
    </script>
@endpush

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
