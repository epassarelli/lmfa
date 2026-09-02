@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Peñas folklóricas de Argentina</h1>
    <p class="mt-3 text-slate-700">Espacios culturales con información editorial verificada para planificar una salida y descubrir agenda folklórica.</p>
    <form method="GET" class="mt-6 grid gap-3 md:grid-cols-4">
      <input name="q" value="{{ request('q') }}" class="rounded-lg border-slate-300" placeholder="Nombre o ciudad">
      <select name="province_id" class="rounded-lg border-slate-300"><option value="">Todas las provincias</option>@foreach($provincias as $provincia)<option value="{{ $provincia->id }}" @selected((int) request('province_id') === $provincia->id)>{{ $provincia->nombre }}</option>@endforeach</select>
      <select name="venue_type" class="rounded-lg border-slate-300"><option value="">Todos los espacios</option>@foreach($venueTypes as $value => $label)<option value="{{ $value }}" @selected(request('venue_type') === $value)>{{ $label }}</option>@endforeach</select>
      <button class="rounded-lg bg-orange-600 px-4 py-2 font-semibold text-white hover:bg-orange-700">Buscar peñas</button>
    </form>
  </section>
  <section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($penias as $penia)
      <a href="{{ $penia->getUrl() }}" class="rounded-xl bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"><h2 class="text-xl font-semibold text-slate-900">{{ $penia->title }}</h2><p class="mt-2 text-sm text-slate-600">{{ $penia->provincia?->nombre }}@if($penia->city) · {{ $penia->city }}@endif</p><p class="mt-3 text-sm text-orange-700">Verificada {{ $penia->last_verified_at?->format('d/m/Y') }}</p></a>
    @empty
      <p class="md:col-span-3 rounded-xl bg-white p-6 text-slate-600">No hay Peñas verificadas para estos filtros.</p>
    @endforelse
  </section>
  <div class="mt-6">{{ $penias->links() }}</div>
@endsection
