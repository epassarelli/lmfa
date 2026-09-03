@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  <x-breadcrumbs :items="$breadcrumbs" />
  <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Radios de folklore argentino</h1>
    <p class="mt-3 text-slate-700">Señales verificadas con frecuencias y enlaces oficiales para escuchar folklore por aire o en línea.</p>
    <form class="mt-6 grid gap-3 md:grid-cols-4" method="GET">
      <input class="rounded border-slate-300" name="q" value="{{ request('q') }}" placeholder="Buscar radio o ciudad">
      <select class="rounded border-slate-300" name="province_id"><option value="">Todas las provincias</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) request('province_id') === $province->id)>{{ $province->nombre }}</option>@endforeach</select>
      <select class="rounded border-slate-300" name="mode"><option value="">Cualquier emisión</option><option value="air" @selected(request('mode') === 'air')>Por aire</option><option value="streaming" @selected(request('mode') === 'streaming')>Streaming</option><option value="web" @selected(request('mode') === 'web')>Web</option></select>
      <button class="rounded bg-amber-700 px-4 py-2 font-semibold text-white">Buscar</button>
    </form>
    <a class="mt-4 inline-block font-semibold text-amber-800 hover:text-amber-950" href="{{ route('radios.programs.index') }}">Explorar programas de folklore →</a>
  </section>

  <section class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($signals as $signal)
      <a href="{{ $signal->getUrl() }}" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
        <p class="text-sm font-semibold text-amber-800">{{ $signal->provincia?->nombre ?? 'Cobertura digital' }}{{ $signal->city ? ' · '.$signal->city : '' }}</p>
        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $signal->title }}</h2>
        @if($signal->excerpt)<p class="mt-2 text-sm text-slate-600">{{ $signal->excerpt }}</p>@endif
        <p class="mt-4 text-sm text-slate-600">{{ implode(' · ', $signal->transmission_modes ?? []) }}</p>
      </a>
    @empty
      <p class="md:col-span-2 xl:col-span-3 rounded-xl bg-white p-6 text-slate-600">No encontramos señales verificadas con esos filtros.</p>
    @endforelse
  </section>
  <div class="mt-8">{{ $signals->links() }}</div>

  @if($programs->isNotEmpty())
    <section class="mt-12 border-t border-slate-200 pt-8">
      <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-2xl font-bold">Programas independientes</h2><a class="font-semibold text-amber-800" href="{{ route('radios.programs.index') }}">Ver todos</a></div>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        @foreach($programs as $program)
          @php($next = $program->nextBroadcast())
          <a class="rounded-lg bg-stone-100 p-4" href="{{ $program->getUrl() }}"><h3 class="font-bold">{{ $program->title }}</h3>@if($next)<p class="mt-2 text-sm text-slate-600">Próxima emisión: {{ $next['starts_at']->format('d/m H:i') }} h</p>@endif</a>
        @endforeach
      </div>
    </section>
  @endif
@endsection
