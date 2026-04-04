@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4">Intérpretes de Folklore Argentino</h1>
    <h2 class="text-xl font-semibold mb-4">{{ request()->segment(1) }}</h2>

    <div class="space-y-4 text-lg text-gray-700">
      <p>Bienvenidos a nuestra sección dedicada a los intérpretes de folklore argentino. Aquí encontrarás información
        detallada sobre los cantantes y artistas más destacados que han dado vida a la rica tradición de la música
        folklórica de Argentina.</p>
      <p>Nuestra colección incluye biografías completas, letras de canciones emblemáticas, discografías, próximos shows y
        noticias relevantes sobre cada artista.</p>
      <p>Ya seas un apasionado del folklore, un investigador o simplemente un amante de la buena música, esta es la
        sección ideal para vos.</p>
    </div>

    {{-- Intérpretes por letra --}}
    <div class="mt-12">
      <h2 class="text-2xl font-bold mb-4">Intérpretes con la letra {{ request()->segment(1) }}</h2>
      <p class="text-lg text-gray-700 mb-4">Explora los perfiles de los intérpretes que han capturado la mayor atención.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($interpretes as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>
    </div>

    {{-- Navegación alfabética --}}
    <div class="mt-8 mb-8">
      {{ $interpretes->links() }}
    </div>

    <div class="mt-16">
      <h2 class="text-2xl font-bold mb-4">Buscar por Orden Alfabético</h2>
      <p class="text-lg text-gray-700 mb-4">Utilizá nuestro índice alfabético para encontrar a tu artista favorito.</p>

      <nav class="flex flex-wrap gap-2 justify-center">
        @foreach (range('a', 'z') as $lt)
          <a href="{{ route('interpretes.letra', strtolower($lt)) }}"
            class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-[#ff661f] hover:text-white transition uppercase font-semibold">{{ $lt }}</a>
        @endforeach
      </nav>
    </div>

  </div>

@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
