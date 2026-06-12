@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @include('frontend.folklore_tournaments.partials.hero_nav')

  <section class="mt-8 rounded-lg bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-semibold text-stone-900">Participantes</h1>
    <p class="mt-2 text-md text-stone-600 mb-8">Los 32 interpretes que forman parte de la Copa del Folklore Argentino 2026.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      @foreach ($participants as $participant)
        <article class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50">
          <div class="aspect-[4/3] overflow-hidden bg-stone-200">
            <img src="{{ $participant->imageUrl() }}" alt="{{ $participant->display_name }}" class="h-full w-full object-cover">
          </div>
          <div class="p-4">
            <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">{{ $participant->group?->name ?? 'Sin zona' }}</p>
            <h2 class="mt-2 text-lg font-semibold text-stone-900">{{ $participant->display_name }}</h2>
            @if($participant->artist?->slug)
              <a href="{{ route('artista.show', $participant->artist->slug) }}" class="mt-3 inline-flex text-sm font-medium text-orange-600 hover:text-orange-700">
                Ver perfil del artista
              </a>
            @endif
          </div>
        </article>
      @endforeach
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
