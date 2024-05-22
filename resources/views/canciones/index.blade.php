@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">

    <h1>Letras de Canciones Folklóricas</h1>
    <p>Bienvenidos a nuestra sección de letras de canciones folklóricas, donde podrás explorar las letras de las canciones
      más emblemáticas de la música folklórica argentina. Desde clásicos inmortales hasta las composiciones
      contemporáneas, aquí encontrarás una vasta colección de letras que reflejan la riqueza y diversidad de nuestro
      folklore.</p>
    <p>Cada letra está cuidadosamente transcrita y organizada por artista y álbum, facilitándote la búsqueda de tus
      canciones favoritas. Descubre el significado profundo y las historias detrás de cada letra, y cómo han influido en
      la cultura y la tradición del folklore argentino. Nuestra colección incluye letras de los artistas y cantantes más
      destacados, así como de talentos emergentes que están dando forma al futuro de la música folklórica.</p>
    <p>Sumérgete en las palabras que han dado vida a la música folklórica argentina y conecta con las emociones y relatos
      que cada canción transmite. Ya sea que estés buscando una letra específica o simplemente quieras explorar el vasto
      repertorio de nuestro folklore, nuestra sección de letras de canciones es el lugar perfecto para ti.</p>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 p-4">
      @foreach ($canciones as $cancion)
        <div class="col">
          <a href="{{ route('interprete.cancion.show', [$cancion->interprete->slug, $cancion->slug]) }}"
            class="card h-100 shadow-sm text-decoration-none">
            <div class="row g-0">
              <div class="col-auto">
                <img class="img-fluid rounded-start" src="{{ asset('storage/interpretes/' . $cancion->interprete->foto) }}"
                  alt="{{ $cancion->cancion }}" style="width: 6rem; height: auto; object-fit: cover;">
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

@endsection
