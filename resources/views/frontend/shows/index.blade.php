@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">

@endsection

@section('content')

  <!-- Start Page Banner -->
  {{-- <div class="page-title-area">
    <div class="container">
      <div class="page-title-content">
        <h2>Cartelera</h2>
        <ul>
          <li><a href="https://templates.envytheme.com/depan/default/index.html">Home</a></li>
          <li>Cartelera</li>
        </ul>
      </div>
    </div>
  </div> --}}
  <!-- End Page Banner -->

  <section class="default-news-area">
    <div class="container">

      <div class="row">

        {{-- Seccion de bloques de noticias --}}
        <div class="col-lg-8">

          {{-- <div class="row">
            <form class="row mb-4">
              
              <div class="col-12 col-md-3">
                
                <select class="form-select" id="artista">
                  <option selected disabled>Artista</option>
                  <option>Jorge Rojas</option>
                  <option>Los Nocheros</option>
                  <option>Abel Pintos</option>
                  <option>Soledad Pastorutti</option>
                  <option>Los Tekis</option>
                  <option>El Chaqueño Palavecino</option>
                  <option>Raly Barrionuevo</option>
                  
                </select>
              </div>

              <div class="col-12 col-md-3">
                <select class="form-select" id="provincia">
                  <option selected disabled>Provincia</option>
                  <option>Buenos Aires</option>
                  <option>Córdoba</option>
                  <option>Salta</option>
                  <option>Jujuy</option>
                  <option>Mendoza</option>
                  <option>Santiago del Estero</option>
                </select>
              </div>

              <div class="col-12 col-md-3">
                <select class="form-select" id="mes">
                  <option selected disabled>Meses</option>
                  <option>Enero</option>
                  <option>Febrero</option>
                  <option>Marzo</option>
                  <option>Abril</option>
                  <option>Mayo</option>
                  <option>Junio</option>
                  <option>Julio</option>
                  <option>Agosto</option>
                  <option>Septiembre</option>
                  <option>Octubre</option>
                  <option>Noviembre</option>
                  <option>Diciembre</option>
                </select>
              </div>

              <div class="col-12 col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-search me-2"></i> Buscar
                </button>
              </div>
            </form>
          </div> --}}


          <div class="row">
            @if ($shows->isEmpty())
              <div class="warning"></div>
              <div class="alert alert-warning" role="alert">
                No hay shows disponibles aún.
              </div>
            @else
              @foreach ($shows as $evento)
                <div class="col-md-6 mb-4">
                  <div class="card text-white overflow-hidden"
                    style="height: 500px; position: relative; background-image: url('{{ asset('storage/interpretes/' . $evento->interprete->foto) }}'); background-size: cover; background-position: center;">

                    <!-- Superposición para oscurecer un poco si querés mejor contraste -->
                    <div class="position-absolute top-0 start-0 w-100 h-40"
                      style="background: rgba(0, 0, 0, 0.5); z-index: 1;"></div>

                    <!-- Contenido del evento -->
                    <div class="card-body position-relative" style="z-index: 2;">
                      <h3 class="card-title mb-1">{{ $evento->show }}</h3>
                      <p class="card-text mb-4">{{ date('d', strtotime($evento->fecha)) }} /
                        {{ date('M', strtotime($evento->fecha)) }}</p>
                    </div>

                    <!-- Botón fijo abajo { route('shows.show', $evento->slug) } -->
                    <div class="position-absolute bottom-0 start-0 w-100 text-center p-2" style="z-index: 2;">
                      <a href="#" class="btn btn-warning w-100 rounded-0 fw-bold">
                        Más información
                      </a>
                    </div>
                  </div>
                </div>
              @endforeach

            @endif
          </div>


        </div>

        <div class="col-lg-4">

          <aside class="widget-area">

            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Eventos destacados</h3>
            </section>

            {{-- <section class="widget widget_stay_connected">
              <h3 class="widget-title">Stay connected</h3>

              <ul class="stay-connected-list">
                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#">
                    <i class="bx bxl-facebook"></i>
                    120,345 Fans
                  </a>
                </li>

                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#" class="twitter">
                    <i class="bx bxl-twitter"></i>
                    25,321 Followers
                  </a>
                </li>

                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#" class="linkedin">
                    <i class="bx bxl-linkedin"></i>
                    7,519 Connect
                  </a>
                </li>

                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#" class="youtube">
                    <i class="bx bxl-youtube"></i>
                    101,545 Subscribers
                  </a>
                </li>

                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#" class="instagram">
                    <i class="bx bxl-instagram"></i>
                    10,129 Followers
                  </a>
                </li>

                <li>
                  <a href="https://templates.envytheme.com/depan/default/contact.html#" class="wifi">
                    <i class="bx bx-wifi"></i>
                    952 Subscribers
                  </a>
                </li>
              </ul>
            </section> --}}

            {{-- <section class="widget widget_newsletter">
              <div class="newsletter-content">
                <h3>Subscribe to our newsletter</h3>
                <p>Subscribe to our newsletter to get the new updates!</p>
              </div>

              <form class="newsletter-form" data-toggle="validator" novalidate="true">
                <input type="email" class="input-newsletter" placeholder="Enter your email" name="EMAIL"
                  required="" autocomplete="off">

                <button type="submit" class="disabled" style="pointer-events: all; cursor: pointer;">Subscribe</button>
                <div id="validator-newsletter" class="form-result"></div>
              </form>
            </section> --}}

          </aside>
        </div>
      </div>


      {{-- <div class="row">
        <p class="lead">Explora nuestra sección de shows y eventos del folklore argentino para mantenerte al tanto de
          las
          presentaciones en
          vivo y festivales que celebran la música folklórica argentina. Aquí encontrarás información detallada sobre los
          próximos conciertos, festivales y eventos especiales donde podrás disfrutar de la música de tus artistas y
          cantantes
          favoritos.</p>
        <p class="lead">Obtén detalles sobre fechas, ubicaciones y horarios de los eventos más importantes, así como
          información sobre la
          compra de entradas y recomendaciones para disfrutar al máximo de cada espectáculo. Ya sea un concierto íntimo en
          una
          peña local o un gran festival folklórico, nuestra sección te mantendrá informado sobre todas las oportunidades
          para
          vivir la música folklórica argentina en vivo.</p>
        <p class="lead">No te pierdas ninguna ocasión para celebrar y disfrutar del folklore argentino. Nuestra sección
          de
          shows y eventos
          te conecta con las experiencias en vivo más emocionantes, permitiéndote formar parte de la rica tradición
          musical
          de
          Argentina. Desde festivales anuales hasta presentaciones exclusivas, te ofrecemos una guía completa para que no
          te
          falte nada.</p>
      </div> --}}

    </div>

  @endsection
