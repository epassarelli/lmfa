@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Letras de Canciones Folklóricas</h1>
    <p class="lead">Bienvenidos a nuestra sección de letras de canciones folklóricas, donde podrás explorar las letras de
      las canciones
      más emblemáticas de la música folklórica argentina. Desde clásicos inmortales hasta las composiciones
      contemporáneas, aquí encontrarás una vasta colección de letras que reflejan la riqueza y diversidad de nuestro
      folklore.</p>
    <p class="lead">Cada letra está cuidadosamente transcrita y organizada por artista y álbum, facilitándote la búsqueda
      de tus
      canciones favoritas. Descubre el significado profundo y las historias detrás de cada letra, y cómo han influido en
      la cultura y la tradición del folklore argentino. Nuestra colección incluye letras de los artistas y cantantes más
      destacados, así como de talentos emergentes que están dando forma al futuro de la música folklórica.</p>
    <p class="lead">Sumérgete en las palabras que han dado vida a la música folklórica argentina y conecta con las
      emociones y relatos
      que cada canción transmite. Ya sea que estés buscando una letra específica o simplemente quieras explorar el vasto
      repertorio de nuestro folklore, nuestra sección de letras de canciones es el lugar perfecto para ti.</p>




    <div class="row mb-4">
      <div class="col-12">
        <h2>Canciones folkloricas con más visitas</h2>
        <p class="lead">
          Descubre las letras de canciones folklóricas más visitadas por nuestros usuarios. Explora las canciones que han
          capturado los corazones de los amantes del folklore argentino, desde clásicos inolvidables hasta nuevos éxitos.
          Sumérgete en las palabras que reflejan la rica tradición cultural de nuestra música y conecta con los temas más
          populares de la escena folklórica.
        </p>
        <div class="row mb-4">
          @foreach ($visitadas as $cancion)
            <div class="col-md-4 mb-4">
              <a href="{{ route('canciones.show', [$cancion->interprete->slug, $cancion->slug]) }}"
                class="card h-100 shadow-sm text-decoration-none">
                <div class="row g-0">
                  <div class="col-auto">
                    @if (file_exists(public_path('storage/interpretes/' . $cancion->interprete->foto)) && $cancion->interprete->foto !== '')
                      <img class="img-fluid rounded-start"
                        src="{{ asset('storage/interpretes/' . $cancion->interprete->foto) }}"
                        alt="{{ $cancion->interprete }}" style="width: 6rem; height: auto; object-fit: cover;">
                    @else
                      <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                        class="card-img-top">
                    @endif
                  </div>
                  <div class="col">
                    <div class="card-body">
                      <h2 class="card-title h6 text-dark mb-2 hover:text-primary">
                        {{ $cancion->cancion }}
                      </h2>
                      <p class="card-text text-muted mb-2">
                        {{ $cancion->interprete->interprete }}
                      </p>
                      <p class="card-text">{{ number_format($cancion->visitas, 0, '', ',') }} visitas</p>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>









    <div class="row mb-4">
      <div class="col-12">
        <h2>Ultimas letras de canciones agregadas</h2>
        <p class="lead">
          Mantente al día con las últimas letras de canciones folklóricas agregadas a nuestro portal. Descubre nuevas
          incorporaciones a nuestra colección y disfruta de lo más reciente del folklore argentino. Desde lanzamientos
          recientes hasta joyas redescubiertas, encuentra las palabras y melodías que están enriqueciendo la tradición de
          nuestra música popular.
        </p>
        <div class="row mb-4">
          @foreach ($ultimas as $cancion)
            <div class="col-md-4 mb-4">
              <a href="{{ route('canciones.show', [$cancion->interprete->slug, $cancion->slug]) }}"
                class="card h-100 shadow-sm text-decoration-none">
                <div class="row g-0">
                  <div class="col-auto">
                    @if (file_exists(public_path('storage/interpretes/' . $cancion->interprete->foto)) && $cancion->interprete->foto !== '')
                      <img class="img-fluid rounded-start"
                        src="{{ asset('storage/interpretes/' . $cancion->interprete->foto) }}"
                        alt="{{ $cancion->interprete }}" style="width: 6rem; height: auto; object-fit: cover;">
                    @else
                      <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                        class="img-fluid rounded-start" style="width: 6rem; height: auto; object-fit: cover;">
                    @endif
                  </div>
                  <div class="col">
                    <div class="card-body">
                      <h2 class="card-title h6 text-dark mb-2 hover:text-primary">
                        {{ $cancion->cancion }}
                      </h2>
                      <p class="card-text text-muted mb-2">
                        {{ $cancion->interprete->interprete }}
                      </p>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>









  </div>

@endsection
