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
            <a href="#">
              {{-- <img src="img/magazine/main-news-1.jpg" alt="image"> --}}
              <img src="{{ asset('storage/noticias/' . $noticias[0]['foto']) }}" alt="{{ $noticias[0]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">Actualidad</div>
              <h3>
                <a href="#">{{ $noticias[0]['titulo'] }}</a>
              </h3>
              <span><a href="">Walters</a> / 28 September, 2024</span>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="single-main-news-inner">
            <a href="#">
              <img src="{{ asset('storage/noticias/' . $noticias[1]['foto']) }}" alt="{{ $noticias[1]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">Business</div>
              <h3>
                <a href="#">{{ $noticias[1]['titulo'] }}</a>
              </h3>
              <span>28 September, 2024</span>
            </div>
          </div>

          <div class="single-main-news-box">
            <a href="#">
              <img src="{{ asset('storage/noticias/' . $noticias[2]['foto']) }}" alt="{{ $noticias[2]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">Sport</div>
              <h3>
                <a href="#">{{ $noticias[3]['titulo'] }}</a>
              </h3>
              <span>28 September, 2024</span>
            </div>
          </div>

          <div class="single-main-news-box">
            <a href="#">
              <img src="{{ asset('storage/noticias/' . $noticias[3]['foto']) }}" alt="{{ $noticias[3]['titulo'] }}">
            </a>
            <div class="news-content">
              <div class="tag">Home news</div>
              <h3>
                <a href="#">{{ $noticias[0]['titulo'] }}</a>
              </h3>
              <span>28 September, 2024</span>
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
              <h2>Most popular</h2>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="single-most-popular-news">
                  <div class="popular-news-image">
                    <a href="#">
                      <img src="{{ asset('storage/noticias/' . $noticias[4]['foto']) }}"
                        alt="{{ $noticias[4]['titulo'] }}">
                    </a>
                  </div>

                  <div class="popular-news-content">
                    <span>Politics</span>
                    <h3>
                      <a href="#">{{ $noticias[4]['titulo'] }}</a>
                    </h3>
                    <p><a href="#">Patricia</a> / 28 September, 2024</p>
                  </div>
                </div>

                <div class="single-most-popular-news">
                  <div class="popular-news-image">
                    <a href="#">
                      <img src="{{ asset('storage/noticias/' . $noticias[5]['foto']) }}"
                        alt="{{ $noticias[5]['titulo'] }}">
                    </a>
                  </div>

                  <div class="popular-news-content">
                    <span>Premer league</span>
                    <h3>
                      <a href="#">Manchester United’s dream of winning by a goal was fulfilled</a>
                    </h3>
                    <p><a href="#">Gonzalez</a> / 28 September, 2024</p>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="#">
                          <img src="{{ asset('storage/noticias/' . $noticias[6]['foto']) }}"
                            alt="{{ $noticias[6]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>Culture</span>
                        <h3>
                          <a href="#">As well as stopping goals, Christiane Endler is opening.</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="#">
                          <img src="{{ asset('storage/noticias/' . $noticias[7]['foto']) }}"
                            alt="{{ $noticias[7]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>Technology</span>
                        <h3>
                          <a href="#">The majority of news published online presents more videos</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="#">
                          <img src="{{ asset('storage/noticias/' . $noticias[8]['foto']) }}"
                            alt="{{ $noticias[8]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>Business</span>
                        <h3>
                          <a href="#">This movement aims to establish women’s rights.</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="most-popular-post">
                  <div class="row align-items-center">
                    <div class="col-lg-4 col-sm-4">
                      <div class="post-image">
                        <a href="#">
                          <img src="{{ asset('storage/noticias/' . $noticias[9]['foto']) }}"
                            alt="{{ $noticias[9]['titulo'] }}">
                        </a>
                      </div>
                    </div>

                    <div class="col-lg-8 col-sm-8">
                      <div class="post-content">
                        <span>Politics</span>
                        <h3>
                          <a href="#">Trump discusses various issues with his party’s political leaders.</a>
                        </h3>
                        <p>28 September, 2024</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="video-news">
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
          </div>

          <div class="politics-news">
            <div class="section-title">
              <h2>Politics</h2>
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
          </div>

          <div class="business-news">
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
          </div>

          <div class="row">
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
          </div>

          <div class="culture-news">
            <div class="section-title">
              <h2>Culture</h2>
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
          </div>

          <div class="health-news">
            <div class="section-title">
              <h2>Health</h2>
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
          </div>
        </div>

        <div class="col-lg-4">
          <aside class="widget-area">
            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Latest news</h3>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg1" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">Negotiations on a peace agreement between the two
                      countries</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg2" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">Love songs helped me through heartbreak</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg3" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">This movement aims to establish women rights</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg4" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">Giving special powers to police officers to prevent
                      crime</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg5" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">Copy paste the style of your element Newspaper</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>
            </section>

            <section class="widget widget_featured_reports">
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
            </section>

            <section class="widget widget_stay_connected">
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
            </section>

            <section class="widget widget_newsletter">
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
            </section>

            <section class="widget widget_popular_posts_thumb">
              <h3 class="widget-title">Popular posts</h3>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg1" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">Match between United States and England at AGD stadium</a>
                  </h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg2" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">For the last time, he addressed the people</a></h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg3" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">The coronavairus is finished and the outfit is busy</a>
                  </h4>
                  <span>28 September, 2024</span>
                </div>
              </article>

              <article class="item">
                <a href="#" class="thumb">
                  <span class="fullimage cover bg4" role="img"></span>
                </a>
                <div class="info">
                  <h4 class="title usmall"><a href="#">A fierce battle is going on between the two in the game</a>
                  </h4>
                  <span>28 September, 2024</span>
                </div>
              </article>
            </section>

            <section class="widget widget_most_shared">
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
            </section>

            <section class="widget widget_tag_cloud">
              <h3 class="widget-title">Tags</h3>

              <div class="tagcloud">
                <a href="#">News</a>
                <a href="#">Business</a>
                <a href="#">Health</a>
                <a href="#">Politics</a>
                <a href="#">Magazine</a>
                <a href="#">Sport</a>
                <a href="#">Tech</a>
                <a href="#">Video</a>
                <a href="#">Global</a>
                <a href="#">Culture</a>
                <a href="#">Fashion</a>
              </div>
            </section>

            <section class="widget widget_instagram">
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
            </section>
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


