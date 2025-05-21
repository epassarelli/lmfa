@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">
@endsection

@section('content')

  {{-- <section class="main-news-area">
    <div class="container">
      <div class="row">

        <div class="col-lg-8">
          <div class="single-main-news">
            <a href="{{ route('noticia.show', [$noticias[0]['categoria']['slug'], $noticias[0]['slug']]) }}">
              <img
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[0]['foto'])) && $noticias[0]['foto'] ? asset('storage/noticias/' . $noticias[0]['foto']) : asset('img/noticia.jpg') }}"
                alt="{{ $noticias[0]['titulo'] }}">

            </a>
            <div class="news-content">
              <div class="tag">{{ $noticias[0]['categoria']['nombre'] }}</div>
              <h3>
                <a
                  href="{{ route('noticia.show', [$noticias[0]['categoria']['slug'], $noticias[0]['slug']]) }}">{{ $noticias[0]['titulo'] }}</a>
              </h3>
              <span>{{ isset($noticias[0]['created_at']) ? \Carbon\Carbon::parse($noticias[0]['created_at'])->translatedFormat('d F, Y') : '' }}
              </span>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="single-main-news-inner">
            <a href="{{ route('noticia.show', [$noticias[1]['categoria']['slug'], $noticias[1]['slug']]) }}">
              <img
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[1]['foto'])) && $noticias[1]['foto'] ? asset('storage/noticias/' . $noticias[1]['foto']) : asset('img/noticia.jpg') }}"
                alt="{{ $noticias[1]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">{{ $noticias[1]['categoria']['nombre'] }}</div>
              <h3>
                <a
                  href="{{ route('noticia.show', [$noticias[1]['categoria']['slug'], $noticias[1]['slug']]) }}">{{ $noticias[1]['titulo'] }}</a>
              </h3>
              <span>{{ isset($noticias[1]['created_at']) ? \Carbon\Carbon::parse($noticias[1]['created_at'])->translatedFormat('d F, Y') : '' }}
              </span>
            </div>
          </div>

          <div class="single-main-news-box">
            <a href="{{ route('noticia.show', [$noticias[2]['categoria']['slug'], $noticias[2]['slug']]) }}">
              <img
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[2]['foto'])) && $noticias[2]['foto'] ? asset('storage/noticias/' . $noticias[2]['foto']) : asset('img/noticia.jpg') }}"
                alt="{{ $noticias[2]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">{{ $noticias[2]['categoria']['nombre'] }}</div>
              <h3>
                <a
                  href="{{ route('noticia.show', [$noticias[2]['categoria']['slug'], $noticias[2]['slug']]) }}">{{ $noticias[2]['titulo'] }}</a>
              </h3>
              <span>{{ isset($noticias[2]['created_at']) ? \Carbon\Carbon::parse($noticias[2]['created_at'])->translatedFormat('d F, Y') : '' }}
              </span>
            </div>
          </div>

          <div class="single-main-news-box">
            <a href="{{ route('noticia.show', [$noticias[3]['categoria']['slug'], $noticias[3]['slug']]) }}">
              <img
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[3]['foto'])) && $noticias[3]['foto'] ? asset('storage/noticias/' . $noticias[3]['foto']) : asset('img/noticia.jpg') }}"
                alt="{{ $noticias[3]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">{{ $noticias[3]['categoria']['nombre'] }}</div>
              <h3>
                <a
                  href="{{ route('noticia.show', [$noticias[3]['categoria']['slug'], $noticias[3]['slug']]) }}">{{ $noticias[3]['titulo'] }}</a>
              </h3>
              <span>{{ isset($noticias[3]['created_at']) ? \Carbon\Carbon::parse($noticias[3]['created_at'])->translatedFormat('d F, Y') : '' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section> --}}



  <section class="default-news-area">
    <div class="container">

      <div class="row">

        {{-- Seccion de bloques de noticias --}}
        <div class="col-lg-8">

          @php
            $bloques = [
                'Ultimas noticias de folklore argentino' => $ultimasNoticias,
                // 'Actualidad' => $actualidad,
                // 'Festivales' => $festivales,
                // 'Lanzamientos' => $lanzamientos,
                // 'Cartelera' => $cartelera,
            ];
          @endphp

          @foreach ($bloques as $titulo => $noticias)
            <div class="most-popular-news">
              <div class="section-title">
                <h2>{{ $titulo }}</h2>
              </div>

              <div class="row">
                @foreach ($noticias as $noticia)
                  <div class="col-md-6 mb-4">
                    <x-noticia-card :noticia="$noticia" />
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach

          <h1>Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y Costumbres</h1>
          <P>Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de Argentina. Descubre música,
            danzas y más. ¡Visítanos hoy!</P>
        </div>

        {{-- Seccion de ultimos discos --}}
        <div class="col-lg-4">

          <aside class="widget-area">


            <section class="widget widget_tag_cloud">
              <h3 class="widget-title">Categorías</h3>

              <div class="tagcloud">

                @foreach ($categorias as $cat)
                  <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}">{{ $cat->nombre }}</a>
                @endforeach

              </div>
            </section>



            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Ultimos albunes</h3>

              @foreach ($discos as $disco)
                <article class="item">
                  <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}" class="thumb">
                    <img
                      src="{{ file_exists(public_path('storage/albunes/' . $disco->foto)) && $disco->foto ? asset('storage/albunes/' . $disco->foto) : asset('img/album.jpg') }}"
                      alt="{{ $disco->album }}">
                  </a>
                  <div class="info">
                    <h4 class="title usmall"><a
                        href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}">{{ $disco->album }}</a>
                    </h4>
                    <span>{{ $disco->interprete->interprete }}</span>
                    <p class="card-text mt-auto">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </article>
              @endforeach

            </section>


            {{-- <section class="widget widget_stay_connected">
              <h3 class="widget-title">Stay connected</h3>

              <ul class="stay-connected-list">
                <li>
                  <a href="#">
                    <i class="bx bxl-facebook"></i>
                    120,345 Fans
                  </a>
                </li>

                <li>
                  <a href="#" class="twitter">
                    <i class="bx bxl-twitter"></i>
                    25,321 Followers
                  </a>
                </li>

                <li>
                  <a href="#" class="linkedin">
                    <i class="bx bxl-linkedin"></i>
                    7,519 Connect
                  </a>
                </li>

                <li>
                  <a href="#" class="youtube">
                    <i class="bx bxl-youtube"></i>
                    1,545 Subscribers
                  </a>
                </li>

                <li>
                  <a href="#" class="instagram">
                    <i class="bx bxl-instagram"></i>
                    10,129 Followers
                  </a>
                </li>

                <li>
                  <a href="#" class="wifi">
                    <i class="bx bx-wifi"></i>
                    952 Subscribers
                  </a>
                </li>
              </ul>
            </section> --}}


            {{-- <section class="widget widget_newsletter">
              <div class="newsletter-content">
                <h3>Apuntate a nuestro boletín</h3>
                <p>Suscribete a nuestro boletín para estar informado con lo que pase en nuestro Folklore Argentino !</p>
              </div>

              <form class="newsletter-form" data-toggle="validator" novalidate="true">
                <input type="email" class="input-newsletter" placeholder="Ingresa tu correo" name="EMAIL"
                  required="" autocomplete="off">

                <button type="submit" class="disabled"
                  style="pointer-events: all; cursor: pointer;">Subscribirme</button>
                <div id="validator-newsletter" class="form-result"></div>
              </form>
            </section> --}}


            <section class="widget widget_popular_posts_thumb">
              <h3 class="widget-title">Últimas biografías</h3>

              @foreach ($interpretes as $artista)
                <article class="item">
                  <a href="{{ route('interprete.show', $artista->slug) }}" class="thumb">
                    <img
                      src="{{ file_exists(public_path('storage/interpretes/' . $artista->foto)) && $artista->foto ? asset('storage/interpretes/' . $artista->foto) : asset('img/interprete.jpg') }}"
                      alt="{{ $artista->interprete }}">

                  </a>
                  <div class="info">
                    <h4 class="title usmall"><a
                        href="{{ route('interprete.show', $artista->slug) }}">{{ $artista->interprete }}</a>
                    </h4>
                    <span>{{ number_format($artista->visitas, 0, '', ',') }} visitas</span>
                  </div>
                </article>
              @endforeach

            </section>


            {{-- <section class="widget widget_most_shared">
              <h3 class="widget-title">Most shared</h3>

              <div class="single-most-shared">
                <div class="most-shared-image">
                  <a href="#">
                    <img src="img/magazine/most-shared-1.jpg" alt="image">
                  </a>

                  <div class="most-shared-content">
                    <h3>
                      <a href="#">All the highlights from western fashion week summer 2024</a>
                    </h3>
                    <p><a href="#">Patricia</a> / 28 September, 2024</p>
                  </div>
                </div>
              </div>
            </section> --}}


            {{-- <section class="widget widget_instagram">
              <h3 class="widget-title">Instagram</h3>

              <ul>
                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-1.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>

                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-2.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>

                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-3.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>

                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-4.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>

                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-5.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>

                <li>
                  <div class="box">
                    <img src="img/magazine/latest-news-6.jpg" alt="image">
                    <i class="bx bxl-instagram"></i>
                    <a href="#" target="_blank" class="link-btn"></a>
                  </div>
                </li>
              </ul>
            </section> --}}

          </aside>
        </div>

      </div>
    </div>
  </section>


@endsection


@section('scripts')
  <script src="{{ asset('js/magazine/jquery.min.js') }}"></script>
  <script src="{{ asset('js/magazine/owl.carousel.min.js') }}"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      $(".owl-carousel").owlCarousel({
        loop: true,
        margin: 10,
        nav: true,
        responsive: {
          0: {
            items: 1
          },
          578: {
            items: 2
          },
          768: {
            items: 2
          }
        }
      });
    });
  </script>
@endsection
