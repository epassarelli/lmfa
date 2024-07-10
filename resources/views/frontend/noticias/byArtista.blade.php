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
        <h1>Noticias de {{ $interprete->interprete }}</h1>
        <p class="lead">
          Mantente informado con las últimas noticias sobre {{ $interprete->interprete }}. Aquí encontrarás las
          actualizaciones
          más recientes, entrevistas, lanzamientos y eventos relacionados con uno de los íconos del folklore argentino. No
          te pierdas ninguna novedad y sigue de cerca la trayectoria y los logros de {{ $interprete->interprete }}.
        </p>

        <div class="row">

          @if ($noticias->isEmpty())

            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay noticias disponibles para {{ $interprete->interprete }} aún.
            </div>
          @else
            @foreach ($noticias as $noticia)
              <div class="col-md-6 mb-4">
                <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
                  class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                  <div class="card-img-top">
                    <img src="{{ asset('storage/noticias/' . $noticia->foto) }}"
                      class="img-fluid w-100 h-auto object-cover" alt="{{ $noticia->titulo }}">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $noticia->titulo }}</h5>
                    <p class="card-text mt-auto">{{ number_format($noticia->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </a>
              </div>
            @endforeach

          @endif

        </div>


        @include('layouts.partials.select-interprete')

      </div>
    </div>

  @endsection
