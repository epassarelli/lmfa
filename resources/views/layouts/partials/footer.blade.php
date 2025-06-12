<footer class="bg-black text-white text-sm">
  <div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Contacto -->
      <div>
        <p class="text-lg font-bold text-white mb-2">Contacto</p>
        <p class="text-gray-300">
          ¡Sigue nuestras redes sociales para estar al día con las últimas noticias, lanzamientos y eventos de música!
        </p>
      </div>

      <!-- Categorías -->
      <div>
        <p class="text-lg font-bold text-white mb-2">Otras secciones</p>
        <ul class="space-y-1 text-gray-400">
          <li>
            <a href="{{ route('mitos.index') }}"
              class="hover:text-white transition-colors {{ request()->segment(1) == 'mitos-y-leyendas-argentinas' ? 'text-white font-semibold' : '' }}">
              Mitos y Leyendas
            </a>
          </li>
          <li>
            <a href="{{ route('comidas.index') }}"
              class="hover:text-white transition-colors {{ request()->segment(1) == 'recetas-de-comidas-tipicas-argentinas' ? 'text-white font-semibold' : '' }}">
              Comidas
            </a>
          </li>
        </ul>
      </div>

      <!-- Redes -->
      <div>
        <p class="text-lg font-bold text-white mb-2">Nuestras redes</p>
        <div class="flex space-x-4 items-center">
          <a href="https://www.facebook.com/MiFolkloreArgentino" target="_blank" title="Facebook">
            <i class="fa-brands fa-facebook fa-2xl" style="color: #3b5998;"></i>
          </a>
          <a href="https://www.instagram.com/mifolkloreargentino/" target="_blank" title="Instagram">
            <i class="fas fa-instagram fa-2xl" style="color: #c32aa3;"></i>
          </a>
          <a href="https://www.twitter.com/mifolklorearg/" target="_blank" title="Twitter">
            <i class="fas fa-twitter fa-2xl" style="color: #1da1f2;"></i>
          </a>
        </div>
      </div>

    </div>
  </div>
</footer>



{{-- <script type="text/javascript" charset="utf-8">
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
    </script> --}}
