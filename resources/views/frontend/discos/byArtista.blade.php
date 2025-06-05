@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto px-4 mt-10">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Contenido principal --}}
      <div class="w-full lg:w-3/4">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Discografía de {{ $interprete->interprete }}</h1>
        <p class="text-lg text-gray-700 mb-6">
          Descubre la discografía completa de {{ $interprete->interprete }}, una de las figuras más influyentes del
          folklore
          argentino. Explora cada álbum, canción por canción, y sumérgete en la evolución musical de este talentoso
          artista. Desde sus primeras grabaciones hasta sus últimas producciones, encuentra aquí una colección detallada
          de su obra musical.
        </p>

        {{-- Mostrar mensaje si no hay discos --}}
        @if ($discos->isEmpty())
          <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg mb-6 border border-yellow-300">
            No hay discos disponibles para {{ $interprete->interprete }} aún.
          </div>
        @else
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($discos as $disco)
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="bg-gray-800 text-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-200">
                <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->album }}"
                  class="w-full h-48 object-cover">
                <div class="p-4 flex flex-col h-full">
                  <h2 class="text-lg font-semibold mb-1">{{ $disco->anio }} - {{ $disco->album }}</h2>
                  <p class="text-sm text-yellow-400 mb-2">{{ $disco->interprete->interprete }}</p>
                  <p class="text-sm mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            @endforeach
          </div>
        @endif

        {{-- Selector de intérprete --}}
        <div class="mt-10">
          @include('layouts.partials.select-interprete')
        </div>
      </div>

      {{-- Sidebar --}}
      <div class="w-full lg:w-1/4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>
  </div>

@endsection
