@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Discografías del Folklore Argentino</h1>
    <p>Bienvenidos a nuestra sección de discografías del folklore argentino, donde encontrarás una completa colección de
      álbumes y grabaciones de los más destacados artistas y cantantes de la música folklórica argentina. Explora las
      obras maestras que han definido y enriquecido este género musical, desde los clásicos inmortales hasta los
      lanzamientos más recientes.</p>
    <p>Cada discografía está detalladamente organizada para ofrecerte información sobre los álbumes, incluyendo listas de
      canciones, fechas de lanzamiento y colaboraciones especiales. Descubre la evolución musical de tus artistas
      favoritos a través de sus producciones discográficas, y sumérgete en la riqueza y diversidad de la música folklórica
      argentina.</p>
    <p>Nuestra sección de discografías es el recurso definitivo para los amantes del folklore que desean conocer más sobre
      la trayectoria musical de sus ídolos y explorar nuevos sonidos. Ya sea que estés buscando un álbum en particular o
      simplemente quieras descubrir más sobre la historia musical del folklore argentino, aquí encontrarás todo lo que
      necesitas.</p>


    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 p-4">
      @foreach ($discos as $disco)
        <div class="col">
          <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
            class="text-decoration-none">
            <div class="card bg-white rounded-lg shadow-md overflow-hidden">
              <img class="card-img-top w-100 h-auto object-cover" src="{{ asset('storage/albunes/' . $disco->foto) }}"
                alt="{{ $disco->album }}">
              <div class="card-body">
                <h5 class="card-title text-lg font-medium text-gray-800 mb-2 hover:text-blue-600">{{ $disco->album }}</h5>
                <p class="card-text text-gray-500 text-sm mb-2">{{ $disco->interprete->interprete }}</p>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>


  </div>

@endsection