<!-- Noticias Destacadas -->
{{-- <section class="noticias-destacadas mb-5">
      <div id="noticiasCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">

          @foreach ($noticias as $index => $noticia)
            <div class="carousel-item @if ($index == 0) active @endif">
              <a href="{{ route('interprete.noticia.show', [$noticia->interprete->slug, $noticia->slug]) }}"
                class="text-decoration-none">
                <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" class="d-block w-100"
                  alt="{{ $noticia->titulo }}">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 p-3">
                  <h2 class="text-white"><b>{{ $noticia->titulo }}</b></h2>
                  <p class="text-white">{{ $noticia->resumen }}</p>
                </div>
              </a>
            </div>
          @endforeach

        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#noticiasCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#noticiasCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>

    </section> --}}

<!-- Próximos Shows y Festivales -->
{{-- <section class="shows-festivales mb-5">

      <h2 class="fs-4">Cartelera folkórica</h2>

      <div class="row">
        @foreach ($shows as $evento)
          <div class="col-md-6 mb-4">
            <div class="card h-100"
              style="background-image: url('{{ asset('storage/interpretes/' . $evento->interprete->foto) }}'); background-size: cover; background-position: center;">
              <div class="row h-100">
                <div class="col-md-4 text-center d-flex align-items-center justify-content-center"
                  style="background-color: rgba(0, 0, 0, 0.7); color: white;">
                  <div>
                    <h2 class="mb-1">{{ date('d', strtotime($evento->fecha)) }}</h2>
                    <p class="mb-1">{{ date('M', strtotime($evento->fecha)) }}</p>
                    <p>{{ date('Y', strtotime($evento->fecha)) }}</p>
                  </div>
                </div>
                <div class="col-md-8 p-4" style="background-color: rgba(255, 255, 255, 0.8);">
                  <div class="d-flex align-items-center mb-3">
                    <h3 class="card-title m-0">{{ $evento->interprete->interprete }}</h3>
                  </div>
                  <h4 class="card-subtitle mb-3 text-muted">{{ $evento->show }}</h4>

                  <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><strong>Ubicación:</strong>
                    {{ $evento->lugar }}</p>
                  <p class="card-text"><i class="fas fa-info-circle me-2"></i><strong>Detalles:</strong>
                    {!! $evento->detalle !!}</p>
                  <p class="card-text"><i class="fas fa-align-left me-2"></i>{{ $evento->descripcion }}</p>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section> --}}

<!-- Biografías de Artistas -->
{{-- <section class="biografias-artistas mb-5">

      <h3 class="fs-4">Grupos y solistas del Folklore Argentino</h3>

      <div class="row">
        @foreach ($interpretes as $artista)
          <div class="col-md-3 mb-4">
            <a href="{{ route('interprete.show', $artista->slug) }}"
              class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
              <div class="card-img-top">
                <img src="{{ asset('storage/interpretes/' . $artista->foto) }}"
                  class="img-fluid w-100 h-auto object-cover" alt="{{ $artista->interprete }}">
              </div>
              <div class="card-body d-flex flex-column">
                <h5 class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">{{ $artista->interprete }}</h5>
                <p class="card-text mt-auto">{{ number_format($artista->visitas, 0, '', ',') }} visitas</p>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </section> --}}

<!-- Letras de Canciones Populares -->
{{-- <section class="letras-canciones mb-5">

      <h4 class="fs-4">Cancionero del Folklore Argentino</h4>

      <div class="row">
        @foreach ($canciones as $cancion)
          <div class="col-md-4 mb-4">
            <a href="{{ route('canciones.show', [$cancion->interprete->slug, $cancion->slug]) }}"
              class="card h-100 shadow-sm text-decoration-none text-white" style="background-color: #343a40;">
              <div class="row g-0 h-100">
                <div class="col-auto d-flex align-items-center justify-content-center p-3 bg-black">
                  <i class="fas fa-music fa-3x"></i>
                </div>
                <div class="col">
                  <div class="card-body d-flex flex-column">
                    <h2 class="card-title h5 mb-2">
                      {{ $cancion->cancion }}
                    </h2>
                    <p class="card-text mb-2" style="font-size: 1.1rem; color: #ffc107;">
                      {{ $cancion->interprete->interprete }}
                    </p>
                    <p class="card-text mt-auto">{{ number_format($cancion->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </section> --}}

<!-- Discos del Folklore Argentino -->
{{-- <section class="letras-canciones mb-4">

      <h5 class="fs-4">Discos del Folklore Argentino</h5>

      <div class="row">
        @foreach ($discos as $disco)
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
    </section> --}}
