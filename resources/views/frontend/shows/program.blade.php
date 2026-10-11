@extends('layouts.app')
@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonicalUrl)
@section('metaRobots', $metaRobots)
@php
  $programImage = app(\App\Services\EditorialImageResolver::class)->resolve($program);
  $programMetaImage = $programImage->isMedia() ? $programImage->media->original_path : $programImage->url;
@endphp
@section('metaImage', $programMetaImage)
@push('json-ld')<script type="application/ld+json">@json($programSchema)</script>@endpush
@section('content')
<x-breadcrumbs :items="$breadcrumbs" />
<article class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <h1 class="text-3xl font-bold mb-3">{{ $program->title }}</h1>
  @if($program->excerpt)<p class="text-lg text-slate-700 mb-4">{{ $program->excerpt }}</p>@endif
  <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
    <div class="md:col-span-2"><x-editorial-image :entity="$program" variant="hero" class="rounded-lg w-full object-cover max-h-[420px]" loading="eager" fetchpriority="high" /></div>
    <div class="md:col-span-3">
      @if($program->venue)<p class="mb-3"><strong>Lugar común:</strong> {{ $program->venue->name }}<br>{{ $program->venue->address }} — {{ $program->venue->city }} / {{ $program->venue->provincia?->nombre }}</p>@endif
      <p class="text-slate-700">Consultá la fecha, los artistas y las entradas de cada jornada en la programación.</p>
    </div>
  </div>
  <div class="prose max-w-none">{!! $program->body !!}</div>
</article>
<section aria-labelledby="program-sessions" class="mb-6"><h2 id="program-sessions" class="text-2xl font-bold mb-4">Programación por jornada</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  @foreach($sessions as $event)
    <article class="bg-white border border-slate-200 rounded-xl p-5">
      <h3 class="text-xl font-semibold mb-2"><a href="{{ route('cartelera.show', $event->slug) }}" class="text-orange-700">{{ $event->title }}</a></h3>
      <p class="mb-2"><time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->translatedFormat('l j \d\e F \d\e Y') }}@if($event->start_at->format('H:i') !== '00:00') — {{ $event->start_at->format('H:i') }} h @endif</time></p>
      @if($event->venue)<p class="text-sm mb-2">{{ $event->venue->name }} — {{ $event->city }}</p>@endif
      @if($event->interpretes->isNotEmpty())<p class="text-sm mb-3"><strong>Artistas:</strong> @foreach($event->interpretes as $artist)<a href="{{ route('artista.show', $artist->slug) }}" class="text-orange-700">{{ $artist->interprete }}</a>@if(!$loop->last), @endif @endforeach</p>@endif
      <a href="{{ route('cartelera.show', $event->slug) }}" class="font-semibold text-orange-700">Ver jornada y entradas →</a>
    </article>
  @endforeach
  </div><div class="mt-6">{{ $sessions->links() }}</div>
</section>
<a href="{{ route('cartelera.index') }}" class="text-orange-700">Volver a la cartelera</a>
@endsection
