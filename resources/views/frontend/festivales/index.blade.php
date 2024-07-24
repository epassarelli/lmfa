@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">

    <h1>Festivales y Fiestas tradicionales del Folklore Argentino</h1>
    <p class="lead">Descubre la magia de las fiestas y festivales folklóricos en nuestra sección dedicada a los eventos
      que celebran la
      música folklórica argentina. Aquí encontrarás información completa sobre los festivales más importantes, las fiestas
      tradicionales y los eventos culturales que destacan la riqueza y diversidad del folklore argentino.</p>
    <p class="lead">Cada festival y fiesta está detalladamente descrito, ofreciendo información sobre las fechas,
      ubicaciones,
      actividades y artistas participantes. Desde festivales nacionales que atraen a miles de visitantes hasta
      celebraciones locales llenas de encanto y autenticidad, te ofrecemos una guía completa para disfrutar de estas
      experiencias únicas. Aprende sobre la historia y el significado de cada evento y cómo puedes participar y disfrutar
      de ellos.</p>
    <p class="lead">Sumérgete en el espíritu festivo del folklore argentino y únete a las celebraciones que honran
      nuestras tradiciones
      y cultura. Nuestra sección de fiestas y festivales folklóricos es tu recurso definitivo para estar al tanto de los
      próximos eventos y para conocer más sobre las celebraciones que hacen vibrar el corazón de nuestra música
      folklórica. Vive la alegría y la pasión del folklore en cada festival y fiesta.</p>



    {{-- <div class="row mb-4">
      <div class="col-md-8">
        <input type="text" class="form-control" id="searchFestivales" placeholder="Buscar fiestas y festivales...">
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary w-100">Buscar</button>
      </div>
    </div> --}}

    {{-- <div class="row mb-4">
      <div class="col-12">
        <h2>Buscar por provincia</h2>
        <nav>
          <ul class="pagination pagination-sm justify-content-center">
            <li class="page-item"><a class="page-link" href="#">A</a></li>
            <li class="page-item"><a class="page-link" href="#">B</a></li>
            <li class="page-item"><a class="page-link" href="#">C</a></li>
            <!-- Repite para todas las letras del abecedario -->
            <li class="page-item"><a class="page-link" href="#">Z</a></li>
          </ul>
        </nav>
      </div>
    </div> --}}





    <div class="row mb-4">
      <div class="col-12">
        <h2>Festivales Más Visitados</h2>
        <p class="lead">
          Descubre los festivales de folklore argentino que han capturado la atención de los amantes de la música y la
          cultura. Estos eventos son los más populares en nuestro portal, atrayendo a miles de visitantes cada año.
          Sumérgete en la tradición y la alegría de los festivales más destacados de Argentina.
        </p>
        <div class="row">
          @foreach ($visitados as $festival)
            <div class="col-md-4 mb-4">
              <a href="{{ route('festivales.show', $festival->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img src="{{ asset('storage/festivales/' . $festival->foto) }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $festival->interprete }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $festival->titulo }}</h5>
                  <p class="card-text mt-auto">{{ number_format($festival->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-12">
        <h2>Últimos Festivales Agregados</h2>
        <p class="lead">
          Mantente al día con los nuevos festivales de folklore argentino que se suman a nuestra colección. Esta sección
          presenta los eventos más recientes añadidos a nuestro portal, ofreciéndote las últimas novedades y oportunidades
          para disfrutar de la rica tradición folklórica de Argentina. No te pierdas los festivales recién descubiertos.
        </p>

        <div class="row">
          @foreach ($ultimos as $festival)
            <div class="col-md-4 mb-4">
              <a href="{{ route('festivales.show', $festival->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img src="{{ asset('storage/festivales/' . $festival->foto) }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $festival->interprete }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $festival->titulo }}</h5>
                  <p class="card-text mt-auto">{{ number_format($festival->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
        </div>

      </div>
    </div>


    {{-- <h1>Festivales por Provincia</h1>
    <p class="lead">Explora los festivales de folklore argentino distribuidos por todas las provincias. Encuentra
      información sobre los eventos más importantes de cada región.</p>

    <div class="row">
      <!-- Repetir este bloque para cada provincia -->
      <div class="col-md-3 mb-4">
        <div class="card">
          <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Provincia de Buenos Aires">
          <div class="card-body text-center">
            <h5 class="card-title">Buenos Aires</h5>
            <a href="festivales/provincia/buenos-aires" class="btn btn-primary">Ver Festivales</a>
          </div>
        </div>
      </div>
      <!-- Fin del bloque -->
    </div> --}}


  </div>

@endsection
