@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)
@push('json-ld')
@php
  $stationSchema = array_filter([
    '@context' => 'https://schema.org', '@type' => 'RadioStation', 'name' => $signal->title,
    'url' => $canonical, 'description' => $metaDescription,
    'areaServed' => $signal->coverage_notes ?: $signal->provincia?->nombre,
    'address' => array_filter(['@type' => 'PostalAddress', 'streetAddress' => $signal->address, 'addressLocality' => $signal->city, 'addressRegion' => $signal->provincia?->nombre]),
    'geo' => $signal->latitude && $signal->longitude ? ['@type' => 'GeoCoordinates', 'latitude' => $signal->latitude, 'longitude' => $signal->longitude] : null,
    'telephone' => $signal->phone, 'email' => $signal->email,
    'sameAs' => array_values(array_filter(array_merge([$signal->website], $signal->listeningChannels->pluck('url')->all()))),
  ]);
@endphp
<script type="application/ld+json">@json($stationSchema)</script>
@endpush

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  @if(!empty($isPreview))<div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Vista previa editorial: esta señal no está indexada ni registra visitas.</div>@endif
  <article class="rounded-2xl bg-white p-6 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-wide text-amber-800">{{ $signal->editorial_focus === 'folklore' ? 'Radio de folklore' : 'Radio con programación folklórica' }}</p>
    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $signal->title }}</h1>
    <p class="mt-2 text-slate-600">{{ $signal->provincia?->nombre }}{{ $signal->city ? ' · '.$signal->city : '' }}</p>
    @if($signal->excerpt)<p class="mt-5 text-lg text-slate-700">{{ $signal->excerpt }}</p>@endif
    <div class="prose mt-6 max-w-none">{!! $signal->body !!}</div>
  </article>

  <section class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-stone-100 p-6"><h2 class="text-xl font-bold">Cómo escuchar</h2>@forelse($signal->listeningChannels as $channel)<div class="mt-4 rounded-lg bg-white p-4"><strong>{{ $channel->label }}</strong>@if($channel->frequency)<p class="mt-1">{{ $channel->frequency_band }} {{ $channel->frequency }}</p>@endif @if($channel->url)<a class="mt-2 inline-block font-semibold text-amber-800 underline" href="{{ $channel->url }}" target="_blank" rel="nofollow noopener noreferrer">Abrir canal oficial</a>@endif</div>@empty<p class="mt-3 text-slate-600">No hay canales activos disponibles.</p>@endforelse</div>
    <div class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="text-xl font-bold">Cobertura y contacto</h2><dl class="mt-4 space-y-3 text-slate-700"><div><dt class="text-sm font-semibold text-slate-500">Alcance</dt><dd>{{ ucfirst($signal->coverage_scope) }}</dd></div>@if($signal->coverage_notes)<div><dt class="text-sm font-semibold text-slate-500">Cobertura</dt><dd>{{ $signal->coverage_notes }}</dd></div>@endif @if($signal->address)<div><dt class="text-sm font-semibold text-slate-500">Dirección</dt><dd>{{ $signal->address }}</dd></div>@endif @if($signal->phone)<div><dt class="text-sm font-semibold text-slate-500">Teléfono</dt><dd><a class="text-amber-800" href="tel:{{ $signal->phone }}">{{ $signal->phone }}</a></dd></div>@endif @if($signal->email)<div><dt class="text-sm font-semibold text-slate-500">Email</dt><dd><a class="text-amber-800" href="mailto:{{ $signal->email }}">{{ $signal->email }}</a></dd></div>@endif</dl>@if($signal->website)<a class="mt-5 inline-block rounded-lg border border-amber-700 px-4 py-2 font-semibold text-amber-800" href="{{ $signal->website }}" target="_blank" rel="nofollow noopener">Sitio oficial</a>@endif @if($signal->last_verified_at)<p class="mt-5 text-sm text-slate-500">Datos verificados el {{ $signal->last_verified_at->format('d/m/Y') }}.</p>@endif</div>
  </section>

  @if($signal->programs->isNotEmpty())<section class="mt-8"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-2xl font-bold">Programación de folklore</h2><a class="font-semibold text-amber-800" href="{{ route('radios.programs.index', ['signal_id' => $signal->id]) }}">Ver grilla</a></div><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($signal->programs as $program)@php($next = $program->nextBroadcast())<a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm" href="{{ $program->getUrl() }}"><h3 class="font-bold">{{ $program->title }}</h3>@if($next)<p class="mt-2 text-sm text-slate-600">Próxima emisión: {{ $next['starts_at']->format('d/m H:i') }} h</p>@endif</a>@endforeach</div></section>@endif
@endsection
