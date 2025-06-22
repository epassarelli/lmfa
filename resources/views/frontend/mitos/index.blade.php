@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold mb-8">Mitos y leyendas argentinas</h1>

    {{-- Más Visitadas --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Leyendas urbanas más visitadas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Explora los mitos y leyendas urbanas del folklore argentino que han capturado la imaginación de nuestros
        visitantes...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    {{-- Índice alfabético --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-lg text-gray-700 mb-4">
        Encuentra fácilmente tus mitos y leyendas favoritos del folklore argentino utilizando nuestro índice alfabético...
      </p>
      <div class="flex flex-wrap justify-center gap-2 mb-4">
        @foreach (range('a', 'z') as $letra)
          <a href="{{ route('mitos.letra', $letra) }}"
            class="inline-block px-3 py-2 border border-gray-300 rounded hover:bg-gray-200 text-sm uppercase font-semibold text-gray-700">
            {{ $letra }}
          </a>
        @endforeach
      </div>
      <hr class="my-6">
    </section>

    {{-- Últimos agregados --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Últimos mitos y leyendas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Mantente al día con las nuevas adiciones a nuestro repertorio de mitos y leyendas urbanas del folklore
        argentino...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimos as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    {{-- Cierre descriptivo --}}
    <section>
      <p class="text-lg text-gray-700 mb-4">Bienvenidos a nuestra sección de mitos y leyendas tradicionales...</p>
      <p class="text-lg text-gray-700 mb-4">Cada mito y leyenda está narrado con detalle...</p>
      <p class="text-lg text-gray-700">Sumérgete en el mundo mágico de los mitos y leyendas y conecta con las raíces
        profundas de nuestra identidad cultural...</p>
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
