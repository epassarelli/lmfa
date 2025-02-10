@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}"> --}}
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">
@endsection

@section('content')

  <section class="default-news-area">
    <div class="container">
      <div class="row">

        <div class="col-lg-8">
          <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
            class="mb-4 img-fluid rounded shadow-lg">
          <h1 class="fs-4">{{ $noticia->titulo }}</h1>

          <p class="fs-5 mb-4">{!! $noticia->noticia !!}</p>
          <p class="text-muted">Visitas: {{ $noticia->visitas }}</p>
        </div>

        <div class="col-lg-4">
          <aside class="widget-area">


            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Ultimas noticias</h3>


              @foreach ($ultimas_noticias as $noticia)
                <article class="item">
                  <a href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}" class="thumb">
                    <img
                      src="{{ file_exists(public_path('storage/noticias/' . $noticia->foto)) && $noticia->foto ? asset('storage/noticias/' . $noticia->foto) : asset('img/album.jpg') }}"
                      alt="{{ $noticia->titulo }}">
                  </a>
                  <div class="info">
                    <h4 class="title usmall"><a
                        href="{{ route('noticia.show', [$noticia->categoria->slug, $noticia->slug]) }}">{{ $noticia->titulo }}</a>
                    </h4>
                    <span>{{ $noticia->created_at ? $noticia->created_at->translatedFormat('d F, Y') : '' }}</span>
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
    </div>
  </section>







@endsection
