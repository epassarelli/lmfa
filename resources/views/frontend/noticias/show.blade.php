@extends('layouts.app')

@php
  $publishedIso = optional($noticia->published_at ?? $noticia->created_at ?? $noticia->updated_at)->toIso8601String();
  $modifiedIso = optional($noticia->updated_at ?? $noticia->published_at ?? $noticia->created_at)->toIso8601String();
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($noticia);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
@endphp

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $metaImage)
@section('ogType', 'article')

@section('ogArticleTags')
  @if ($publishedIso)
    <meta property="article:published_time" content="{{ $publishedIso }}">
  @endif
  @if ($modifiedIso)
    <meta property="article:modified_time" content="{{ $modifiedIso }}">
  @endif
  <meta property="article:author" content="{{ $noticia->interprete ? route('artista.show', $noticia->interprete->slug) : url('/') }}">
  <meta property="article:section" content="{{ $noticia->categoria->nombre ?? 'Folklore' }}">
@endsection

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "{{ $noticia->titulo }}",
  "image": [
    "{{ $metaImage }}"
  ],
  "datePublished": @json($publishedIso),
  "dateModified": @json($modifiedIso),
  "author": [{
      "@type": "Person",
      "name": "{{ $noticia->interprete->interprete ?? 'Redaccion' }}",
      "url": "{{ $noticia->interprete ? route('artista.show', $noticia->interprete->slug) : url('/') }}"
    }]
}
</script>
@endpush

@section('styles')
@endsection

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif
  <section class="bg-white p-2 mb-4">
    <div class="mb-4">
      <x-editorial-image
        :entity="$noticia"
        variant="detail"
        class="rounded shadow-lg w-full"
        loading="eager"
        fetchpriority="high"
      />
    </div>

    <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ $h1 }}</h1>

    <div class="prose prose-lg max-w-none mb-6 text-gray-800">
      {!! $noticia->noticia !!}
    </div>

    <p class="text-sm text-gray-500">Visitas: {{ $noticia->visitas }}</p>

    <div class="related">
      @if ($noticia->interpretes->count() > 1)
        <div class="mt-6 border-t pt-4 text-sm text-gray-700">
          <p class="font-semibold text-gray-800 mb-2">Tambien participan:</p>
          <ul class="flex flex-wrap gap-2">
            @foreach ($noticia->interpretes as $interprete)
              @if ($interprete->id !== $noticia->interprete_id)
                <li>
                  <a href="{{ route('artista.noticias', $interprete->slug) }}"
                    class="inline-block bg-orange-100 text-orange-700 px-3 py-1 rounded-full hover:bg-orange-200 transition">
                    {{ $interprete->interprete }}
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </section>

  <div class="redes">
    <x-compartir-redes :titulo="$noticia->titulo" :url="Request::url()" />
  </div>

  @if ($relacionadas && $relacionadas->count() > 0)
    <section class="bg-white p-2 mb-4">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-2">
        Noticias relacionadas
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($relacionadas as $noticiaRelacionada)
          <x-noticia-card :noticia="$noticiaRelacionada" />
        @endforeach
      </div>
    </section>
  @endif
@endsection

@section('sidebar')
@if($interprete)
    @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
@endif

  <x-sidebar.newsletter-form />

  @if (isset($ultimasSidebar) && $ultimasSidebar->count() > 0)
    <x-sidebar.card-noticias :noticias="$ultimasSidebar" />
  @endif

  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
