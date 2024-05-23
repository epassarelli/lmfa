{{-- Tengo la siguiente view, index.show y tengo el inconveniente que tanto sea por el aspecto de las imagenes como por el
largo del texto del titulo las cards tienen distintas alturas.

Como puedo hacer para que queden todas con la misma altura? --}}

{{-- Si en lugar de mostrar la lista de noticias tal como la tengo, paginada, quisiera hacer un scroll infinito con Livewire
se puede hacer? Me dices como? --}}
@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">
    <h1>Noticias del Folklore Argentino</h1>
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
    {{-- <div class="d-flex flex-wrap justify-content-center">
      {{ auth()->user() }}
      <br>
      @if (!empty($administrados))
        @foreach ($administrados as $inte)
          {{ $inte }}
        @endforeach
      @else
        {{ 'No posee interpretes administrados' }}
      @endif
    </div> --}}

    <!-- Listado de noticias en cards -->
    <div class="row">
      @foreach ($noticias as $noticia)
        <div class="col-md-4 pb-4">
          <div class="card h-100 shadow-sm text-decoration-none">
            <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
              class="text-decoration-none">
              <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
                class="card-img-top" style="height: 12rem; object-fit: cover;">
              <div class="card-body">
                <h3 class="card-title h5 text-dark">{{ $noticia->titulo }}</h3>
                <p class="card-text">{{ number_format($noticia->visitas, 0, '', ',') }} visitas</p>
              </div>
            </a>
          </div>
        </div>
      @endforeach
    </div>


  </div>

@endsection
