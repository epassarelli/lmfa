@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($receta);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
@endphp
@section('metaImage', $metaImage)
@section('ogType', 'article')

@if (is_array($receta->ingredients) && count($receta->ingredients) > 0 && is_array($receta->instructions) && count($receta->instructions) > 0)
  @push('json-ld')
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Recipe",
    "name": @json($receta->titulo),
    "description": @json($metaDescription),
    "image": [@json($metaImage)],
    "recipeIngredient": @json(array_values($receta->ingredients)),
    "recipeInstructions": @json(collect($receta->instructions)->values()->map(fn ($step) => ['@type' => 'HowToStep', 'text' => $step])->all())
    @if($receta->prep_time_minutes),
    "prepTime": @json('PT'.$receta->prep_time_minutes.'M')
    @endif
    @if($receta->cook_time_minutes),
    "cookTime": @json('PT'.$receta->cook_time_minutes.'M')
    @endif
    @if($receta->servings),
    "recipeYield": @json($receta->servings)
    @endif
  }
  </script>
  @endpush
@endif

@section('ogArticleTags')
  <meta property="article:section" content="Cocina Regional Argentina">
@endsection

@section('content')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <div class="flex flex-col lg:flex-row gap-8">

      <!-- Columna principal -->
      <div class="w-full lg:w-2/3">

        <h1 class="text-3xl font-bold mb-4">{{ $h1 }}</h1>

        <div class="mb-4">
          <x-editorial-image
            :entity="$receta"
            variant="main"
            class="rounded-lg shadow w-full"
            loading="eager"
            fetchpriority="high"
          />
        </div>

        <div class="prose receta-contenido max-w-none mb-4">
          {!! $receta->receta !!}
        </div>

        <p class="text-gray-600 text-sm mb-8">Visitas: {{ $receta->visitas }}</p>

        {{-- Muestro ls redes p compartir --}}
        <div class="redes">
          <x-compartir-redes :titulo="$receta->titulo" :url="Request::url()" />
        </div>

        <!-- Índice alfabético -->
        <h2 class="text-xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
        <p class="text-gray-700 mb-4">
          Encuentra fácilmente tus recetas favoritas de la cocina argentina utilizando nuestro índice alfabético...
        </p>

        <hr class="my-4">

        <nav class="flex flex-wrap justify-center gap-2 mb-4">
          @foreach (range('a', 'z') as $letra)
            <a href="{{ route('comidas.letra', $letra) }}"
              class="bg-gray-200 text-gray-800 px-3 py-1 rounded hover:bg-gray-300 text-sm font-semibold">
              {{ $letra }}
            </a>
          @endforeach
        </nav>

        <hr class="my-4">
      </div>

      <!-- Sidebar -->
      <div class="w-full lg:w-1/3">
        <h3 class="text-xl font-semibold mb-4">Recetas relacionadas</h3>

        @foreach ($relacionadas as $relacionada)
          <x-receta-card :receta="$relacionada" />
        @endforeach

      </div>

    </div>

  </div>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
