@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)


@section('content')

  {{-- Contenido principal --}}
  <h1 class="text-xl font-semibold mb-6">Noticias de {{ $interprete->interprete }}</h1>

  @if ($noticias->isEmpty())
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
      No hay noticias disponibles para <strong>{{ $interprete->interprete }}</strong> aún.
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      @foreach ($noticias as $noticia)
        <x-noticia-card :noticia="$noticia" />
      @endforeach
    </div>
  @endif

  <p class="text-lg text-gray-700 leading-relaxed mt-6">
    Mantente informado con las últimas noticias sobre <strong>{{ $interprete->interprete }}</strong>. Aquí
    encontrarás las
    actualizaciones más recientes, entrevistas, lanzamientos y eventos relacionados con uno de los íconos del
    folklore argentino.
    No te pierdas ninguna novedad y sigue de cerca la trayectoria y los logros de {{ $interprete->interprete }}.
  </p>

  {{-- @include('layouts.partials.select-interprete') --}}

@endsection


@section('sidebar')
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
@endsection
