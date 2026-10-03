@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($show);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
  $eventSchema = \App\Support\EventSchema::forEvent($show, $canonicalUrl, $metaImage, $metaDescription);
  $journeyItem = fn (string $module, int $position) => ['sourceType' => 'event', 'sourceId' => $show->id, 'module' => $module, 'position' => $position];
  $continuityArtist = $artistContinuity['artist'];
@endphp
@section('metaImage', $metaImage)

@push('json-ld')
<script type="application/ld+json">
@json($eventSchema)
</script>
@endpush

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />

  @include('frontend.shows._filters', ['variant' => 'compact'])

  <article class="bg-white rounded-xl shadow-sm p-6 mb-6">
    @if ($eventStatus === 'past')
      <p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
        <strong>Este evento ya se realizó.</strong>
        @if ($continuityArtist)
          Más abajo encontrás otras fechas de {{ $continuityArtist->interprete }} y la agenda vigente.
        @else
          Mirá la agenda vigente en la <a href="{{ route('cartelera.index') }}" class="underline">cartelera</a>.
        @endif
      </p>
    @elseif ($eventStatus === 'ongoing')
      <p class="mb-4 inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-800" role="status">En curso</p>
    @endif

    <h1 class="text-3xl font-bold mb-2">{{ $h1 }}</h1>

    @if (filled($show->excerpt))
      <p class="text-lg text-slate-700 mb-4">{{ $show->excerpt }}</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-start mb-6">
      <div class="md:col-span-2">
        <x-editorial-image
          :entity="$show"
          variant="hero"
          class="rounded-lg shadow w-full object-cover max-h-[420px]"
          loading="eager"
          fetchpriority="high"
        />
      </div>

      <div class="md:col-span-3">
        @include('frontend.shows._facts')

        @if ($artists->isNotEmpty())
          <p class="mt-4 text-sm text-slate-700">
            <span class="font-semibold text-slate-900">{{ $artists->count() === 1 ? 'Artista:' : 'Artistas:' }}</span>
            @foreach ($artists as $artist)
              <a href="{{ route('artista.show', $artist->slug) }}" class="text-orange-700 hover:text-orange-800">{{ $artist->interprete }}</a>@if (! $loop->last), @endif
            @endforeach
          </p>
        @endif
      </div>
    </div>

    @if (filled(trim(strip_tags((string) $show->body))))
      <div class="prose max-w-none mb-6">
        {!! $show->body !!}
      </div>
    @endif

    <a href="{{ route('backend.contributions.create', ['type' => 'show', 'id' => $show->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium" data-nosnippet>
      Sugerir corrección de datos del evento
    </a>
  </article>

  @if ($continuityArtist && $artistContinuity['events']->isNotEmpty())
    @php $artistModule = 'event_related_artist_events'; @endphp
    <x-content-journey.section :title="'Más de '.$continuityArtist->interprete" :module="$artistModule" source-type="event" :source-id="$show->id" :items="$artistContinuity['events']">
      <p class="-mt-2 mb-4 text-sm text-slate-600">
        {{ $artistContinuity['mode'] === 'upcoming' ? 'Próximas fechas' : 'Presentaciones anteriores' }}
      </p>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($artistContinuity['events'] as $event)
          <x-show-card :show="$event" :event-title="true" :journey="$journeyItem($artistModule, $loop->iteration)" />
        @endforeach
      </div>
      <div class="mt-4 flex flex-wrap gap-3 text-sm">
        <a href="{{ route('artista.show', $continuityArtist->slug) }}" class="inline-flex items-center rounded-lg border border-orange-200 px-4 py-2 font-semibold text-orange-700 hover:border-orange-300 hover:text-orange-800">Biografía de {{ $continuityArtist->interprete }}</a>
        <a href="{{ route('artista.shows', $continuityArtist->slug) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">Todas sus fechas</a>
      </div>
    </x-content-journey.section>
  @endif

  @if ($show->provincia && $provinceUpcoming->isNotEmpty())
    @php $provinceModule = 'event_related_province_events'; @endphp
    <x-content-journey.section :title="'Próximos eventos en '.$show->provincia->nombre" :module="$provinceModule" source-type="event" :source-id="$show->id" :items="$provinceUpcoming">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($provinceUpcoming as $event)
          <x-show-card :show="$event" :event-title="true" :journey="$journeyItem($provinceModule, $loop->iteration)" />
        @endforeach
      </div>
    </x-content-journey.section>
  @endif

  @if ($artists->isNotEmpty())
    @php $artistsModule = 'event_related_artists'; @endphp
    <x-content-journey.section title="Artistas en escena" :module="$artistsModule" source-type="event" :source-id="$show->id" :items="$artists">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($artists as $artist)
          <x-biografia-card :interprete="$artist" :journey="$journeyItem($artistsModule, $loop->iteration)" />
        @endforeach
      </div>
    </x-content-journey.section>
  @endif

  @include('frontend.shows._province_links')
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.invite-to-publish />
@endsection
