    <!-- footer -->
    <div class="bg-dark">
      <div class="container py-4 small">
        <div class="row">


          <!-- contacto -->
          <div class="col-md-4 mb-3">
            <p class="h6 fw-bold text-white">Contacto</p>
            <ul class="list-unstyled light text-light">
              <li>¡Sigue nuestras redes sociales para estar al día con las últimas noticias, lanzamientos y eventos de
                música!</li>
            </ul>
          </div>


          <!-- categorias -->
          <div class="col-md-4 mb-3">
            <p class="h6 fw-bold text-white">Otras secciones</p>
            <ul class="list-unstyled text-white-50 light">
              {{-- <li><a href="#" class="text-decoration-none link-light" title="Politica de privacidad"> Politica de
                  privacidad
                </a></li> --}}
              <li class="link-light {{ request()->segment(1) == 'biografias-de-artistas-folkloricos' ? 'active' : '' }}">
                <a href="{{ route('interpretes.index') }}" title="Biografías de artistas folklóricos"
                  class="nav-link">Biografías</a>
              </li>
              {{-- <li class="link-light {{ request()->segment(1) == 'noticias-de-folklore-argentino' ? 'active' : '' }}">
            <a href="{{ route('noticias.index') }}" title="Noticias del folklore argentino"
              class="nav-link">Noticias</a>
          </li> --}}
              <li class="link-light {{ request()->segment(1) == 'cartelera-de-eventos-folkloricos' ? 'active' : '' }}">
                <a href="{{ route('shows.index') }}" title="Cartelera de eventos folklóricos"
                  class="nav-link">Cartelera</a>
              </li>
              <li
                class="link-light {{ request()->segment(1) == 'discografias-del-folklore-argentino' ? 'active' : '' }}">
                <a href="{{ route('discos.index') }}" title="Discografias del folklore argentino"
                  class="nav-link">Discos</a>
              </li>
              <li class="link-light {{ request()->segment(1) == 'letras-de-canciones-folkloricas' ? 'active' : '' }}">
                <a href="{{ route('canciones.index') }}" title="Letras de canciones folklóricas"
                  class="nav-link">Canciones</a>
              </li>
              <li
                class="link-light {{ request()->segment(1) == 'festivales-y-fiestas-tradicionales' ? 'active' : '' }}">
                <a href="{{ route('festivales.index') }}" title="Festivales y fiestas tradicionales"
                  class="nav-link">Festivales</a>
              </li>
              <li class="link-light {{ request()->segment(1) == 'mitos-y-leyendas-argentinas' ? 'active' : '' }}">
                <a href="{{ route('mitos.index') }}" title="Mitos y leyendas argentinas" class="nav-link">Mitos y
                  Leyendas</a>
              </li>
              <li
                class="link-light {{ request()->segment(1) == 'recetas-de-comidas-tipicas-argentinas' ? 'active' : '' }}">
                <a href="{{ route('comidas.index') }}" title="Recetas de comidas típicas argentinas"
                  class="nav-link">Comidas</a>
              </li>
            </ul>
          </div>


          <!-- data fiscal -->
          {{-- <div class="col-md-3 mb-3">
            <p class="h6 fw-bold text-white">Data fiscal</p>
            <img src="{{ asset('img/datafiscal-qr.png') }}" title="Imagen de data fiscal de la empresa">
          </div> --}}


          <!-- redes -->
          <div class="col-md-3">
            <p class="h6 fw-bold text-white">Nuestras redes</p>
            <ul class="list-group list-group-horizontal">
              <li class="list-group-item bg-transparent ps-0 border-0 light text-light">
                <a href="https://www.facebook.com/MiFolkloreArgentino" class="text-decoration-none link-light"
                  title="Nuestra página de Facebook" target="_blank"> <i class="fa-brands fa-facebook fa-3x"
                    style="color: #3b5998;"></i> </a>
              </li>
              <li class="list-group-item bg-transparent ps-0 border-0 light text-light">
                <a href="https://www.instagram.com/mifolkloreargentino/" class="text-decoration-none link-light"
                  title="Nuestro Instagram" target="_blank"> <i class="fab fa-instagram fa-3x"
                    style="color: #c32aa3;"></i></a>
              </li>
              <li class="list-group-item bg-transparent ps-0 border-0 light text-light">
                <a href="https://www.twitter.com/mifolklorearg/" class="text-decoration-none link-light"
                  title="Nuestro Twitter" target="_blank"> <i class="fab fa-twitter fa-3x"
                    style="color: #1da1f2;"></i></a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- FIN footer -->


    {{-- <script src="{{ asset('js/magazine/jquery.min.js') }}"></script>
    <script src="{{ asset('js/magazine/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/magazine/main.js') }}"></script>


    <script>
      document.addEventListener("DOMContentLoaded", function() {
        $('.owl-carousel').owlCarousel({
          loop: true,
          margin: 10,
          nav: true,
          responsive: {
            0: {
              items: 1
            },
            576: {
              items: 2
            },
            1200: {
              items: 2
            }
          }

        });
      });
    </script> --}}


    {{-- <script src="js/jquery.min.js"></script> --}}
    <!-- <script src="libs/bootstrap/dist/js/bootstrap.min.js"></script> -->
    {{-- <script src="js/bootstrap.bundle.min.js"></script> --}}

    <script type="text/javascript" charset="utf-8">
      // tooltip
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });

      // menú dropdown hover
      const $dropdown = $(".dropdown");
      const $dropdownToggle = $(".dropdown-toggle");
      const $dropdownMenu = $(".dropdown-menu");
      const showClass = "show";

      $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 768px)").matches) {
          $dropdown.hover(
            function() {
              const $this = $(this);
              $this.addClass(showClass);
              $this.find($dropdownToggle).attr("aria-expanded", "true");
              $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
              const $this = $(this);
              $this.removeClass(showClass);
              $this.find($dropdownToggle).attr("aria-expanded", "false");
              $this.find($dropdownMenu).removeClass(showClass);
            }
          );
        } else {
          $dropdown.off("mouseenter mouseleave");
        }
      });

      // search animado
      const searchBtn = document.querySelector('#searchBtn');
      const animatedInput = document.querySelector('#animated-input');

      searchBtn.addEventListener('click', openSearch);

      function openSearch(e) {
        animatedInput.focus();
      }
      // Check if there is text in input every 50ms
      setInterval(function() {
        if (animatedInput.value) {
          animatedInput.style.width = '225px';
        }
      }, 50);
    </script>
