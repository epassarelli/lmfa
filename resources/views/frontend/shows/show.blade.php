@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <section class="default-news-area">
    <div class="container">

      <div class="row">

        {{-- Seccion de bloques de noticias --}}
        <div class="col-lg-8">
        </div>

        <div class="col-lg-4">

          <aside class="widget-area">

            <section class="widget widget_latest_news_thumb">
              <h3 class="widget-title">Eventos destacados</h3>
            </section>

            <section class="widget widget_stay_connected">
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
            </section>

            <section class="widget widget_newsletter">
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
            </section>

          </aside>
        </div>
      </div>
    </div>
  </section>

@endsection
