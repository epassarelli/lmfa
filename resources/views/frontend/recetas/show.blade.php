@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col lg:flex-row gap-8">

      <!-- Columna principal -->
      <div class="w-full lg:w-2/3">

        <h1 class="text-3xl font-bold mb-4">{{ $receta->titulo }}</h1>

        {{-- Imagen opcional --}}
        {{-- <img src="{{ asset('storage/recetas/' . $receta->foto) }}" alt="{{ $receta->titulo }}"
          class="mb-4 rounded-lg shadow"> --}}

        <div class="prose max-w-none mb-4">
          {!! $receta->receta !!}
        </div>

        <p class="text-gray-600 text-sm mb-8">Visitas: {{ $receta->visitas }}</p>

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
        <h3 class="text-xl font-semibold mb-4">Últimas recetas</h3>

        @foreach ($ultimas_recetas as $ultima_receta)
          <x-receta-card :receta="$ultima_receta" />
        @endforeach

      </div>

    </div>

  </div>

@endsection
