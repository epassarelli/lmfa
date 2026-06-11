@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.folklore_tournaments.partials.hero_nav')

  <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-semibold text-stone-900">Fixture</h1>
    <p class="mt-2 text-sm text-stone-600">Partidos agrupados por fase y jornada.</p>

    <div class="mt-6 space-y-6">
      @foreach ($matchesByMatchday as $groupKey => $matches)
        @php
          [$phase, $matchday] = explode('|', $groupKey);
        @endphp
        <div class="rounded-lg border border-stone-200">
          <div class="border-b border-stone-200 bg-stone-50 px-4 py-3">
            <h2 class="text-lg font-semibold text-stone-900">{{ ucfirst(str_replace('_', ' ', $phase)) }} · Jornada {{ $matchday === 'sin-jornada' ? '-' : $matchday }}</h2>
          </div>
          <div class="divide-y divide-stone-200">
            @foreach ($matches as $match)
              <div class="flex flex-col gap-2 px-4 py-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                  <img src="{{ $match->participant1?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant1?->display_name }}" class="h-12 w-12 rounded-full border border-stone-200 object-cover">
                  <div>
                    <p class="font-medium text-stone-900">{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</p>
                    <p class="text-sm text-stone-600">{{ $match->group?->name ?? 'Fase final' }}</p>
                  </div>
                  <img src="{{ $match->participant2?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant2?->display_name }}" class="h-12 w-12 rounded-full border border-stone-200 object-cover">
                </div>
                <div class="flex items-center gap-3">
                  <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase text-stone-700">{{ $match->statusLabel() }}</span>
                  @if($match->isFinished())
                    <span class="text-sm font-semibold text-stone-900">{{ $match->participant_1_votes }} - {{ $match->participant_2_votes }}</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
