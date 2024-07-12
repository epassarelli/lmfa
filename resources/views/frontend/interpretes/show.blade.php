@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">
        <h1>Biografía de {{ $interprete->interprete }}</h1>



        <div class="lead">
          {!! $interprete->biografia !!}
        </div>


        {{-- <div class="p-4">
          <span>Redes sociales del artista</span>
          <a href="#" class="text-decoration-none me-3"><i class="fab fa-facebook fa-2x"></i></a>
          <a href="#" class="text-decoration-none me-3"><i class="fab fa-twitter fa-2x"></i></a>
          <a href="#" class="text-decoration-none me-3"><i class="fab fa-instagram fa-2x"></i></a>
          <a href="#" class="text-decoration-none"><i class="fab fa-youtube fa-2x"></i></a>
        </div> --}}




        {{-- <h2>Discografía</h2>
        <ul class="list-group mb-4">
          <li class="list-group-item">La Negra (1967)</li>
          <li class="list-group-item">Mercedes Sosa en Argentina (1982)</li>
          <li class="list-group-item">Cantora 1 y 2 (2009)</li>
        </ul>

        <h2>Letras de Canciones</h2>
        <ul class="list-group mb-4">
          <li class="list-group-item">Gracias a la Vida</li>
          <li class="list-group-item">Alfonsina y el Mar</li>
          <li class="list-group-item">Solo le Pido a Dios</li>
        </ul>

        <h2>Galería de Imágenes</h2>
        <div id="imageGallery" class="carousel slide mb-4" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="...">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#imageGallery" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#imageGallery" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>

        <h2>Videos Musicales</h2>
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="ratio ratio-16x9">
              <iframe src="https://www.youtube.com/embed/yourvideoid1" title="YouTube video" allowfullscreen></iframe>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ratio ratio-16x9">
              <iframe src="https://www.youtube.com/embed/yourvideoid2" title="YouTube video" allowfullscreen></iframe>
            </div>
          </div>
          <div class="col-md-4">
            <div class="ratio ratio-16x9">
              <iframe src="https://www.youtube.com/embed/yourvideoid3" title="YouTube video" allowfullscreen></iframe>
            </div>
          </div>
        </div>

        <h2>Próximos Shows</h2>
        <p>Información no disponible (artista fallecido).</p>

        <h2>Noticias Relacionadas</h2>
        <ul class="list-group mb-4">
          <li class="list-group-item">Homenaje a Mercedes Sosa en el aniversario de su fallecimiento.</li>
        </ul>

        <h2>Comentarios y Reseñas</h2>
        <div class="mb-4">
          <div class="card mb-2">
            <div class="card-body">
              <p class="card-text">"Una artista incomparable, su voz y su legado son eternos."</p>
              <footer class="blockquote-footer">Usuario 1</footer>
            </div>
          </div>
          <div class="card mb-2">
            <div class="card-body">
              <p class="card-text">"Mercedes Sosa es una leyenda de la música argentina. Su música siempre será
                recordada."
              </p>
              <footer class="blockquote-footer">Usuario 2</footer>
            </div>
          </div>
        </div>

        <h2>Preguntas Frecuentes</h2>
        <div class="accordion mb-4" id="faqAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq1">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1"
                aria-expanded="true" aria-controls="collapse1">
                ¿Cuál fue su primer álbum?
              </button>
            </h2>
            <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                "Canciones con Fundamento" (1959).
              </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq2">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                ¿Cuándo falleció Mercedes Sosa?
              </button>
            </h2>
            <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2"
              data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                El 4 de octubre de 2009.
              </div>
            </div>
          </div>
        </div> --}}


        @include('layouts.partials.select-interprete')


      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>


    {{-- <div class="container">
    <div class="row">

      <div class="col-12 col-md-2">
        <!-- Contenido secundario o barra lateral -->
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>


      <div class="col-12 col-md-10">
        <!-- Contenido principal -->
        <h1>Contenido Principal</h1>
        <p>Esta es la columna principal que ocupa el espacio restante.</p>


        <div class="cmax-w-xl mx-auto py-4">
          <div class="d-flex align-items-center mb-4">
            <img class="rounded-circle me-4" src="{{ $interprete->foto }}" alt="{{ $interprete->nombre }}"
              style="width: 6rem; height: 6rem;">
            <h1 class="h3 fw-bold">{{ $interprete->interprete }}</h1>
          </div>

          <div class="mb-4">
            <p class="fs-5">{!! $interprete->biografia !!}</p>
          </div>

          <div class="mb-4">
            <h2 class="h5 fw-bold mb-2">Contacto</h2>
            <p>{{ $interprete->correo }}</p>
            <p>{{ $interprete->telefono }}</p>
            <p>{{ $interprete->direccion }}</p>
          </div>

          <div class="mb-4">
            <h2 class="h5 fw-bold mb-2">Redes sociales</h2>
            <ul class="list-inline">
              <li class="list-inline-item">
                <a href="{{ $interprete->facebook }}" target="_blank" class="text-decoration-none">
                  <i class="fab fa-facebook fa-lg"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a href="{{ $interprete->twitter }}" target="_blank" class="text-decoration-none">
                  <i class="fab fa-twitter fa-lg"></i>
                </a>
              </li>
              <li class="list-inline-item">
                <a href="{{ $interprete->instagram }}" target="_blank" class="text-decoration-none">
                  <i class="fab fa-instagram fa-lg"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>

      </div>

    </div>
  </div> --}}



  </div>

@endsection
