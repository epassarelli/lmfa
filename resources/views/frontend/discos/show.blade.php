@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    <x-disco-card :disco="$disco" />
  </div>


  {{-- Listado de canciones --}}

  <h2 class="text-xl font-semibold mb-2">Listado de Canciones</h2>
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
