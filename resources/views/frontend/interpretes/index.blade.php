@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="container mx-auto px-4 py-6 text-white">
    <h1 class="text-2xl font-bold mb-6">Biografías de artistas folklóricos</h1>

    <!-- Más visitados -->
    <section class="mb-12">
      <h2 class="text-xl font-semibold mb-2">Intérpretes Más Visitados</h2>
      <p class="text-base text-gray-300 mb-6">
        Explora los perfiles de los intérpretes de folklore argentino que han capturado la mayor atención de nuestros
        visitantes...
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($visitados as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>
    </section>

    <!-- Índice alfabético -->
    <section class="mb-12">
      <h2 class="text-xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-base text-gray-300 mb-4">
        Encuentra fácilmente a tu intérprete favorito de folklore argentino utilizando nuestro índice alfabético...
      </p>

      <div class="border-t border-gray-600 my-4"></div>
      <ul class="flex flex-wrap justify-center gap-2 text-sm">
        @foreach (range('a', 'z') as $letra)
          <li>
            <a href="{{ route('interprete.letra', $letra) }}"
              class="px-3 py-1 bg-gray-700 rounded hover:bg-yellow-500 hover:text-black transition">
              {{ $letra }}
            </a>
          </li>
        @endforeach
      </ul>
      <div class="border-t border-gray-600 my-4"></div>
    </section>

    <!-- Últimos agregados -->
    <section class="mb-12">
      <h2 class="text-xl font-semibold mb-2">Últimos Intérpretes Agregados</h2>
      <p class="text-base text-gray-300 mb-6">
        Descubre los nuevos talentos y las voces emergentes del folklore argentino...
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($ultimos as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>
    </section>

    <!-- Textos finales -->
    <div class="space-y-4 text-base text-gray-300">
      <p>Bienvenidos a nuestra sección dedicada a los intérpretes de folklore argentino...</p>
      <p>Nuestra colección incluye biografías completas, letras de canciones emblemáticas...</p>
      <p>Ya seas un apasionado del folklore, un investigador o simplemente un amante...</p>
      <p>Explora la rica diversidad de artistas y cantantes que han dado vida a la música folklórica argentina...</p>
    </div>
  </div>
@endsection
