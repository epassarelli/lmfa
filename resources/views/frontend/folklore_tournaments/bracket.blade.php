@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="rounded-lg bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-semibold text-stone-900">Llaves</h1>
    <p class="mt-2 text-sm text-stone-600">Cruces agrupados por fase. En esta version MVP no se muestra un bracket grafico avanzado.</p>

    <div class="mt-6 space-y-6">
      @forelse ($phases as $phase => $matches)
        <div class="rounded-lg border border-stone-200">
          <div class="border-b border-stone-200 bg-stone-50 px-4 py-3">
            <h2 class="text-lg font-semibold text-stone-900">{{ ucfirst(str_replace('_', ' ', $phase)) }}</h2>
          </div>
          <div class="divide-y divide-stone-200">
            @foreach ($matches as $match)
              <div class="flex flex-col gap-2 px-4 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                  <p class="font-medium text-stone-900">{{ $match->participant1?->display_name ?? 'Por definir' }} vs {{ $match->participant2?->display_name ?? 'Por definir' }}</p>
                  <p class="text-sm text-stone-600">Estado: {{ $match->statusLabel() }}</p>
                </div>
                @if($match->isFinished())
                  <div class="text-right">
                    <p class="text-sm font-semibold text-stone-900">{{ $match->participant_1_votes }} - {{ $match->participant_2_votes }}</p>
                    <p class="text-xs text-stone-600">Ganador: {{ $match->winner?->display_name ?? '—' }}</p>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <p class="text-sm text-stone-600">Todavia no hay cruces de fase final cargados.</p>
      @endforelse
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
