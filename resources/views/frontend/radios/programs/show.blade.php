@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)
@push('json-ld')
@php
  $programSchema = array_filter([
    '@context' => 'https://schema.org', '@type' => 'RadioSeries', 'name' => $program->title,
    'url' => $canonical, 'description' => $metaDescription, 'inLanguage' => 'es-AR',
    'broadcaster' => $program->signal ? ['@type' => 'RadioStation', 'name' => $program->signal->title, 'url' => $program->signal->getUrl()] : null,
    'sameAs' => array_values(array_filter([$program->listening_url])),
  ]);
@endphp
<script type="application/ld+json">@json($programSchema)</script>
@endpush

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  @if(!empty($isPreview))<div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">Vista previa editorial: este programa no está indexado ni registra visitas.</div>@endif
  @php($next = $program->nextBroadcast())
  <article class="rounded-2xl bg-white p-6 shadow-sm"><p class="text-sm font-semibold uppercase tracking-wide text-amber-800">Programa de folklore</p><h1 class="mt-2 text-3xl font-bold">{{ $program->title }}</h1>@if($program->signal)<p class="mt-2 text-slate-600">Se emite por <a class="font-semibold text-amber-800" href="{{ $program->signal->getUrl() }}">{{ $program->signal->title }}</a></p>@endif @if($program->excerpt)<p class="mt-5 text-lg text-slate-700">{{ $program->excerpt }}</p>@endif @if($next)<div class="mt-5 rounded-lg bg-amber-50 p-4"><strong>Próxima emisión:</strong> {{ $next['starts_at']->format('d/m/Y H:i') }} h <span class="text-sm text-slate-600">({{ $next['slot']->timezone }})</span></div>@endif<div class="prose mt-6 max-w-none">{!! $program->body !!}</div></article>
  <section class="mt-8 grid gap-6 lg:grid-cols-2"><div class="rounded-2xl bg-stone-100 p-6"><h2 class="text-xl font-bold">Grilla semanal</h2>@forelse($program->slots as $slot)<p class="mt-3"><strong>{{ ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][$slot->weekday] }}</strong> · {{ substr($slot->starts_at, 0, 5) }}@if($slot->ends_at)–{{ substr($slot->ends_at, 0, 5) }}@endif h</p>@empty<p class="mt-3 text-slate-600">La grilla todavía no fue informada.</p>@endforelse</div><div class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="text-xl font-bold">Dónde escuchar</h2>@if($program->listening_url)<a class="mt-4 inline-block rounded-lg bg-amber-700 px-4 py-2 font-semibold text-white" href="{{ $program->listening_url }}" target="_blank" rel="nofollow noopener noreferrer">Escuchar en {{ ucfirst(str_replace('_', ' ', $program->platform ?? 'línea')) }}</a>@endif @if($program->signal)@foreach($program->signal->listeningChannels as $channel)<div class="mt-4"><strong>{{ $channel->label }}</strong>@if($channel->frequency)<p>{{ $channel->frequency_band }} {{ $channel->frequency }}</p>@endif @if($channel->url)<a class="text-amber-800 underline" href="{{ $channel->url }}" target="_blank" rel="nofollow noopener noreferrer">Abrir canal oficial</a>@endif</div>@endforeach @endif @if($program->last_verified_at)<p class="mt-5 text-sm text-slate-500">Datos verificados el {{ $program->last_verified_at->format('d/m/Y') }}.</p>@endif</div></section>
  @if($relatedPrograms->isNotEmpty())<section class="mt-8"><h2 class="text-2xl font-bold">Otros programas relacionados</h2><div class="mt-4 grid gap-4 md:grid-cols-3">@foreach($relatedPrograms as $related)<a class="rounded-lg bg-white p-4 shadow-sm" href="{{ $related->getUrl() }}"><h3 class="font-bold">{{ $related->title }}</h3></a>@endforeach</div></section>@endif
@endsection
