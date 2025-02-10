@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/magazine/owl.carousel.min.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('css/magazine/owl.theme.default.min.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('css/magazine/style.css') }}">
@endsection

@section('content')

  <section class="main-news-area">
    <div class="container">
      <div class="row">

        <div class="col-lg-8">
          <div class="single-main-news">
            <a href="{{ route('noticia.show', [$noticias[0]['categoria']['slug'], $noticias[0]['slug']]) }}">
              <img
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[0]['foto'])) && $noticias[0]['foto'] ? asset('storage/albunes/' . $noticias[0]['foto']) : asset('img/noticia.jpg') }}"
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
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[1]['foto'])) && $noticias[1]['foto'] ? asset('storage/albunes/' . $noticias[1]['foto']) : asset('img/noticia.jpg') }}"
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
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[2]['foto'])) && $noticias[2]['foto'] ? asset('storage/albunes/' . $noticias[2]['foto']) : asset('img/noticia.jpg') }}"
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
                src="{{ file_exists(public_path('storage/noticias/' . $noticias[3]['foto'])) && $noticias[3]['foto'] ? asset('storage/albunes/' . $noticias[3]['foto']) : asset('img/noticia.jpg') }}"
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
  </section>


  <section class="default-news-area">
    <div class="container">
      <div class="row">
        <div class="col-lg-8">


          <div class="most-popular-news">
            <div class="section-title">
              {{-- <h2>Lo más leído</h2> --}}

            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="single-most-popular-news">
                  <div class="popular-news-image">
                    <a href="{{ route('noticia.show', [$noticias[4]['categoria']['slug'], $noticias[4]['slug']]) }}">
                      <img src="{{ asset('storage/noticias/' . $noticias[4]['foto']) }}"
                        alt="{{ $noticias[4]['titulo'] }}">
                    </a>
                  </div>

                  <div class="popular-news-content">
                    <span>{{ $noticias[4]['categoria']['nombre'] }}</span>
                    <h3>
                      <a
                        href="{{ route('noticia.show', [$noticias[4]['categoria']['slug'], $noticias[4]['slug']]) }}">{{ $noticias[4]['titulo'] }}</a>
                    </h3>
                    <p>
                      {{ isset($noticias[4]['created_at']) ? \Carbon\Carbon::parse($noticias[4]['created_at'])->translatedFormat('d F, Y') : '' }}
                    </p>
                  </div>
                </div>

                <div class="single-most-popular-news">
                  <div class="popular-news-image">
                    <a href="{{ route('noticia.show', [$noticias[5]['categoria']['slug'], $noticias[5]['slug']]) }}">
                      <img src="{{ asset('storage/noticias/' . $noticias[5]['foto']) }}"
                        alt="{{ $noticias[5]['titulo'] }}">
                    </a>
                  </div>

                  <div class="popular-news-content">
                    <span>{{ $noticias[5]['categoria']['nombre'] }}</span>
                    <h3>
                      <a
                        href="{{ route('noticia.show', [$noticias[5]['categoria']['slug'], $noticias[5]['slug']]) }}">{{ $noticias[5]['titulo'] }}</a>
                    </h3>
                    <p>
                      {{ isset($noticias[5]['created_at']) ? \Carbon\Carbon::parse($noticias[5]['created_at'])->translatedFormat('d F, Y') : '' }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="{{ route('noticia.show', [$noticias[6]['categoria']['slug'], $noticias[6]['slug']]) }}">
                          <img src="{{ asset('storage/noticias/' . $noticias[6]['foto']) }}"
                            alt="{{ $noticias[6]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>{{ $noticias[6]['categoria']['nombre'] }}</span>
                        <h3>
                          <a
                            href="{{ route('noticia.show', [$noticias[6]['categoria']['slug'], $noticias[6]['slug']]) }}">{{ $noticias[6]['titulo'] }}</a>
                        </h3>
                        <p>
                          {{ isset($noticias[6]['created_at']) ? \Carbon\Carbon::parse($noticias[6]['created_at'])->translatedFormat('d F, Y') : '' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="{{ route('noticia.show', [$noticias[7]['categoria']['slug'], $noticias[7]['slug']]) }}">
                          <img src="{{ asset('storage/noticias/' . $noticias[7]['foto']) }}"
                            alt="{{ $noticias[7]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>{{ $noticias[7]['categoria']['nombre'] }}</span>
                        <h3>
                          <a
                            href="{{ route('noticia.show', [$noticias[7]['categoria']['slug'], $noticias[7]['slug']]) }}">{{ $noticias[7]['titulo'] }}</a>
                        </h3>
                        <p>
                          {{ isset($noticias[7]['created_at']) ? \Carbon\Carbon::parse($noticias[7]['created_at'])->translatedFormat('d F, Y') : '' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="{{ route('noticia.show', [$noticias[8]['categoria']['slug'], $noticias[8]['slug']]) }}">
                          <img src="{{ asset('storage/noticias/' . $noticias[8]['foto']) }}"
                            alt="{{ $noticias[8]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>{{ $noticias[8]['categoria']['nombre'] }}</span>
                        <h3>
                          <a
                            href="{{ route('noticia.show', [$noticias[8]['categoria']['slug'], $noticias[8]['slug']]) }}">{{ $noticias[8]['titulo'] }}</a>
                        </h3>
                        <p>
                          {{ isset($noticias[8]['created_at']) ? \Carbon\Carbon::parse($noticias[8]['created_at'])->translatedFormat('d F, Y') : '' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="{{ route('noticia.show', [$noticias[9]['categoria']['slug'], $noticias[9]['slug']]) }}">
                          <img src="{{ asset('storage/noticias/' . $noticias[9]['foto']) }}"
                            alt="{{ $noticias[9]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>{{ $noticias[9]['categoria']['nombre'] }}</span>
                        <h3>
                          <a
                            href="{{ route('noticia.show', [$noticias[9]['categoria']['slug'], $noticias[9]['slug']]) }}">{{ $noticias[9]['titulo'] }}</a>
                        </h3>
                        <p>
                          {{ isset($noticias[9]['created_at']) ? \Carbon\Carbon::parse($noticias[9]['created_at'])->translatedFormat('d F, Y') : '' }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


          {{-- <div class="video-news">
            <div class="section-title">
              <h2>Top video</h2>
            </div>

            <div class="video-slides owl-carousel owl-theme owl-loaded owl-drag">





              <div class="owl-stage-outer">
                <div class="owl-stage"
                  style="transform: translate3d(-645px, 0px, 0px); transition: 0.25s; width: 2261px;">
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-2.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">The lazy man’s guide to travel you to our moms</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-3.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">Sony laptops are still part of the sony family</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-1.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">Apply these 10 secret techniques to improve travel</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-2.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">The lazy man’s guide to travel you to our moms</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-3.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">Sony laptops are still part of the sony family</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-1.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">Apply these 10 secret techniques to improve travel</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="video-item">
                      <div class="video-news-image">
                        <a href="#">
                          <img src="img/magazine/video-news-2.jpg" alt="image">
                        </a>

                        <a href="https://www.youtube.com/watch?v=UG8N5JT4QLc" class="popup-youtube">
                          <i class="bx bx-play-circle"></i>
                        </a>
                      </div>

                      <div class="video-news-content">
                        <h3>
                          <a href="#">The lazy man’s guide to travel you to our moms</a>
                        </h3>
                        <span>28 September, 2024</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="owl-nav"><button type="button" role="presentation" class="owl-prev"><i
                    class="bx bx-chevron-left"></i></button><button type="button" role="presentation"
                  class="owl-next"><i class="bx bx-chevron-right"></i></button></div>
              <div class="owl-dots disabled"></div>
            </div>
          </div> --}}


          {{-- <div class="politics-news">
            <div class="section-title">
              <h2>Festivales</h2>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="single-politics-news">
                  <div class="politics-news-image">
                    <a href="#">
                      <img src="img/magazine/politics-news-1.jpg" alt="image">
                    </a>
                  </div>

                  <div class="politics-news-content">
                    <span>Politics</span>
                    <h3>
                      <a href="#">Organizing conference among our selves to make it better financially</a>
                    </h3>
                    <p><a href="#">Jonson Steven</a> / 28 September, 2024</p>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="politics-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="politics-news-image">
                        <a href="#">
                          <img src="img/magazine/politics-news-2.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="politics-news-content">
                        <h3>
                          <a href="#">Politically, new riots have started inside the country</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="politics-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="politics-news-image">
                        <a href="#">
                          <img src="img/magazine/politics-news-3.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="politics-news-content">
                        <h3>
                          <a href="#">Public discussion in 5 major issues</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="politics-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="politics-news-image">
                        <a href="#">
                          <img src="img/magazine/politics-news-4.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="politics-news-content">
                        <h3>
                          <a href="#">Preparations are being made in a new way for the elections</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> --}}

          {{-- <div class="business-news">
            <div class="section-title">
              <h2>Business</h2>
            </div>

            <div class="business-news-slides owl-carousel owl-theme owl-loaded owl-drag">







              <div class="owl-stage-outer">
                <div class="owl-stage"
                  style="transform: translate3d(-968px, 0px, 0px); transition: 0.25s; width: 2584px;">
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>Business</span>
                        <h3>
                          <a href="#">We have to make a business plan while maintaining mental heatlh during this
                            epidemic</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>News</span>
                        <h3>
                          <a href="#">Many people are established today by doing ecommerce business during the time
                            of Corona</a>
                        </h3>
                        <p><a href="#">Sanford</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>Business</span>
                        <h3>
                          <a href="#">We have to make a business plan while maintaining mental heatlh during this
                            epidemic</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>News</span>
                        <h3>
                          <a href="#">Many people are established today by doing ecommerce business during the time
                            of Corona</a>
                        </h3>
                        <p><a href="#">Sanford</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>Business</span>
                        <h3>
                          <a href="#">We have to make a business plan while maintaining mental heatlh during this
                            epidemic</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>News</span>
                        <h3>
                          <a href="#">Many people are established today by doing ecommerce business during the time
                            of Corona</a>
                        </h3>
                        <p><a href="#">Sanford</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>Business</span>
                        <h3>
                          <a href="#">We have to make a business plan while maintaining mental heatlh during this
                            epidemic</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-business-news">
                      <div class="business-news-image">
                        <a href="#">
                          <img src="img/magazine/business-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="business-news-content">
                        <span>News</span>
                        <h3>
                          <a href="#">Many people are established today by doing ecommerce business during the time
                            of Corona</a>
                        </h3>
                        <p><a href="#">Sanford</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="owl-nav"><button type="button" role="presentation" class="owl-prev"><i
                    class="bx bx-chevron-left"></i></button><button type="button" role="presentation"
                  class="owl-next"><i class="bx bx-chevron-right"></i></button></div>
              <div class="owl-dots disabled"></div>
            </div>
          </div> --}}

          {{-- <div class="culture-news">
            <div class="section-title">
              <h2>Cartelera folklórica</h2>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="single-culture-news">
                  <div class="culture-news-image">
                    <a href="#">
                      <img src="img/magazine/culture-news-1.jpg" alt="image">
                    </a>
                  </div>

                  <div class="culture-news-content">
                    <span>Culture</span>
                    <h3>
                      <a href="#">Entertainment activists started again a few months later</a>
                    </h3>
                    <p><a href="#">Steven</a> / 28 September, 2024</p>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="culture-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="culture-news-image">
                        <a href="#">
                          <img src="img/magazine/culture-news-2.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="culture-news-content">
                        <h3>
                          <a href="#">Working in the garden is a tradition for women</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="culture-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="culture-news-image">
                        <a href="#">
                          <img src="img/magazine/culture-news-3.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="culture-news-content">
                        <h3>
                          <a href="#">The fashion that captures the lives of women</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="culture-news-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="culture-news-image">
                        <a href="#">
                          <img src="img/magazine/culture-news-4.jpg" alt="image">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="culture-news-content">
                        <h3>
                          <a href="#">A group of artists performed music in a group way</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> --}}

          {{-- <div class="health-news">
            <div class="section-title">
              <h2>Fiestas y festivales tradicionales</h2>
            </div>

            <div class="health-news-slides owl-carousel owl-theme owl-loaded owl-drag">







              <div class="owl-stage-outer">
                <div class="owl-stage"
                  style="transform: translate3d(-1291px, 0px, 0px); transition: 0.25s; width: 2584px;">
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Health</span>
                        <h3>
                          <a href="#">At present, diseases have become the main obstacle for children to get out
                            healthy</a>
                        </h3>
                        <p><a href="#">Tikelo</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Fitness</span>
                        <h3>
                          <a href="#">Morning yoga is very important for maintaining good physical fitness</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Health</span>
                        <h3>
                          <a href="#">At present, diseases have become the main obstacle for children to get out
                            healthy</a>
                        </h3>
                        <p><a href="#">Tikelo</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Fitness</span>
                        <h3>
                          <a href="#">Morning yoga is very important for maintaining good physical fitness</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Health</span>
                        <h3>
                          <a href="#">At present, diseases have become the main obstacle for children to get out
                            healthy</a>
                        </h3>
                        <p><a href="#">Tikelo</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item active" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Fitness</span>
                        <h3>
                          <a href="#">Morning yoga is very important for maintaining good physical fitness</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-1.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Health</span>
                        <h3>
                          <a href="#">At present, diseases have become the main obstacle for children to get out
                            healthy</a>
                        </h3>
                        <p><a href="#">Tikelo</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                  <div class="owl-item cloned" style="width: 292.992px; margin-right: 30px;">
                    <div class="single-health-news">
                      <div class="health-news-image">
                        <a href="#">
                          <img src="img/magazine/health-news-2.jpg" alt="image">
                        </a>
                      </div>

                      <div class="health-news-content">
                        <span>Fitness</span>
                        <h3>
                          <a href="#">Morning yoga is very important for maintaining good physical fitness</a>
                        </h3>
                        <p><a href="#">Patricia</a> / 28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="owl-nav"><button type="button" role="presentation" class="owl-prev"><i
                    class="bx bx-chevron-left"></i></button><button type="button" role="presentation"
                  class="owl-next"><i class="bx bx-chevron-right"></i></button></div>
              <div class="owl-dots disabled"></div>
            </div>
          </div> --}}


          {{-- <div class="row">
            <div class="col-lg-6">
              <div class="section-title">
                <h2>Sports</h2>
              </div>

              <div class="single-sports-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="sports-news-image">
                      <a href="#">
                        <img src="img/magazine/sports-news-1.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="sports-news-content">
                      <h3>
                        <a href="#">Start a new men’s road World Championships</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="single-sports-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="sports-news-image">
                      <a href="#">
                        <img src="img/magazine/sports-news-2.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="sports-news-content">
                      <h3>
                        <a href="#">He look the first wicket with the first ball in this match.</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="single-sports-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="sports-news-image">
                      <a href="#">
                        <img src="img/magazine/sports-news-3.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="sports-news-content">
                      <h3>
                        <a href="#">The last time of the match is goning on</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="section-title">
                <h2>Tech</h2>
              </div>

              <div class="single-tech-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="tech-news-image">
                      <a href="#">
                        <img src="img/magazine/tech-news-1.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="tech-news-content">
                      <h3>
                        <a href="#">5 more phones have come to the market with features.</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="single-tech-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="tech-news-image">
                      <a href="#">
                        <img src="img/magazine/tech-news-2.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="tech-news-content">
                      <h3>
                        <a href="#">Like humans, the new robot has a lot of memory power</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="single-tech-news">
                <div class="row align-items-center">
                  <div class="col-lg-4 col-sm-4">
                    <div class="tech-news-image">
                      <a href="#">
                        <img src="img/magazine/tech-news-3.jpg" alt="image">
                      </a>
                    </div>
                  </div>

                  <div class="col-lg-8 col-sm-8">
                    <div class="tech-news-content">
                      <h3>
                        <a href="#">All new gadgets are being made in technology</a>
                      </h3>
                      <p>28 September, 2024</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> --}}

        </div>

        {{-- Seccion de ultimos discos --}}
        <div class="col-lg-4">
          <aside class="widget-area">

            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Ultimos albunes</h3>

              @foreach ($discos as $disco)
                <article class="item">
                  <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                    class="thumb">
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

            {{-- <section class="widget widget_featured_reports">
              <h3 class="widget-title">Featured reports</h3>

              <div class="single-featured-reports">
                <div class="featured-reports-image">
                  <a href="#">
                    <img src="img/magazine/featured-reports-1.jpg" alt="image">
                  </a>

                  <div class="featured-reports-content">
                    <h3>
                      <a href="#">All the highlights from western fashion week summer 2024</a>
                    </h3>
                    <p><a href="#">Patricia</a> / 28 September, 2024</p>
                  </div>
                </div>
              </div>
            </section> --}}


            <section class="widget widget_tag_cloud">
              <h3 class="widget-title">Categorías</h3>

              <div class="tagcloud">

                @foreach ($categorias as $cat)
                  <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}">{{ $cat->nombre }}</a>
                @endforeach

              </div>
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
                    101,545 Subscribers
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
                <h3>Subscribe to our newsletter</h3>
                <p>Subscribe to our newsletter to get the new updates!</p>
              </div>

              <form class="newsletter-form" data-toggle="validator" novalidate="true">
                <input type="email" class="input-newsletter" placeholder="Enter your email" name="EMAIL"
                  required="" autocomplete="off">

                <button type="submit" class="disabled"
                  style="pointer-events: all; cursor: pointer;">Subscribe</button>
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
