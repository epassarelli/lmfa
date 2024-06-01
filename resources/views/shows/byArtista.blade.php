@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <style>

  </style>

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-9">
        <h1>Shows de {{ $interprete->interprete }}</h1>
        <p class="lead">
          Consulta la cartelera de shows y eventos de {{ $interprete->interprete }}, y no te pierdas la oportunidad de ver
          en vivo a uno de los mayores exponentes del folklore argentino. Aquí encontrarás fechas, lugares y detalles de
          todas sus presentaciones. Acompaña a {{ $interprete->interprete }} en sus giras y disfruta de una experiencia
          única con su música en directo.
        </p>

        <div class="row">
          @if ($shows->isEmpty())
            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay shows disponibles para {{ $interprete->interprete }} aún.
            </div>
          @else
            @foreach ($shows as $evento)
              <div class="card mb-3">
                <div class="row g-0">
                  <div class="col-md-4 col-lg-2 d-flex align-items-center justify-content-center bg-light">
                    <div class="text-center">
                      <h5 class="card-title mb-1">{{ date('d', strtotime($evento->fecha)) }}</h5>
                      <p class="card-text mb-1">{{ date('M', strtotime($evento->fecha)) }}</p>
                      <p class="card-text">{{ date('Y', strtotime($evento->fecha)) }}</p>
                    </div>
                  </div>
                  <div class="col-md-8 col-lg-10">
                    <div class="card-body">
                      <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                          alt="{{ $evento->interprete->interprete }}" class="rounded-circle me-3"
                          style="width: 50px; height: 50px;">
                        <h5 class="card-title m-0">{{ $evento->interprete->interprete }}</h5>
                      </div>
                      <h6 class="card-subtitle mb-2 text-muted"><strong>Show:</strong> {{ $evento->show }}</h6>
                      {{-- <p class="card-text"><strong>Show:</strong> {{ $evento->show }}</p> --}}
                      <p class="card-text"><strong>Detalles:</strong> {!! $evento->detalle !!}</p>
                      <p class="card-text"><strong>Lugar:</strong> {{ $evento->lugar }}</p>
                      <p class="card-text">{{ $evento->descripcion }}</p>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        @include('layouts.partials.select-interprete')

      </div>

    </div>

  @endsection
