@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}"> --}}
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">
@endsection

@section('content')


  <div class="container mt-5">
    <div class="row mb-4">

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
              <div class="col-lg-6">
                <div class="single-sports-news-box">
                  <div class="sports-news-image">
                    <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">
                      <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}">
                    </a>

                    <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                      <i class="bx bx-play-circle"></i>
                    </a>
                  </div>

                  <div class="sports-news-content">
                    <span>Football</span>
                    <h3>
                      <a
                        href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">{{ $noticia->titulo }}</a>
                    </h3>
                    <p><a href="#">{{ number_format($noticia->visitas, 0, '', ',') }} visitas</a> / 28 September,
                      2024</p>
                  </div>
                </div>
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
