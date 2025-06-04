{{-- Tengo la siguiente view, index.show y tengo el inconveniente que tanto sea por el aspecto de las imagenes como por el
largo del texto del titulo las cards tienen distintas alturas.

Como puedo hacer para que queden todas con la misma altura? --}}

{{-- Si en lugar de mostrar la lista de noticias tal como la tengo, paginada, quisiera hacer un scroll infinito con Livewire
se puede hacer? Me dices como? --}}
@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)


@section('styles')
  <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">
@endsection


@section('content')

  <div class="container">

    <!-- Listado de noticias en cards -->
    <div class="row mt-5">
      @php
        $bloques = [
            'Noticias de folklore argentino' => $ultimas,
            // 'Actualidad' => $actualidad,
            // 'Festivales' => $festivales,
            // 'Lanzamientos' => $lanzamientos,
            // 'Cartelera' => $cartelera,
        ];
      @endphp

      @foreach ($bloques as $titulo => $noticias)
        <div class="most-popular-news">
          <div class="section-title">
            <h1>{{ $titulo }}</h1>
          </div>

          <div class="row">
            @foreach ($noticias as $noticia)
              <div class="col-md-4 mb-4">
                <x-noticia-card :noticia="$noticia" />
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>




    <p class="lead">Mantente al día con las últimas noticias del folklore argentino en nuestra sección dedicada a
      mantenerte informado
      sobre todo lo relacionado con la música folklórica de nuestro país. Aquí encontrarás las actualizaciones más
      recientes, incluyendo lanzamientos de nuevos álbumes, giras de conciertos, y eventos especiales que destacan lo
      mejor del folklore argentino.</p>
    <p class="lead">Descubre entrevistas exclusivas con tus artistas y cantantes favoritos, reportajes en profundidad
      sobre tendencias
      y movimientos dentro de la escena folklórica, y análisis detallados sobre la evolución de este género musical.
      Nuestra cobertura incluye tanto a los grandes íconos del folklore como a los nuevos talentos emergentes que están
      dando forma al futuro de la música tradicional argentina.</p>
    <p class="lead">No te pierdas ninguna novedad del mundo del folklore argentino. Desde festivales y shows hasta
      proyectos
      colaborativos y homenajes, nuestra sección de noticias te mantendrá conectado con todo lo que está ocurriendo en el
      vibrante panorama de la música folklórica argentina.</p>

    {{-- <div class="row mt-5">

      <h2>Noticias Más Visitadas del Folklore Argentino</h2>
      <p class="lead">
        Mantente al tanto de las noticias más visitadas y populares del folklore argentino. Descubre qué historias,
        eventos y novedades han captado la atención de los seguidores de la música folklórica. Desde entrevistas
        exclusivas con los artistas más influyentes hasta coberturas de los festivales y conciertos más destacados, no te
        pierdas lo más relevante y comentado en el mundo del folklore argentino.
      </p>

      @foreach ($visitadas as $noticia)
        <div class="col-md-4 mb-4">
          <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
            class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
            <div class="card-img-top">
              <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" class="img-fluid w-100 h-auto object-cover"
                alt="{{ $noticia->titulo }}">
            </div>
            <div class="card-body d-flex flex-column">
              <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $noticia->titulo }}</h5>
              <p class="card-text mt-auto">{{ number_format($noticia->visitas, 0, '', ',') }} visitas</p>
            </div>
          </a>
        </div>
      @endforeach
    </div> --}}


  </div>

@endsection
