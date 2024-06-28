@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-9">
        <h1>Letras de canciones por {{ $interprete->interprete }}</h1>
        <p class="lead">
          Explora las letras de canciones de {{ $interprete->interprete }}, uno de los exponentes más destacados del
          folklore argentino. Sumérgete en las palabras y los mensajes que caracterizan sus melodías, cada letra
          reflejando la rica herencia cultural de nuestra tierra. Desde emotivos relatos hasta vivencias cotidianas,
          descubre la profundidad y la poesía que han cautivado a los seguidores de {{ $interprete->interprete }} a lo
          largo de los años.
        </p>

        <div class="row">
          <ul class="list-group">
            @foreach ($canciones as $index => $cancion)
              <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}"
                class="list-group-item @if ($index % 2 == 0) list-group-item-secondary @endif">
                {{ $cancion->interprete->interprete }} - {{ $cancion->cancion }}
              </a>
            @endforeach
          </ul>
        </div>

        @include('layouts.partials.select-interprete')

      </div>

      <style>
        .list-group-item:hover {
          background-color: orange;
        }
      </style>







    </div>

  @endsection
