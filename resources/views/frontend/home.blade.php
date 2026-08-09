@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section>
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

    <section class="mb-4 rounded bg-white p-2 shadow-sm cv-auto">
      <h1 class="mb-4 border-b-2 border-[#ff661f] text-xl font-semibold text-gray-900">{{ $h1 }}</h1>
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
