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
              <div class="col-md-6 mb-4">
                <x-noticia-card :noticia="$noticia" />
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
