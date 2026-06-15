@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Recetas de comidas típicas argentinas</h1>

    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2">Recetas de comidas más visitadas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Descubre las recetas de comidas típicas argentinas que más interés han despertado entre nuestros visitantes.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitadas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </div>

    <p class="text-lg text-gray-700 mb-10">
      Sumérgete en el sabor auténtico de la cocina argentina con nuestras recetas de comidas típicas...
    </p>

    <x-alpha-filter
      class="mb-12"
      title="Buscar por Orden Alfabético"
      description="Encuentra fácilmente tus recetas favoritas utilizando nuestro índice alfabético."
      route-name="comidas.letra"
      :letters="$alphabet"
    />

    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 mb-2">Últimas recetas de comidas típicas agregadas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Mantente al día con las novedades culinarias de nuestra cocina argentina...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </div>

    <div class="space-y-4">
      <p class="text-lg text-gray-700">
        Bienvenidos a nuestra sección de recetas de comidas típicas, donde te invitamos a descubrir...
      </p>
      <p class="text-lg text-gray-700">
        Cada receta está cuidadosamente detallada con ingredientes, pasos de preparación...
      </p>
      <p class="text-lg text-gray-700">
        Nuestra sección de recetas de comidas típicas es tu guía culinaria para experimentar...
      </p>
    </div>
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
