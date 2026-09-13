@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($mito);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
@endphp
@section('metaImage', $metaImage)
@section('ogType', 'article')

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": @json($mito->titulo),
  "description": @json($metaDescription),
  "image": [@json($metaImage)],
  "mainEntityOfPage": @json(\App\Support\CanonicalUrl::current())
}
</script>
@endpush

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold mb-4">{{ $h1 }}</h1>

    <div class="mb-6">
      <x-editorial-image
        :entity="$mito"
        variant="main"
        class="rounded-lg shadow-lg w-full"
        loading="eager"
        fetchpriority="high"
      />
    </div>

    <div class="text-lg text-gray-800 mb-6">
      {!! $mito->mito !!}
    </div>

    <p class="text-sm text-gray-600 mb-6">Visitas: {{ $mito->visitas }}</p>

    {{-- Muestro ls redes p compartir --}}
    <div class="redes mb-4">
      <x-compartir-redes :titulo="$mito->titulo" :url="Request::url()" />
    </div>

    @if ($relacionados && $relacionados->count() > 0)
      <section class="bg-white p-2 mb-4">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-2">
          Mitos relacionados
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          @foreach ($relacionados as $relacionado)
            <x-mito-card :mito="$relacionado" />
          @endforeach
        </div>
      </section>
    @endif

  </div>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
  <x-sidebar.advertisement />

@endsection
