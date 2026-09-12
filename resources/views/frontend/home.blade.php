@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section>
    <section class="mb-6 rounded bg-white p-2 shadow-sm">
      <h1 class="mb-4 border-b-2 border-[#ff661f] text-xl font-semibold text-gray-900">{{ $h1 }}</h1>
      <p class="text-base text-gray-700">
        Ya reunimos <strong>{{ $totales['noticias'] }}</strong> noticias, <strong>{{ $totales['artistas'] }}</strong> artistas,
        <strong>{{ $totales['discos'] }}</strong> discos, <strong>{{ $totales['festivales'] }}</strong> festivales y
        <strong>{{ $totales['recetas'] }}</strong> recetas del folklore argentino.
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

    @if (array_filter($destacados))
      <section class="mb-8">
        <h2 class="mb-3 text-lg font-semibold text-gray-800">Destacados del catálogo</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
          @if ($destacados['artista'])
            <x-biografia-card :interprete="$destacados['artista']" />
          @endif
          @if ($destacados['disco'])
            <x-disco-card :disco="$destacados['disco']" />
          @endif
          @if ($destacados['festival'])
            <x-festival-card :festival="$destacados['festival']" />
          @endif
          @if ($destacados['receta'])
            <x-receta-card :receta="$destacados['receta']" />
          @endif
        </div>
      </section>
    @endif

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
