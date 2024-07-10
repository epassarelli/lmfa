@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container">
    <h1>Intérpretes de Folklore Argentino</h1>
    <p class="lead">Bienvenidos a nuestra sección dedicada a los intérpretes de folklore argentino. Aquí encontrarás
      información
      detallada sobre los cantantes y artistas más destacados que han dado vida a la rica tradición de la música
      folklórica de Argentina. Desde leyendas consagradas hasta nuevos talentos, exploramos las historias, trayectorias y
      contribuciones de aquellos que mantienen viva la esencia del folklore argentino.</p>
    <p class="lead">Nuestra colección incluye biografías completas, letras de canciones emblemáticas, discografías,
      próximos shows y
      noticias relevantes sobre cada artista. Con un enfoque en la autenticidad y la profundidad, cada perfil de
      intérprete te permitirá descubrir y apreciar la diversidad y el legado cultural del folklore argentino.</p>
    <p class="lead">Ya seas un apasionado del folklore, un investigador o simplemente un amante de la buena música,
      nuestra sección de
      intérpretes de folklore argentino es el lugar ideal para sumergirte en el mundo de la música folklórica de nuestro
      país. Explora y disfruta de los sonidos, historias y talentos que definen nuestra identidad cultural.</p>



    {{-- <h1>Intérpretes de Folklore Argentino</h1> --}}
    <p class="lead">Explora la rica diversidad de artistas y cantantes que han dado vida a la música folklórica
      argentina. Encuentra biografías, discografías, letras de canciones y más.</p>

    {{-- <div class="row mb-4">
      <div class="col-md-8">
        <input type="text" class="form-control" id="searchInterpretes" placeholder="Buscar intérpretes...">
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary w-100">Buscar</button>
      </div>
    </div> --}}





    <div class="row mb-4">
      <div class="col-12">
        <h2>Intérpretes Más Visitados</h2>
        <p class="lead">
          Explora los perfiles de los intérpretes de folklore argentino que han capturado la mayor atención de nuestros
          visitantes. Estos artistas se destacan por su popularidad y su contribución significativa a la música
          folklórica. Sumérgete en la vida y obra de los cantantes y músicos más reconocidos de nuestro portal.
        </p>

        <div class="row">
          <!-- Repetir este bloque para cada intérprete -->
          @foreach ($visitados as $visitado)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.show', $visitado->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img src="{{ asset('storage/interpretes/' . $visitado->foto) }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $visitado->interprete }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $visitado->interprete }}</h5>
                  <p class="card-text mt-auto">{{ number_format($visitado->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
          <!-- Fin del bloque -->
        </div>
      </div>
    </div>





    <div class="row mb-4">
      <div class="col-12">
        <h2>Buscar por Orden Alfabético</h2>
        <p class="lead">
          Encuentra fácilmente a tu intérprete favorito de folklore argentino utilizando nuestro índice alfabético. Esta
          sección te permite navegar por nuestra extensa base de datos de artistas ordenados de la A a la Z, facilitando
          la búsqueda de biografías, discografías y mucho más sobre los talentos del folklore.
        </p>

        <nav>
          <hr>
          <ul class="pagination pagination-sm justify-content-center">
            @foreach (range('a', 'z') as $letra)
              <li class="page-item mx-1"><a class="page-link"
                  href="{{ route('interprete.letra', $letra) }}">{{ $letra }}</a></li>
            @endforeach
          </ul>
          <hr>
        </nav>
      </div>
    </div>








    <div class="row mb-4">
      <div class="col-12">
        <h2>Últimos Intérpretes Agregados</h2>
        <p class="lead">
          Descubre los nuevos talentos y las voces emergentes del folklore argentino. Esta sección presenta los
          intérpretes más recientes añadidos a nuestra base de datos, ofreciendo una oportunidad única para conocer las
          futuras estrellas de la música folklórica. Mantente actualizado con las últimas incorporaciones a nuestro
          portal.
        </p>
        <div class="row">
          <!-- Repetir este bloque para cada intérprete -->
          @foreach ($ultimos as $ultimo)
            <div class="col-md-3 mb-4">
              <a href="{{ route('interprete.show', $ultimo->slug) }}"
                class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
                <div class="card-img-top">
                  <img src="{{ asset('storage/interpretes/' . $ultimo->foto) }}"
                    class="img-fluid w-100 h-auto object-cover" alt="{{ $ultimo->interprete }}">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $ultimo->interprete }}</h5>
                  <p class="card-text mt-auto">{{ number_format($ultimo->visitas, 0, '', ',') }} visitas</p>
                </div>
              </a>
            </div>
          @endforeach
          <!-- Fin del bloque -->
        </div>
      </div>
    </div>


    {{-- <div class="container mt-5">
      <h1>Sugerir un Intérprete</h1>
      <p class="lead">
        ¿Conoces a un talento del folklore argentino que debería estar en nuestro portal? ¡Queremos escucharte! Como
        usuario registrado, tienes la oportunidad de sugerir intérpretes y ayudar a que más personas descubran la riqueza
        de nuestra música folklórica. Al unirte a nuestra comunidad, podrás contribuir activamente, recibir
        actualizaciones exclusivas y formar parte de un espacio que celebra nuestras tradiciones.
      </p>
      <p>
        Los beneficios de pertenecer a nuestra comunidad incluyen:
      <ul>
        <li>Contribuir al enriquecimiento del contenido del portal.</li>
        <li>Acceso a noticias y eventos exclusivos.</li>
        <li>Participación en sorteos y promociones.</li>
        <li>Conectar con otros amantes del folklore argentino.</li>
      </ul>
      </p>
    </div>

    <div class="container mt-4">
      <h2>Formulario de Sugerencia de Intérprete</h2>
      <form action="/submit-interpreter-suggestion" method="post" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="name" class="form-label">Nombre del Intérprete</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="biography" class="form-label">Biografía</label>
          <textarea class="form-control" id="biography" name="biography" rows="5" required></textarea>
        </div>
        <div class="mb-3">
          <label for="photo" class="form-label">Foto del Intérprete</label>
          <input type="file" class="form-control" id="photo" name="photo" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar Sugerencia</button>
      </form>
    </div> --}}

    {{--   
    <!-- Listado de interpretes en cards -->
   <div class="d-flex flex-wrap justify-content-center">

      @foreach ($interpretes as $interprete)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 p-4 mb-8">
          <div class="card h-100 shadow-sm text-decoration-none">
            <a href="{{ route('interprete.show', $interprete->slug) }}" class="text-decoration-none">
              <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="{{ $interprete->interprete }}"
                class="card-img-top" style="height: 16rem; object-fit: cover;">
              <div class="card-body">
                <h3 class="card-title h6 font-weight-bold text-dark mb-2">{{ $interprete->interprete }}</h3>
              </div>
            </a>
          </div>
        </div>
      @endforeach 

    </div>
    --}}

  </div>

@endsection
