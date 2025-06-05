@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Introducción --}}
    <section class="mb-10">
      <h1 class="text-3xl font-bold mb-4">Mitos y Leyendas Tradicionales</h1>
      <p class="text-lg text-gray-700 mb-4">Bienvenidos a nuestra sección de mitos y leyendas tradicionales, donde
        exploramos las historias y relatos que forman parte del rico patrimonio cultural del folklore argentino...</p>
      <p class="text-lg text-gray-700 mb-4">Cada mito y leyenda está narrado con detalle...</p>
      <p class="text-lg text-gray-700">Sumérgete en el mundo mágico de los mitos y leyendas y conecta con las raíces
        profundas de nuestra identidad cultural...</p>
    </section>

    {{-- Mitos por letra --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Leyendas urbanas con la letra {{ strtoupper($letra) }}</h2>
      <p class="text-gray-700 mb-6">Descubre relatos tradicionales organizados alfabéticamente que enriquecen nuestra
        tradición cultural...</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($mitos as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    {{-- Índice alfabético --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-gray-700 mb-4">Encuentra fácilmente tus mitos y leyendas favoritos del folklore argentino utilizando
        nuestro índice alfabético...</p>

      <div class="flex flex-wrap justify-center gap-2 mb-6">
        @foreach (range('a', 'z') as $letra)
          <a href="{{ route('mitos.letra', $letra) }}"
            class="inline-block px-3 py-2 border border-gray-300 rounded hover:bg-gray-200 text-sm uppercase font-semibold text-gray-700">
            {{ $letra }}
          </a>
        @endforeach
      </div>

      <hr class="border-gray-300 my-6">
    </section>

    {{-- Más visitados --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Leyendas urbanas más visitadas</h2>
      <p class="text-gray-700 mb-6">Estas historias, llenas de misterio y tradición, son las más populares en nuestro
        portal...</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    {{-- Últimos mitos --}}
    <section>
      <h2 class="text-2xl font-semibold mb-2">Últimos mitos y leyendas</h2>
      <p class="text-gray-700 mb-6">Mantente al día con las nuevas adiciones que hemos agregado a nuestro portal...</p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimos as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

  </div>

@endsection
