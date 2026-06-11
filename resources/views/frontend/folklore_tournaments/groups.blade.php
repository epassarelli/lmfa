@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.folklore_tournaments.partials.hero_nav')

  <section class="space-y-8">
    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-semibold text-stone-900">Zonas y tablas</h1>
      <p class="mt-2 text-sm text-stone-600">Participantes, posiciones y partidos de cada grupo.</p>
    </div>

    @foreach ($groups as $entry)
      <article class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-stone-900">{{ $entry['group']->name }}</h2>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50">
              <tr>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">Participante</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">PJ</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">PG</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">PE</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">PP</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">VF</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">VC</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">DIF</th>
                <th class="px-3 py-2 text-left font-semibold text-stone-700">PTS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
              @foreach ($entry['standings'] as $row)
                @php
                  $participant = $entry['group']->participants->firstWhere('id', $row['participant_id']);
                @endphp
                <tr>
                  <td class="px-3 py-2">
                    <div class="flex items-center gap-3">
                      <img src="{{ $participant?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $row['display_name'] }}" class="h-10 w-10 rounded-md object-cover">
                      <div>
                        <div class="font-medium text-stone-900">{{ $row['display_name'] }}</div>
                        <div class="text-xs text-stone-500">ID {{ $participant?->artist_id ?? '-' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-2">{{ $row['pj'] }}</td>
                  <td class="px-3 py-2">{{ $row['pg'] }}</td>
                  <td class="px-3 py-2">{{ $row['pe'] }}</td>
                  <td class="px-3 py-2">{{ $row['pp'] }}</td>
                  <td class="px-3 py-2">{{ $row['votos_a_favor'] }}</td>
                  <td class="px-3 py-2">{{ $row['votos_en_contra'] }}</td>
                  <td class="px-3 py-2">{{ $row['diferencia'] }}</td>
                  <td class="px-3 py-2 font-semibold">{{ $row['puntos'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-2">
          @foreach ($entry['matches'] as $match)
            <div class="rounded-lg border border-stone-200 p-4">
              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <img src="{{ $match->participant1?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant1?->display_name }}" class="h-12 w-12 rounded-md object-cover">
                  <div>
                    <p class="font-medium text-stone-900">{{ $match->participant1?->display_name }} vs {{ $match->participant2?->display_name }}</p>
                    <p class="text-sm text-stone-600">Jornada {{ $match->matchday ?? '-' }}</p>
                  </div>
                  <img src="{{ $match->participant2?->imageUrl() ?? asset('img/album.jpg') }}" alt="{{ $match->participant2?->display_name }}" class="h-12 w-12 rounded-md object-cover">
                </div>
                <div>
                  @if($match->isFinished())
                    <span class="text-sm font-semibold text-stone-900">{{ $match->participant_1_votes }} - {{ $match->participant_2_votes }}</span>
                  @else
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase text-stone-700">{{ $match->statusLabel() }}</span>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </article>
    @endforeach
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
