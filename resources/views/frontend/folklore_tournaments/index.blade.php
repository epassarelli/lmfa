@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.folklore_tournaments.partials.hero_nav')

  <section class="mt-8 grid gap-6 xl:grid-cols-2">
    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h2 class="text-xl font-semibold text-stone-900">Proximos partidos</h2>
      <div class="mt-4 space-y-3">
        @forelse ($upcomingMatches as $match)
          <div class="rounded-lg border border-stone-200 p-4">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <img src="{{ $match->participant1?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant1?->display_name }}" class="h-12 w-12 rounded-md object-cover">
                <div>
                  <p class="text-sm font-semibold text-stone-900">{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</p>
                  <p class="text-sm text-stone-600">{{ $match->group?->name ?? 'Fase final' }} · {{ ucfirst(str_replace('_', ' ', $match->phase)) }}</p>
                </div>
                <img src="{{ $match->participant2?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant2?->display_name }}" class="h-12 w-12 rounded-md object-cover">
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase text-stone-700">{{ $match->statusLabel() }}</span>
            </div>
          </div>
        @empty
          <p class="text-sm text-stone-600">Todavia no hay partidos programados para mostrar.</p>
        @endforelse
      </div>
    </div>

    <div class="rounded-lg bg-white p-5 shadow-sm">
      <h2 class="text-xl font-semibold text-stone-900">Ultimos resultados</h2>
      <div class="mt-4 space-y-3">
        @forelse ($latestResults as $match)
          <div class="rounded-lg border border-stone-200 p-4">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <img src="{{ $match->participant1?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant1?->display_name }}" class="h-12 w-12 rounded-md object-cover">
                <div>
                  <p class="text-sm font-semibold text-stone-900">{{ $match->participant1?->display_name }} {{ $match->participant_1_votes }} - {{ $match->participant_2_votes }} {{ $match->participant2?->display_name }}</p>
                  <p class="text-sm text-stone-600">{{ $match->group?->name ?? 'Fase final' }} · {{ ucfirst(str_replace('_', ' ', $match->phase)) }}</p>
                </div>
                <img src="{{ $match->participant2?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant2?->display_name }}" class="h-12 w-12 rounded-md object-cover">
              </div>
              @if($match->instagram_url)
                <a href="{{ $match->instagram_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-orange-600 hover:text-orange-700">Ver post</a>
              @endif
            </div>
          </div>
        @empty
          <p class="text-sm text-stone-600">Aun no hay resultados finalizados cargados.</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
    <h2 class="text-2xl font-semibold text-stone-900">Como funciona la copa</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-3">
      <div class="rounded-lg border border-stone-200 p-4">
        <h3 class="font-semibold text-stone-900">1. Se publica el cruce</h3>
        <p class="mt-2 text-sm text-stone-600">Cada partido se anuncia en las publicaciones oficiales de Instagram de Mi Folklore Argentino.</p>
      </div>
      <div class="rounded-lg border border-stone-200 p-4">
        <h3 class="font-semibold text-stone-900">2. La comunidad vota</h3>
        <p class="mt-2 text-sm text-stone-600">Los votos se registran fuera del sitio y luego se cargan manualmente para reflejar el resultado oficial.</p>
      </div>
      <div class="rounded-lg border border-stone-200 p-4">
        <h3 class="font-semibold text-stone-900">3. El portal actualiza tablas</h3>
        <p class="mt-2 text-sm text-stone-600">Resultados, posiciones, fixture y llaves se actualizan desde la administracion del portal.</p>
      </div>
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
