@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto px-4 mt-10">
    <div class="flex flex-col lg:flex-row gap-8 mb-10">

      {{-- Columna izquierda con portada y detalles --}}
      <div class="lg:w-2/3 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          {{-- Portada y título --}}
          <div>
            <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->album }}"
              class="w-full rounded shadow">
            <h1 class="text-2xl font-bold mt-4">{{ $disco->anio }} - {{ $disco->album }}</h1>
            <p class="text-lg text-gray-700 mt-2">
              <span class="font-semibold">Intérprete:</span>
              <a href="{{ route('interprete.show', $disco->interprete->slug) }}" class="text-blue-600 hover:underline">
                {{ $disco->interprete->interprete }}
              </a>
            </p>
          </div>

          {{-- Listado de canciones --}}
          <div>
            <h2 class="text-xl font-semibold mb-2">Listado de Canciones</h2>
            <hr class="mb-4">
            <ol class="space-y-2">
              @foreach ($disco->canciones as $cancion)
                <li class="text-lg">
                  <a href="{{ route('canciones.show', [$disco->interprete->slug, $cancion->slug]) }}"
                    class="flex justify-between items-center text-gray-800 hover:bg-gray-100 p-2 rounded transition">
                    {{ $cancion->cancion }}
                    <span class="text-gray-500">
                      <i class="fas fa-file-alt mr-1"></i>
                      <i class="fas fa-video"></i>
                    </span>
                  </a>
                </li>
              @endforeach
            </ol>
          </div>
        </div>

        {{-- Spotify Embed --}}
        @if ($disco->spotify !== '')
          <div class="mt-8">
            {!! $disco->spotify !!}
          </div>
        @endif


        {{-- Muestro ls redes p compartir --}}
        <div class="redes">
          <x-compartir-redes :titulo="$disco->album" :url="Request::url()" />
        </div>

      </div>

      {{-- Columna derecha con info del intérprete --}}
      <div class="lg:w-1/3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>
    </div>

    {{-- Otros discos del intérprete --}}
    <div>
      <h3 class="text-2xl font-bold mb-6">Otros discos de {{ $interprete->interprete }}</h3>

      @if ($related->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded shadow mb-6">
          No hay discos relacionados disponibles para {{ $interprete->interprete }} aún.
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach ($related as $disco)
            <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
              class="bg-gray-800 text-white rounded overflow-hidden shadow-md hover:shadow-lg transition duration-200">
              <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->album }}"
                class="w-full h-48 object-cover">
              <div class="p-4 flex flex-col h-full">
                <h4 class="text-lg font-semibold mb-1">{{ $disco->anio }} - {{ $disco->album }}</h4>
                <p class="text-sm text-yellow-400 mb-2">{{ $disco->interprete->interprete }}</p>
                <p class="text-sm mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
              </div>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>

@endsection
