@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Discografías del Folklore Argentino</h1>
    <p class="lead">Bienvenidos a nuestra sección de discografías del folklore argentino, donde encontrarás una completa
      colección de
      álbumes y grabaciones de los más destacados artistas y cantantes de la música folklórica argentina. Explora las
      obras maestras que han definido y enriquecido este género musical, desde los clásicos inmortales hasta los
      lanzamientos más recientes.</p>
    <p class="lead">Cada discografía está detalladamente organizada para ofrecerte información sobre los álbumes,
      incluyendo listas de
      canciones, fechas de lanzamiento y colaboraciones especiales. Descubre la evolución musical de tus artistas
      favoritos a través de sus producciones discográficas, y sumérgete en la riqueza y diversidad de la música folklórica
      argentina.</p>
    <p class="lead">Nuestra sección de discografías es el recurso definitivo para los amantes del folklore que desean
      conocer más sobre
      la trayectoria musical de sus ídolos y explorar nuevos sonidos. Ya sea que estés buscando un álbum en particular o
      simplemente quieras descubrir más sobre la historia musical del folklore argentino, aquí encontrarás todo lo que
      necesitas.</p>




    <div class="row mb-4">
      <div class="col-12">
        <h2>Discos folklóricos Más Visitados</h2>
        <p class="lead">
          Descubre los discos folklóricos más visitados y populares del folklore argentino. Estos álbumes han capturado la
          atención y el corazón de los amantes de la música folklórica, destacándose por su calidad y autenticidad.
          Explora los trabajos más escuchados de los artistas más influyentes en el folklore argentino y sumérgete en las
          melodías que definen nuestra rica herencia cultural.
        </p>
        <div class="row">
          @foreach ($visitados as $disco)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img
                    src="{{ file_exists(public_path('storage/albunes/' . $disco->foto)) && $disco->foto ? asset('storage/albunes/' . $disco->foto) : asset('img/imagennodisponible400x400.jpg') }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $disco->album }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">{{ $disco->album }}</h5>
                  <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                    {{ $disco->interprete->interprete }}
                  </p>
                  <p class="card-text mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>









    <div class="row mb-4">
      <div class="col-12">
        <h2>Ultimos albunes del folklore agregados</h2>
        <p class="lead">
          Mantente al día con los últimos discos agregados al portal de folklore argentino. Aquí encontrarás las novedades
          más recientes en la discografía de nuestros artistas, incluyendo los lanzamientos más frescos que enriquecen la
          tradición de nuestra música folklórica. No te pierdas la oportunidad de descubrir nuevos sonidos y talentos
          emergentes en el mundo del folklore argentino.
        </p>
        <div class="row">
          @foreach ($ultimos as $disco)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img
                    src="{{ file_exists(public_path('storage/albunes/' . $disco->foto)) && $disco->foto ? asset('storage/albunes/' . $disco->foto) : asset('img/imagennodisponible400x400.jpg') }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $disco->album }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2">{{ $disco->album }}</h5>
                  <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                    {{ $disco->interprete->interprete }}
                  </p>
                  <p class="card-text mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>





  </div>

@endsection
