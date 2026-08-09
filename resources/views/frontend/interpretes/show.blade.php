@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto))

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@id": "{{ \App\Support\CanonicalUrl::current() }}",
  "@type": "MusicGroup",
  "name": "{{ $interprete->interprete }}",
  "description": "{{ $metaDescription }}",
  "url": "{{ \App\Support\CanonicalUrl::current() }}",
  "image": "{{ $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto) }}",
  "genre": "Folklore Argentino"
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Quien es {{ $interprete->interprete }}?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "{{ \Illuminate\Support\Str::limit(\App\Support\SeoMetadata::clean($interprete->biografia), 200) }}"
      }
    },
    {
      "@type": "Question",
      "name": "Cual es la trayectoria de {{ $interprete->interprete }} en el folklore?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Conoce la biografia completa, discos y canciones de {{ $interprete->interprete }} en Mi Folklore Argentino."
      }
    }
  ]
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold mb-2">{{ $h1 }}</h1>
    <div class="mb-6">
      <a href="{{ route('backend.contributions.create', ['type' => 'interprete', 'id' => $interprete->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
        Sugerir edicion de biografia
      </a>
    </div>

    <div class="prose max-w-none prose-lg prose-slate">
      {!! $interprete->biografia !!}
    </div>
  </section>

  <div class="redes">
    <x-compartir-redes :titulo="$interprete->interprete" :url="Request::url()" />
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  @if ($interprete->images->isNotEmpty())
    <div class="mb-4">
      <x-optimized-image :image="$interprete->images->first()" variant="card" width="400" height="400"
        class="rounded shadow-md object-cover w-full" :alt="$interprete->interprete" fetchpriority="high" />
    </div>
  @elseif ($interprete->foto)
    <div class="mb-4">
      <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
          class="rounded shadow-md object-cover w-full" loading="lazy">
    </div>
  @endif
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  <br>
  <x-sidebar.social-links />
@endsection
