@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold mb-4">Recetas de Comidas Típicas</h1>
    <div class="prose max-w-none">
      <p>Bienvenidos a nuestra sección de recetas de comidas típicas...</p>
      <p>Cada receta está cuidadosamente detallada...</p>
      <p>Nuestra sección de recetas de comidas típicas es tu guía culinaria...</p>
    </div>

    <section class="mt-12">
      <h2 class="text-2xl font-semibold mb-2">Recetas de comidas con la letra {{ strtoupper($letra) }}</h2>
      <p class="text-gray-700 mb-6">Explora las recetas publicadas organizadas alfabéticamente.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($comidas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>

    <section class="mt-16">
      <h2 class="text-2xl font-semibold mb-2">Recetas de comidas más visitadas</h2>
      <div class="prose max-w-none mb-6">
        <p>Descubre las recetas de comidas típicas argentinas que más interés...</p>
        <p>Sumérgete en el sabor auténtico de la cocina argentina...</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($visitadas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>

    <x-alpha-filter
      class="mt-16"
      title="Buscar por Orden Alfabético"
      description="Encuentra fácilmente tus recetas favoritas..."
      route-name="comidas.letra"
      :letters="$alphabet"
      :active-letter="$letra"
    />

    <section class="mt-16">
      <h2 class="text-2xl font-semibold mb-2">Últimas recetas de comidas típicas agregadas</h2>
      <p class="text-gray-700 mb-6">Mantente al día con las novedades culinarias...</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($ultimas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
