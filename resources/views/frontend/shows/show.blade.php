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
@php
  $eventSchema = [
    '@context' => 'https://schema.org', '@type' => 'Event', 'name' => $show->titulo,
    'startDate' => optional($show->fecha)->toIso8601String(), 'url' => \App\Support\CanonicalUrl::current(),
    'location' => ['@type' => 'Place', 'name' => $show->lugar, 'address' => $show->direccion ?? $show->lugar],
    'image' => $metaImage, 'description' => $metaDescription,
    'performer' => $show->interpretes->where('estado', 1)->map(fn ($artist) => ['@type' => $artist->artist_type === 'soloist' ? 'Person' : 'MusicGroup', 'name' => $artist->interprete, 'url' => route('artista.show', $artist->slug)])->values()->all(),
  ];
  if ($journey['enabled'] && $journey['festivals']->isNotEmpty()) $eventSchema['superEvent'] = ['@type' => 'Festival', 'name' => $journey['festivals']->first()->title, 'url' => $journey['festivals']->first()->getUrl()];
@endphp
<script type="application/ld+json">
@json($eventSchema)
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
          @if ($journey['enabled'] && $journey['festivals']->isNotEmpty())
            <x-content-journey.section title="Este evento forma parte de">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">@foreach ($journey['festivals'] as $festival)<x-festival-card :festival="$festival" />@endforeach</div>
            </x-content-journey.section>
          @endif
          @if ($journey['enabled'] && $journey['artists']->isNotEmpty())
            <x-content-journey.section title="Artistas en escena">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">@foreach ($journey['artists'] as $artist)<x-biografia-card :interprete="$artist" />@endforeach</div>
            </x-content-journey.section>
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
