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

    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Contenido principal --}}
      <div class="w-full lg:w-2/3">
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

        <p class="text-sm text-gray-600">Visitas: {{ $mito->visitas }}</p>

        {{-- Muestro ls redes p compartir --}}
        <div class="redes">
          <x-compartir-redes :titulo="$mito->titulo" :url="Request::url()" />
        </div>

      </div>

      {{-- Barra lateral --}}
      <div class="w-full lg:w-1/3">
        <h3 class="text-xl font-semibold mb-4">Mitos relacionados</h3>

        @foreach ($relacionados as $relacionado)
          <x-mito-card :mito="$relacionado" />
        @endforeach

      </div>

    </div>

  </div>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
