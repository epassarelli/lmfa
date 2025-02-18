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

      <div class="col-lg-8">
        <div class="section-title">
          <h2>Noticias de {{ $categoria->nombre }}</h2>
        </div>


        <div class="row">

          @if ($noticias->isEmpty())

            <div class="warning"></div>
            <div class="alert alert-warning" role="alert">
              No hay noticias disponibles para {{ $categoria->nombre }} aún.
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


        {{-- @include('layouts.partials.select-interprete') --}}

      </div>

      <div class="col-lg-4">
        <aside class="widget-area">
          {{-- @include('layouts.partials.interpretes-header', ['interprete' => $interprete]) --}}
          <section class="widget widget_latest_news_thumb">
            <h3 class="widget-title">Ultimas noticias</h3>


            @foreach ($ultimas as $ultima)
              <article class="item">
                <a href="{{ route('noticia.show', [$ultima->categoria->slug, $ultima->slug]) }}" class="thumb">
                  <img
                    src="{{ file_exists(public_path('storage/noticias/' . $ultima->foto)) && $ultima->foto ? asset('storage/noticias/' . $ultima->foto) : asset('img/album.jpg') }}"
                    alt="{{ $ultima->titulo }}">
                </a>
                <div class="info">
                  <h4 class="title usmall"><a
                      href="{{ route('noticia.show', [$ultima->categoria->slug, $ultima->slug]) }}">{{ $ultima->titulo }}</a>
                  </h4>
                  <span>{{ $ultima->created_at ? $ultima->created_at->translatedFormat('d F, Y') : '' }}</span>
                  {{-- <p class="card-text mt-auto">
                      {{ $noticia->created_at ? $noticia->created_at->translatedFormat('d F, Y') : '' }}
                    </p> --}}
                </div>
              </article>
            @endforeach

          </section>
        </aside>
      </div>

    </div>

  @endsection
