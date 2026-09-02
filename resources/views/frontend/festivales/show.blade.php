@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($festival);
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
  "headline": @json($festival->title),
  "description": @json($metaDescription),
  "image": [@json($metaImage)],
  "url": @json(\App\Support\CanonicalUrl::normalize($canonical))
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.festivales._filters')

  <article class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <x-editorial-image
      :entity="$festival"
      variant="hero"
      class="rounded shadow-lg w-full object-cover max-h-[500px] mb-5"
      loading="eager"
      fetchpriority="high"
    />

    <h1 class="text-3xl font-bold mb-2">{{ $h1 }}</h1>

    @if ($festival->excerpt)
      <p class="text-lg text-slate-700 mb-5">{{ $festival->excerpt }}</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
      <div class="rounded-lg bg-slate-50 p-4">
        <span class="font-semibold text-slate-900">Provincia:</span> {{ $festival->provincia?->nombre ?? '-' }}
      </div>
      <div class="rounded-lg bg-slate-50 p-4">
        <span class="font-semibold text-slate-900">Localidad:</span> {{ $festival->locality?->name ?? '-' }}
      </div>
      <div class="rounded-lg bg-slate-50 p-4">
        <span class="font-semibold text-slate-900">Mes:</span> {{ $festival->mes?->nombre ?? '-' }}
      </div>
    </div>

    <div class="prose max-w-none mb-6">
      {!! $festival->body !!}
    </div>

    <div class="mb-4">
      <a href="{{ route('backend.contributions.create', ['type' => 'festival', 'id' => $festival->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
        Sugerir correccion o actualizacion
      </a>
    </div>

    <div class="flex flex-wrap gap-3">
      <a href="{{ route('festivales.index') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Ver todos los festivales</a>
      @if ($festival->provincia)
        <a href="{{ route('festivales.province', $festival->provincia->slug) }}" class="inline-flex items-center rounded-lg bg-orange-100 px-4 py-2 text-sm font-semibold text-orange-800 hover:bg-orange-200">Festivales de {{ $festival->provincia->nombre }}</a>
      @endif
      @if ($festival->mes)
        <a href="{{ route('festivales.month', str($festival->mes->nombre)->slug()) }}" class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Festivales de {{ $festival->mes->nombre }}</a>
      @endif
    </div>
  </article>

  @if ($journey->enabled)
    @if ($journey->upcomingEvents->isNotEmpty())
      <x-content-journey.section title="Proximas fechas">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          @foreach ($journey->upcomingEvents as $event)
            <x-show-card :show="$event" />
          @endforeach
        </div>
      </x-content-journey.section>
    @endif

    @if ($journey->artists->isNotEmpty())
      <x-content-journey.section title="Artistas vinculados al festival">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          @foreach ($journey->artists as $artist)
            <x-biografia-card :interprete="$artist" />
          @endforeach
        </div>
      </x-content-journey.section>
    @endif

    @if ($journey->knowledgeArticles->isNotEmpty() || $journey->news->isNotEmpty())
      <x-content-journey.section title="Historia y contexto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach ($journey->knowledgeArticles as $article)
            <a href="{{ $article->getUrl() }}" class="rounded-lg border border-slate-200 p-4 font-semibold hover:text-orange-700">{{ $article->title }}</a>
          @endforeach
          @foreach ($journey->news as $news)
            <a href="{{ route('noticias.show', $news->slug) }}" class="rounded-lg border border-slate-200 p-4 font-semibold hover:text-orange-700">{{ $news->title ?? $news->titulo }}</a>
          @endforeach
        </div>
      </x-content-journey.section>
    @endif
  @endif

  @if ($sameProvince->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-2xl font-semibold text-slate-900 mb-4">Festivales de la misma provincia</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($sameProvince as $item)
          <x-festival-card :festival="$item" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($sameMonth->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-2xl font-semibold text-slate-900 mb-4">Festivales del mismo mes</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($sameMonth as $item)
          <x-festival-card :festival="$item" />
        @endforeach
      </div>
    </section>
  @endif
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
