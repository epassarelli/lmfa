@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Mitos y Leyendas Tradicionales</h1>
    <p class="lead">Bienvenidos a nuestra sección de mitos y leyendas tradicionales, donde exploramos las historias y
      relatos que
      forman parte del rico patrimonio cultural del folklore argentino. Aquí encontrarás una colección fascinante de mitos
      ancestrales y leyendas populares que han sido transmitidos de generación en generación, reflejando la sabiduría,
      creencias y valores de nuestra gente.</p>
    <p class="lead">Cada mito y leyenda está narrado con detalle, proporcionando contexto histórico y cultural para
      ayudarte a entender
      su significado y su relevancia en la actualidad. Descubre las historias de seres míticos, héroes legendarios y
      fenómenos sobrenaturales que han inspirado canciones, danzas y celebraciones en la música folklórica argentina.</p>
    <p class="lead">Sumérgete en el mundo mágico de los mitos y leyendas y conecta con las raíces profundas de nuestra
      identidad
      cultural. Nuestra sección es una ventana abierta a la imaginación y el misterio, invitándote a conocer y apreciar
      las narrativas que enriquecen el folklore argentino y que continúan alimentando la creatividad y la tradición de
      nuestro pueblo.</p>







    <div class="row mb-4">
      <div class="col-12">
        <h2>Leyendas urbanas con la letra {{ $letra }}</h2>
        <p class="lead">
          Mantente al día con las nuevas adiciones a nuestro repertorio de mitos y leyendas urbanas del folklore
          argentino. En esta sección, te presentamos los últimos relatos que hemos agregado a nuestro portal. Descubre
          historias recientes que enriquecen nuestra tradición cultural y explora los misterios y maravillas de los mitos
          y leyendas que continúan fascinando a las generaciones.
        </p>

        <div class="row justify-content-center">
          @foreach ($mitos as $mito)
            <div class="col-md-4 col-sm-6 mb-4">
              <div class="card">
                <a href="{{ route('mitos.show', $mito->slug) }}" class="text-decoration-none">

                  <div class="card-body">
                    <h5 class="card-title h5 text-dark">{{ $mito->titulo }}</h5>

                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>




    <div class="row mb-4">
      <div class="col-12">
        <h2>Buscar por Orden Alfabético</h2>
        <p class="lead">
          Encuentra fácilmente tus mitos y leyendas favoritos del folklore argentino utilizando nuestro índice alfabético.
          Esta sección te permite explorar una amplia gama de relatos tradicionales, ordenados de la A a la Z, facilitando
          tu búsqueda y ayudándote a descubrir nuevas historias llenas de misterio y encanto que puedes disfrutar y
          compartir.
        </p>
        <hr>
        <nav>
          <ul class="pagination pagination-sm justify-content-center">
            @foreach (range('a', 'z') as $letra)
              <li class="page-item"><a class="page-link mx-1 "
                  href="{{ route('mitos.letra', $letra) }}">{{ $letra }}</a></li>
            @endforeach
          </ul>
        </nav>
        <hr>
      </div>
    </div>





    <div class="row mb-4">
      <div class="col-12">
        <h2>Leyendas urbanas más visitadas</h2>
        <p class="lead">
          Explora los mitos y leyendas urbanas del folklore argentino que han capturado la imaginación de nuestros
          visitantes. Estas historias, llenas de misterio y tradición, son las más populares en nuestro portal. Desde el
          inquietante relato de la Luz Mala hasta las leyendas de aparecidos en el norte argentino, descubre las
          narraciones que más han fascinado a nuestra comunidad.
        </p>

        <div class="row justify-content-center">
          @foreach ($visitados as $mito)
            <div class="col-md-4 col-sm-6 mb-4">
              <div class="card">
                <a href="{{ route('mitos.show', $mito->slug) }}" class="text-decoration-none">
                  {{-- @if (file_exists(public_path('storage/mitos/' . $mito->foto)) && $mito->foto !== '')
                    <img src="{{ asset('storage/mitos/' . $mito->foto) }}" alt="{{ $mito->titulo }}"
                      class="card-img-top">
                  @else
                    <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                      class="card-img-top">
                  @endif --}}
                  <div class="card-body">
                    <h5 class="card-title  h5 text-dark">{{ $mito->titulo }}</h5>
                    <p class="card-text">{{ number_format($mito->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>












    <div class="row mb-4">
      <div class="col-12">
        <h2>Ultimos mitos y leyendas</h2>
        <p class="lead">
          Mantente al día con las nuevas adiciones a nuestro repertorio de mitos y leyendas urbanas del folklore
          argentino. En esta sección, te presentamos los últimos relatos que hemos agregado a nuestro portal. Descubre
          historias recientes que enriquecen nuestra tradición cultural y explora los misterios y maravillas de los mitos
          y leyendas que continúan fascinando a las generaciones.
        </p>

        <div class="row justify-content-center">
          @foreach ($ultimos as $mito)
            <div class="col-md-4 col-sm-6 mb-4">
              <div class="card">
                <a href="{{ route('mitos.show', $mito->slug) }}" class="text-decoration-none">
                  {{-- @if (file_exists(public_path('storage/mitos/' . $mito->foto)) && $mito->foto !== '')
                    <img src="{{ asset('storage/mitos/' . $mito->foto) }}" alt="{{ $mito->titulo }}"
                      class="card-img-top">
                  @else
                    <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                      class="card-img-top">
                  @endif --}}
                  <div class="card-body">
                    <h5 class="card-title h5 text-dark">{{ $mito->titulo }}</h5>

                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>





  </div>


@endsection
