@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Programas de radio de folklore argentino</h1>
    <p class="text-base text-gray-700">
      Programas y streams verificados con su señal, grilla semanal y próxima emisión. Ya reunimos <strong>{{ $programs->total() }}</strong> programas en el portal.
    </p>
  </section>
  <section class="bg-white p-4 rounded shadow-sm mb-6">
    <form class="grid gap-3 md:grid-cols-4" method="GET">
      <input class="rounded-lg border-gray-300 focus:border-[#ff661f] focus:ring-[#ff661f]" name="q" value="{{ request('q') }}" placeholder="Buscar programa o radio">
      <select class="rounded-lg border-gray-300 focus:border-[#ff661f] focus:ring-[#ff661f]" name="signal_id"><option value="">Todas las señales</option>@foreach($signals as $signal)<option value="{{ $signal->id }}" @selected((int) request('signal_id') === $signal->id)>{{ $signal->title }}</option>@endforeach</select>
      <select class="rounded-lg border-gray-300 focus:border-[#ff661f] focus:ring-[#ff661f]" name="platform"><option value="">Cualquier plataforma</option>@foreach(['youtube','facebook','twitch','spotify','stream_directo','otra_oficial'] as $platform)<option value="{{ $platform }}" @selected(request('platform') === $platform)>{{ ucfirst(str_replace('_', ' ', $platform)) }}</option>@endforeach</select>
      <button class="rounded-lg bg-[#ff661f] px-4 py-2 font-semibold text-white hover:bg-orange-600">Buscar</button>
    </form>
    <a class="mt-3 inline-block text-sm font-semibold text-[#ff661f] hover:underline" href="{{ route('radios.index') }}">← Volver a las radios</a>
  </section>
  <section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">@forelse($programs as $program)@php($next = $program->nextBroadcast())<a href="{{ $program->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><p class="text-sm font-semibold text-amber-800">{{ $program->signal?->title ?? ucfirst(str_replace('_', ' ', $program->platform ?? 'Stream independiente')) }}</p><h2 class="mt-1 text-xl font-bold">{{ $program->title }}</h2>@if($program->excerpt)<p class="mt-2 text-sm text-slate-600">{{ $program->excerpt }}</p>@endif @if($next)<p class="mt-4 text-sm font-semibold text-slate-700">Próxima: {{ $next['starts_at']->format('d/m H:i') }} h</p>@endif</a>@empty<p class="md:col-span-2 xl:col-span-3 rounded-xl bg-white p-6 text-slate-600">No encontramos programas verificados con esos filtros.</p>@endforelse</section>
  <x-public-pagination :paginator="$programs" />
@endsection
