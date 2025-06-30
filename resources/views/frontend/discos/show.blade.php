@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="grid grid-cols-12 gap-6">

    {{-- Izquierda: Datos del disco --}}
    <div class="col-span-12 md:col-span-4">
      <div class="bg-white rounded shadow p-2 space-y-2">
        @if ($disco->foto)
          <img src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->titulo }}" class="rounded mb-4 w-full">
        @endif

        <h1 class="text-2xl font-bold text-gray-800">{{ $disco->titulo }}</h1>

        @if ($disco->anio)
          <p class="text-sm text-gray-500"><strong>Año:</strong> {{ $disco->anio }}</p>
        @endif

        @if ($disco->interprete)
          <p class="text-sm text-gray-500"><strong>Intérprete:</strong> {{ $disco->interprete->interprete }}</p>
        @endif
      </div>
    </div>

    {{-- Derecha: Listado de canciones (si hay), sino Spotify --}}
    @if ($disco->canciones && $disco->canciones->count())
      <div class="col-span-12 md:col-span-8">
        <h2 class="text-xl font-semibold mb-3">Listado de Canciones</h2>
        <ul class="divide-y divide-gray-200 border rounded overflow-hidden">
          @foreach ($disco->canciones as $cancion)
            <li>
              <a href="{{ route('artista.cancion', [$disco->interprete->slug, $cancion->slug]) }}"
                class="flex justify-between items-center px-4 py-2 text-gray-800 hover:bg-[#ff661f]/10 transition-colors">
                <span>{{ $cancion->cancion }}</span>
                <span class="text-gray-500 flex gap-2">
                  <i class="fas fa-file-alt"></i>
                  <i class="fas fa-video"></i>
                </span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    @elseif ($disco->spotify !== '')
      <div class="col-span-12 md:col-span-8">
        {!! $disco->spotify !!}
      </div>
    @endif

  </div>


  {{-- Spotify Embed en sección separada si hay canciones --}}
  @if ($disco->canciones && $disco->canciones->count() && $disco->spotify !== '')
    <div class="mt-8">
      {!! $disco->spotify !!}
    </div>
  @endif


  {{-- Muestro ls redes p compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$disco->album" :url="Request::url()" />
  </div>


  {{-- Otros discos del intérprete --}}
  <h3 class="text-xl font-semibold mb-6 border-b-2 border-[#ff661f]">Otros discos de {{ $interprete->interprete }}</h3>

  @if ($related->isEmpty())

    <div class="bg-yellow-100 text-yellow-800 p-4 rounded shadow mb-6">
      No hay discos relacionados disponibles para {{ $interprete->interprete }} aún.
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      @foreach ($related as $disco)
        <x-disco-card :disco="$disco" />
      @endforeach
    </div>

  @endif


@endsection

@section('sidebar')

  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])

@endsection
