@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="container mx-auto px-4 mt-10">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Columna principal --}}
      <div class="w-full lg:w-9/12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Letras de canciones por {{ $interprete->interprete }}</h1>

        <p class="text-gray-700 text-lg leading-relaxed mb-8">
          Explora las letras de canciones de <strong>{{ $interprete->interprete }}</strong>, uno de los exponentes más
          destacados del
          folklore argentino. Sumérgete en las palabras y los mensajes que caracterizan sus melodías, cada letra
          reflejando la rica herencia cultural de nuestra tierra. Desde emotivos relatos hasta vivencias cotidianas,
          descubre la profundidad y la poesía que han cautivado a los seguidores de {{ $interprete->interprete }} a lo
          largo de los años.
        </p>

        {{-- Lista de canciones --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          @foreach ($canciones as $index => $cancion)
            <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}"
              class="block bg-white border border-gray-200 hover:bg-yellow-100 text-gray-800 px-4 py-3 rounded shadow-sm transition duration-200">
              {{ $cancion->cancion }}
            </a>
          @endforeach
        </div>

        {{-- Selector de intérprete --}}
        @include('layouts.partials.select-interprete')
      </div>

      {{-- Sidebar --}}
      <div class="w-full lg:w-3/12">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>
    </div>
  </div>
@endsection
