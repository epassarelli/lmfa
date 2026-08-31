@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($show);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
@endphp
@section('metaImage', $metaImage)

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $show->titulo }}",
  "startDate": "{{ $show->fecha ? $show->fecha->toIso8601String() : '' }}",
  "location": {
    "@type": "Place",
    "name": "{{ $show->lugar }}",
    "address": "{{ $show->direccion ?? $show->lugar }}"
  },
  "image": "{{ $metaImage }}",
  "description": "{{ $metaDescription }}",
  "performer": {
    "@type": "MusicGroup",
    "name": "{{ $show->interprete->interprete ?? 'Artistas Varios' }}"
  }
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <div class="container mx-auto px-4 mt-4">
      <x-breadcrumbs :items="$breadcrumbs" />
    </div>
  @endif
  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col lg:flex-row gap-8">
        <div class="w-full lg:w-2/3">
          <div class="mb-6">
            <x-editorial-image
              :entity="$show"
              variant="hero"
              class="rounded shadow-lg w-full object-cover max-h-[500px]"
              loading="eager"
              fetchpriority="high"
            />
          </div>

          <h1 class="text-3xl font-bold mb-2">{{ $h1 }}</h1>
          <div class="mb-4">
            <a href="{{ route('backend.contributions.create', ['type' => 'show', 'id' => $show->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
              Sugerir correccion de datos del evento
            </a>
          </div>
          <div class="prose max-w-none mb-6">
            {!! $show->detalle !!}
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
