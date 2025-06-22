@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-3xl font-bold mb-4">Recetas de Comidas Típicas</h1>
    <div class="prose max-w-none">
      <p>Bienvenidos a nuestra sección de recetas de comidas típicas...</p>
      <p>Cada receta está cuidadosamente detallada...</p>
      <p>Nuestra sección de recetas de comidas típicas es tu guía culinaria...</p>
    </div>

    <!-- Recetas por letra -->
    <section class="mt-12">
      <h2 class="text-2xl font-semibold mb-2">Recetas de comidas con la letra {{ $letra }}</h2>
      <p class="text-gray-700 mb-6">Explora los perfiles de los intérpretes...</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($comidas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>

    <!-- Recetas más visitadas -->
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

    <!-- Índice alfabético -->
    <section class="mt-16">
      <h2 class="text-2xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-gray-700 mb-4">Encuentra fácilmente tus recetas favoritas...</p>
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
    </section>

    <!-- Últimas recetas -->
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

  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
