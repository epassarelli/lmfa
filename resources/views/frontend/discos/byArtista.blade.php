@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

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
              <div class="col-md-4 mb-4">
                <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                  class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                  <div class="card-img-top">
                    <img src="{{ asset('storage/albunes/' . $disco->foto) }}" class="img-fluid w-100 h-auto object-cover"
                      alt="{{ $disco->album }}">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2">{{ $disco->album }}</h5>
                    <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                      {{ $disco->interprete->interprete }}
                    </p>
                    <p class="card-text mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </a>
              </div>
            @endforeach
          @endif

        </div>

        @include('layouts.partials.select-interprete')

      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>

  @endsection
