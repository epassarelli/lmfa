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
          ...
        </p>
        <div class="row">
          @foreach ($visitados as $disco)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="text-decoration-none">
                <div class="card">
                  <img class="card-img-top w-100 h-auto object-cover" src="{{ asset('storage/albunes/' . $disco->foto) }}"
                    alt="{{ $disco->album }}">
                  <div class="card-body">
                    <h5 class="card-title">{{ $disco->album }}</h5>
                    <p class="card-text text-gray-500 text-sm">{{ $disco->interprete->interprete }}</p>
                    <p class="card-text">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
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
        <h2>Ultimos albunes del folklore agregados</h2>
        <p class="lead">
          ...
        </p>
        <div class="row">
          @foreach ($ultimos as $disco)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                class="text-decoration-none">
                <div class="card">
                  <img class="card-img-top w-100 h-auto object-cover" src="{{ asset('storage/albunes/' . $disco->foto) }}"
                    alt="{{ $disco->album }}">
                  <div class="card-body">
                    <h5 class="card-title">{{ $disco->album }}</h5>
                    <p class="card-text text-gray-500 text-sm">{{ $disco->interprete->interprete }}</p>
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
