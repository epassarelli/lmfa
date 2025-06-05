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
        @foreach ($interpretes as $visitado2)
          <a href="{{ route('interprete.show', $visitado2->slug) }}"
            class="bg-gray-800 text-white rounded shadow overflow-hidden hover:scale-[1.01] transition-transform duration-200 flex flex-col">
            <img
              src="{{ file_exists(public_path('storage/interpretes/' . $visitado2->foto)) && $visitado2->foto !== ''
                  ? asset('storage/interpretes/' . $visitado2->foto)
                  : asset('storage/img/imagennodisponible600x400.jpg') }}"
              alt="{{ $visitado2->interprete }}" class="w-full h-48 object-cover">
            <div class="p-4 flex-1 flex flex-col">
              <h5 class="text-yellow-400 text-lg font-semibold mb-2">{{ $visitado2->interprete }}</h5>
              <p class="mt-auto text-sm">{{ number_format($visitado2->visitas, 0, '', ',') }} visitas</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>

    {{-- Navegación alfabética --}}
    <div class="mt-16">
      <h2 class="text-2xl font-bold mb-4">Buscar por Orden Alfabético</h2>
      <p class="text-lg text-gray-700 mb-4">Utilizá nuestro índice alfabético para encontrar a tu artista favorito.</p>

      <nav class="flex flex-wrap gap-2 justify-center">
        @foreach (range('A', 'Z') as $letra)
          <a href="{{ route('interprete.letra', $letra) }}"
            class="px-3 py-1 border rounded text-sm hover:bg-gray-200 transition">{{ $letra }}</a>
        @endforeach
      </nav>
    </div>

    {{-- Intérpretes más visitados --}}
    <div class="mt-16">
      <h2 class="text-2xl font-bold mb-4">Intérpretes Más Visitados</h2>
      <p class="text-lg text-gray-700 mb-4">Estos artistas son los más populares del portal.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($visitados as $visitado)
          <a href="{{ route('interprete.show', $visitado->slug) }}"
            class="bg-gray-800 text-white rounded shadow overflow-hidden hover:scale-[1.01] transition-transform duration-200 flex flex-col">
            <img src="{{ asset('storage/interpretes/' . $visitado->foto) }}" alt="{{ $visitado->interprete }}"
              class="w-full h-48 object-cover">
            <div class="p-4 flex-1 flex flex-col">
              <h5 class="text-yellow-400 text-lg font-semibold mb-2">{{ $visitado->interprete }}</h5>
              <p class="mt-auto text-sm">{{ number_format($visitado->visitas, 0, '', ',') }} visitas</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>

    {{-- Últimos intérpretes agregados --}}
    <div class="mt-16">
      <h2 class="text-2xl font-bold mb-4">Últimos Intérpretes Agregados</h2>
      <p class="text-lg text-gray-700 mb-4">Conocé las últimas incorporaciones a nuestro portal.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($ultimos as $ultimo)
          <a href="{{ route('interprete.show', $ultimo->slug) }}"
            class="bg-gray-800 text-white rounded shadow overflow-hidden hover:scale-[1.01] transition-transform duration-200 flex flex-col">
            <img src="{{ asset('storage/interpretes/' . $ultimo->foto) }}" alt="{{ $ultimo->interprete }}"
              class="w-full h-48 object-cover">
            <div class="p-4 flex-1 flex flex-col">
              <h5 class="text-yellow-400 text-lg font-semibold mb-2">{{ $ultimo->interprete }}</h5>
              <p class="mt-auto text-sm">{{ number_format($ultimo->visitas, 0, '', ',') }} visitas</p>
            </div>
          </a>
        @endforeach
      </div>
    </div>

  </div>

@endsection
