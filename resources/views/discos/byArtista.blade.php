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
        <h1>Discografía de {{ $interprete->interprete }}</h1>
        <p class="lead">
          Descubre la discografía completa de {{ $interprete->interprete }}, una de las figuras más influyentes del
          folklore
          argentino. Explora cada álbum, canción por canción, y sumérgete en la evolución musical de este talentoso
          artista. Desde sus primeras grabaciones hasta sus últimas producciones, encuentra aquí una colección detallada
          de su obra musical.
        </p>

        <div class="row">

          @if ($discos->isEmpty())
            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay discos disponibles para {{ $interprete->interprete }} aún.
            </div>
          @else
            @foreach ($discos as $disco)
              <div class="col-md-4 mt-4">
                <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                  class="text-decoration-none">
                  <div class="card bg-white rounded-lg shadow-md overflow-hidden">
                    <img class="card-img-top w-100 h-auto object-cover"
                      src="{{ asset('storage/albunes/' . $disco->foto) }}" alt="{{ $disco->album }}">
                    <div class="card-body">
                      <h5 class="card-title text-lg font-medium text-gray-800 mb-2 hover:text-blue-600">
                        {{ $disco->album }}
                      </h5>
                      <p class="card-text text-gray-500 text-sm mb-2">{{ $disco->interprete->interprete }}</p>
                    </div>
                  </div>
                </a>
              </div>
            @endforeach
          @endif

        </div>

      </div>

    </div>

  @endsection
