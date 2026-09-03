@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)
@push('json-ld')
@php
  $venueSchema = ['@context' => 'https://schema.org', '@type' => 'MusicVenue', 'name' => $penia->title, 'url' => $canonical, 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $penia->city, 'addressRegion' => $penia->provincia?->nombre, 'streetAddress' => $penia->address], 'telephone' => $penia->phone, 'sameAs' => array_values(array_filter([$penia->website, $penia->reservation_url]))];
@endphp
<script type="application/ld+json">@json($venueSchema)</script>
@endpush
@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  @if(!empty($isPreview))<div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Vista previa editorial: esta ficha no está indexada ni registra visitas.</div>@endif
  <div class="mb-6"><a class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-orange-400 hover:text-orange-700" href="{{ route('penia-profiles.index') }}">Explorar y buscar Peñas</a></div>
  <article class="rounded-2xl bg-white p-6 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">{{ $penia->title }}</h1>
    <p class="mt-2 text-slate-600">{{ $penia->venue_type }} · {{ $penia->city }}, {{ $penia->provincia?->nombre }}</p>
    @if($penia->excerpt)<p class="mt-5 text-lg text-slate-700">{{ $penia->excerpt }}</p>@endif
    <div class="prose mt-6 max-w-none">{!! $penia->body !!}</div>
    <section class="mt-8 rounded-xl bg-slate-50 p-5"><h2 class="text-xl font-semibold">Datos verificados</h2><p class="mt-2">Última validación: {{ $penia->last_verified_at?->format('d/m/Y') }}</p>@if($penia->phone)<p>Contacto: {{ $penia->phone }}</p>@endif @if($penia->website)<a class="text-orange-700" href="{{ $penia->website }}" rel="nofollow noopener" target="_blank">Sitio oficial</a>@endif</section>
  </article>
  @if($penia->events->isNotEmpty())<section class="mt-8"><h2 class="mb-4 text-2xl font-semibold">Próximos eventos</h2><div class="grid gap-5 md:grid-cols-3">@foreach($penia->events as $event)<x-show-card :show="$event" />@endforeach</div></section>@endif
  @if($sameProvince->isNotEmpty())
    <section class="mt-8">
      <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-semibold text-slate-900">Más Peñas en {{ $penia->provincia?->nombre }}</h2>
        <a class="text-sm font-semibold text-orange-700 hover:text-orange-900" href="{{ route('penia-profiles.index', ['province_id' => $penia->province_id]) }}">Ver todas</a>
      </div>
      <div class="grid gap-5 md:grid-cols-3">
        @foreach($sameProvince as $item)
          <a href="{{ $item->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
            <h3 class="text-lg font-semibold text-slate-900">{{ $item->title }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ $item->city ?: $item->provincia?->nombre }}</p>
            <span class="mt-3 inline-block text-sm font-semibold text-orange-700">Ver ficha</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif
@endsection
