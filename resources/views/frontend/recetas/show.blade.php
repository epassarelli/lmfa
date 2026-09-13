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
    <div class="redes mb-4">
      <x-compartir-redes :titulo="$receta->titulo" :url="Request::url()" />
    </div>

    @if ($relacionadas && $relacionadas->count() > 0)
      <section class="bg-white p-2 mb-4">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-2">
          Recetas relacionadas
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          @foreach ($relacionadas as $relacionada)
            <x-receta-card :receta="$relacionada" />
          @endforeach
        </div>
      </section>
    @endif

    <x-alpha-filter
      class="mb-4"
      title="Buscar por orden alfabético"
      description="Encontrá fácilmente tus recetas favoritas de la cocina argentina utilizando nuestro índice alfabético."
      route-name="comidas.letra"
    />

  </div>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
  <x-sidebar.advertisement />

@endsection
