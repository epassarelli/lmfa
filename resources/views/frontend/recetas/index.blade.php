@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Recetas de comidas típicas argentinas</h1>

    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-2">Recetas de comidas más visitadas</h2>
      <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
        Descubre las recetas de comidas típicas argentinas que más interés han despertado entre nuestros visitantes.
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitadas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </div>

    <p class="text-lg text-gray-700 dark:text-gray-300 mb-10">
      Sumérgete en el sabor auténtico de la cocina argentina con nuestras recetas de comidas típicas...
    </p>

    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
        Encuentra fácilmente tus recetas favoritas utilizando nuestro índice alfabético.
      </p>

      <div class="flex flex-wrap justify-center gap-2 border-t border-b py-4">
        @foreach (range('a', 'z') as $letra)
          <a href="{{ route('comidas.letra', $letra) }}"
            class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">{{ $letra }}</a>
        @endforeach
      </div>
    </div>

    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-2">Ulimas recetas de comidas típicas agregadas
      </h2>
      <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
        Mantente al día con las novedades culinarias de nuestra cocina argentina...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </div>

    <div class="space-y-4">
      <p class="text-lg text-gray-700 dark:text-gray-300">
        Bienvenidos a nuestra sección de recetas de comidas típicas, donde te invitamos a descubrir...
      </p>
      <p class="text-lg text-gray-700 dark:text-gray-300">
        Cada receta está cuidadosamente detallada con ingredientes, pasos de preparación...
      </p>
      <p class="text-lg text-gray-700 dark:text-gray-300">
        Nuestra sección de recetas de comidas típicas es tu guía culinaria para experimentar...
      </p>
    </div>
  </div>
@endsection

@section('sidebar')

  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
