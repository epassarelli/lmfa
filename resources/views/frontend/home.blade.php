@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section>
    <section class="relative mb-8 overflow-hidden rounded-lg bg-stone-900 shadow-sm">
      <img src="{{ asset('images/copa-folklore-2026/hero-banner.png') }}" alt="Copa del Folklore Argentino 2026" class="absolute inset-0 h-full w-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-r from-stone-950/90 via-stone-950/70 to-stone-900/20"></div>
      <div class="relative px-6 py-10 text-white md:px-10 md:py-14">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-300">Mi Folklore Argentino</p>
        <h2 class="max-w-3xl text-3xl font-bold leading-tight md:text-5xl">Copa del Folklore Argentino 2026</h2>
        <p class="mt-4 max-w-3xl text-base text-stone-200 md:text-lg">
          Un torneo editorial y participativo entre 32 interpretes del folklore argentino, con votacion abierta en Instagram y seguimiento completo en el portal.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
          <a href="{{ route('folklore.cup.index') }}"
            class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">
            Ir a la copa
          </a>
          <a href="{{ route('folklore.cup.groups') }}"
            class="inline-flex items-center rounded-md border border-stone-300 px-4 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
            Ver zonas
          </a>
        </div>
      </div>
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
            <x-noticia-card :noticia="$noticia" />
          @endforeach
        </div>
      </div>
    @endforeach

    <section class="mb-4 rounded bg-white p-2 shadow-sm">
      <h1 class="mb-4 border-b-2 border-[#ff661f] text-xl font-semibold text-gray-900">Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y Costumbres</h1>
      <p class="text-lg text-gray-700">Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre musica, danzas y mas. Visitanos hoy.</p>
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
