@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Programas de radio de folklore argentino</h1>
    <p class="mt-3 text-slate-700">Programas y streams verificados con su señal, grilla semanal y próxima emisión.</p>
    <form class="mt-6 grid gap-3 md:grid-cols-4" method="GET"><input class="rounded border-slate-300" name="q" value="{{ request('q') }}" placeholder="Buscar programa o radio"><select class="rounded border-slate-300" name="signal_id"><option value="">Todas las señales</option>@foreach($signals as $signal)<option value="{{ $signal->id }}" @selected((int) request('signal_id') === $signal->id)>{{ $signal->title }}</option>@endforeach</select><select class="rounded border-slate-300" name="platform"><option value="">Cualquier plataforma</option>@foreach(['youtube','facebook','twitch','spotify','stream_directo','otra_oficial'] as $platform)<option value="{{ $platform }}" @selected(request('platform') === $platform)>{{ ucfirst(str_replace('_', ' ', $platform)) }}</option>@endforeach</select><button class="rounded bg-amber-700 px-4 py-2 font-semibold text-white">Buscar</button></form>
    <a class="mt-4 inline-block font-semibold text-amber-800" href="{{ route('radios.index') }}">← Volver a las radios</a>
  </section>
  <section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">@forelse($programs as $program)@php($next = $program->nextBroadcast())<a href="{{ $program->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><p class="text-sm font-semibold text-amber-800">{{ $program->signal?->title ?? ucfirst(str_replace('_', ' ', $program->platform ?? 'Stream independiente')) }}</p><h2 class="mt-1 text-xl font-bold">{{ $program->title }}</h2>@if($program->excerpt)<p class="mt-2 text-sm text-slate-600">{{ $program->excerpt }}</p>@endif @if($next)<p class="mt-4 text-sm font-semibold text-slate-700">Próxima: {{ $next['starts_at']->format('d/m H:i') }} h</p>@endif</a>@empty<p class="md:col-span-2 xl:col-span-3 rounded-xl bg-white p-6 text-slate-600">No encontramos programas verificados con esos filtros.</p>@endforelse</section>
  <div class="mt-8">{{ $programs->links() }}</div>
@endsection
