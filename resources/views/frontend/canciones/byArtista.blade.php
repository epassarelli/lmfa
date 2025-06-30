@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  {{-- Columna principal --}}

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
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @foreach ($canciones as $index => $cancion)
      <x-letra-card :letra="$cancion" />
    @endforeach
  </div>

@endsection


@section('sidebar')

  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])

@endsection
