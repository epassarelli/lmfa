@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)
@push('json-ld')
@php
  $venueSchema = ['@context' => 'https://schema.org', '@type' => 'MusicVenue', 'name' => $penia->title, 'url' => $canonical, 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $penia->city, 'addressRegion' => $penia->provincia?->nombre, 'streetAddress' => $penia->address], 'geo' => $penia->latitude && $penia->longitude ? ['@type' => 'GeoCoordinates', 'latitude' => $penia->latitude, 'longitude' => $penia->longitude] : null, 'telephone' => $penia->phone, 'sameAs' => array_values(array_filter([$penia->website, $penia->reservation_url]))];
  $mapUrl = $penia->latitude && $penia->longitude ? 'https://www.openstreetmap.org/export/embed.html?bbox='.($penia->longitude - 0.012).'%2C'.($penia->latitude - 0.008).'%2C'.($penia->longitude + 0.012).'%2C'.($penia->latitude + 0.008).'&layer=mapnik&marker='.$penia->latitude.'%2C'.$penia->longitude : null;
  $mapLink = $penia->latitude && $penia->longitude ? 'https://www.openstreetmap.org/?mlat='.$penia->latitude.'&mlon='.$penia->longitude.'#map=16/'.$penia->latitude.'/'.$penia->longitude : 'https://www.google.com/maps/search/?api=1&query='.urlencode(implode(', ', array_filter([$penia->address, $penia->city, $penia->provincia?->nombre])));
@endphp
<script type="application/ld+json">@json(array_filter($venueSchema))</script>
@endpush

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  @if(!empty($isPreview))<div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Vista previa editorial: esta ficha no está indexada ni registra visitas.</div>@endif

  <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Encontrá otra Peña</h2>
    @include('frontend.penia-profiles._filters', ['filterFormClass' => 'mt-4 grid gap-3 lg:grid-cols-4'])
  </section>

  <article class="rounded-2xl bg-white p-6 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-wide text-orange-700">{{ $venueTypes[$penia->venue_type] ?? $penia->venue_type }}</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $penia->title }}</h1>
    @if($penia->excerpt)<p class="mt-5 text-lg text-slate-700">{{ $penia->excerpt }}</p>@endif
    <div class="prose mt-6 max-w-none">{!! $penia->body !!}</div>
  </article>

  <section class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-slate-50 p-6">
      <h2 class="text-xl font-semibold text-slate-900">Ubicación y contacto</h2>
      <dl class="mt-4 space-y-3 text-slate-700">
        <div><dt class="text-sm font-semibold text-slate-500">Provincia</dt><dd>{{ $penia->provincia?->nombre ?? '-' }}</dd></div>
        @if($penia->locality)<div><dt class="text-sm font-semibold text-slate-500">Localidad</dt><dd>{{ $penia->locality->name }}</dd></div>@endif
        @if($penia->city)<div><dt class="text-sm font-semibold text-slate-500">Ciudad</dt><dd>{{ $penia->city }}</dd></div>@endif
        @if($penia->address)<div><dt class="text-sm font-semibold text-slate-500">Dirección</dt><dd>{{ $penia->address }}</dd></div>@endif
        @if($penia->phone)<div><dt class="text-sm font-semibold text-slate-500">Teléfono</dt><dd><a class="text-orange-700 hover:text-orange-900" href="tel:{{ $penia->phone }}">{{ $penia->phone }}</a></dd></div>@endif
        @if($penia->email)<div><dt class="text-sm font-semibold text-slate-500">Email</dt><dd><a class="text-orange-700 hover:text-orange-900" href="mailto:{{ $penia->email }}">{{ $penia->email }}</a></dd></div>@endif
        @if($penia->capacity)<div><dt class="text-sm font-semibold text-slate-500">Capacidad</dt><dd>Hasta {{ $penia->capacity }} personas</dd></div>@endif
      </dl>
      @if($penia->website || $penia->reservation_url)<div class="mt-5 flex flex-wrap gap-3">@if($penia->website)<a class="rounded-lg border border-orange-300 px-4 py-2 text-sm font-semibold text-orange-800" href="{{ $penia->website }}" rel="nofollow noopener" target="_blank">Sitio oficial</a>@endif @if($penia->reservation_url)<a class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white" href="{{ $penia->reservation_url }}" rel="nofollow noopener" target="_blank">Reservar</a>@endif</div>@endif
    </div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      @if($mapUrl)<iframe class="h-80 w-full border-0" title="Mapa de {{ $penia->title }}" src="{{ $mapUrl }}" loading="lazy"></iframe>@else<div class="flex h-80 items-center justify-center bg-slate-100 p-6 text-center text-slate-600">La ubicación geográfica de esta Peña aún no fue cargada.</div>@endif
      <div class="p-4"><a class="text-sm font-semibold text-orange-700 hover:text-orange-900" href="{{ $mapLink }}" target="_blank" rel="noopener">Abrir ubicación en el mapa</a></div>
    </div>
  </section>

  @if($penia->regular_events_summary || $penia->admission_notes || $penia->accessibility_notes)
    <section class="mt-8 rounded-2xl bg-white p-6 shadow-sm"><h2 class="text-xl font-semibold text-slate-900">Información para tu visita</h2><div class="mt-4 grid gap-5 md:grid-cols-3">@if($penia->regular_events_summary)<div><h3 class="font-semibold">Programación habitual</h3><p class="mt-2 text-sm text-slate-700">{{ $penia->regular_events_summary }}</p></div>@endif @if($penia->admission_notes)<div><h3 class="font-semibold">Ingreso y reservas</h3><p class="mt-2 text-sm text-slate-700">{{ $penia->admission_notes }}</p></div>@endif @if($penia->accessibility_notes)<div><h3 class="font-semibold">Accesibilidad</h3><p class="mt-2 text-sm text-slate-700">{{ $penia->accessibility_notes }}</p></div>@endif</div><p class="mt-5 text-sm text-slate-500">Datos verificados el {{ $penia->last_verified_at?->format('d/m/Y') }}.</p></section>
  @endif
  @if($penia->events->isNotEmpty())<section class="mt-8"><h2 class="mb-4 text-2xl font-semibold">Próximos eventos</h2><div class="grid gap-5 md:grid-cols-3">@foreach($penia->events as $event)<x-show-card :show="$event" />@endforeach</div></section>@endif
  @if($sameProvince->isNotEmpty())<section class="mt-8"><div class="mb-4 flex flex-wrap items-center justify-between gap-3"><h2 class="text-2xl font-semibold text-slate-900">Más Peñas en {{ $penia->provincia?->nombre }}</h2><a class="text-sm font-semibold text-orange-700 hover:text-orange-900" href="{{ route('penia-profiles.index', ['province_id' => $penia->province_id]) }}">Ver todas</a></div><div class="grid gap-5 md:grid-cols-3">@foreach($sameProvince as $item)<a href="{{ $item->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><h3 class="text-lg font-semibold text-slate-900">{{ $item->title }}</h3><p class="mt-2 text-sm text-slate-600">{{ $item->city ?: $item->provincia?->nombre }}</p><span class="mt-3 inline-block text-sm font-semibold text-orange-700">Ver ficha</span></a>@endforeach</div></section>@endif
@endsection
