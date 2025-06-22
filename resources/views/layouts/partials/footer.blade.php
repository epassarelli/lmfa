<footer class="bg-black text-white text-sm">
  <div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Contacto -->
      <div>
        <p class="text-lg font-semibold text-white mb-2">Contacto</p>
        <p class="text-gray-300">
          ¡Sigue nuestras redes sociales para estar al día con las últimas noticias, lanzamientos y eventos de música!
        </p>
      </div>

      <!-- Categorías -->
      <div>
        <p class="text-lg font-semibold text-white mb-2">Otras secciones</p>
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
        <p class="text-lg font-semibold text-white mb-2">Nuestras redes</p>
        <div class="flex space-x-4">
          <a href="https://www.facebook.com/MiFolkloreArgentino/" target="_blank" aria-label="Facebook">
            <svg class="w-6 h-6 text-[#ff661f] hover:text-orange-600 transition-colors" fill="currentColor"
              viewBox="0 0 24 24">
              <path
                d="M22 12c0-5.522-4.478-10-10-10S2 6.478 2 12c0 4.991 3.657 9.128 8.438 9.879v-6.987h-2.54v-2.892h2.54V9.845c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.242 0-1.63.771-1.63 1.562v1.875h2.773l-.443 2.892h-2.33v6.987C18.343 21.128 22 16.991 22 12z" />
            </svg>
          </a>
          <a href="https://www.instagram.com/mifolkloreargentino/" target="_blank" aria-label="Instagram">
            <svg class="w-6 h-6 text-[#ff661f] hover:text-orange-600 transition-colors" fill="currentColor"
              viewBox="0 0 24 24">
              <path
                d="M12 2.2c3.2 0 3.584.012 4.85.07 1.17.054 1.97.24 2.427.415a4.92 4.92 0 0 1 1.758 1.14 4.922 4.922 0 0 1 1.14 1.757c.175.457.361 1.258.415 2.428.058 1.266.07 1.65.07 4.85s-.012 3.584-.07 4.85c-.054 1.17-.24 1.97-.415 2.427a4.92 4.92 0 0 1-1.14 1.758 4.922 4.922 0 0 1-1.758 1.14c-.457.175-1.258.361-2.428.415-1.266.058-1.65.07-4.85.07s-3.584-.012-4.85-.07c-1.17-.054-1.97-.24-2.427-.415a4.92 4.92 0 0 1-1.758-1.14 4.922 4.922 0 0 1-1.14-1.758c-.175-.457-.361-1.258-.415-2.428C2.212 15.784 2.2 15.4 2.2 12s.012-3.584.07-4.85c.054-1.17.24-1.97.415-2.427a4.92 4.92 0 0 1 1.14-1.758A4.922 4.922 0 0 1 5.583 2.685c.457-.175 1.258-.361 2.428-.415C8.416 2.212 8.8 2.2 12 2.2zm0 1.8c-3.152 0-3.518.012-4.756.068-.989.045-1.521.21-1.875.35a3.12 3.12 0 0 0-1.13.74 3.12 3.12 0 0 0-.74 1.13c-.14.354-.305.886-.35 1.875C3.812 9.482 3.8 9.848 3.8 13s.012 3.518.068 4.756c.045.989.21 1.521.35 1.875.175.43.408.823.74 1.13.307.332.7.565 1.13.74.354.14.886.305 1.875.35 1.238.056 1.604.068 4.756.068s3.518-.012 4.756-.068c.989-.045 1.521-.21 1.875-.35a3.12 3.12 0 0 0 1.13-.74 3.12 3.12 0 0 0 .74-1.13c.14-.354.305-.886.35-1.875.056-1.238.068-1.604.068-4.756s-.012-3.518-.068-4.756c-.045-.989-.21-1.521-.35-1.875a3.12 3.12 0 0 0-.74-1.13 3.12 3.12 0 0 0-1.13-.74c-.354-.14-.886-.305-1.875-.35C15.518 4.012 15.152 4 12 4zm0 3.4a4.6 4.6 0 1 1 0 9.2 4.6 4.6 0 0 1 0-9.2zm0 1.8a2.8 2.8 0 1 0 0 5.6 2.8 2.8 0 0 0 0-5.6zm5.2-.9a1.05 1.05 0 1 1 0 2.1 1.05 1.05 0 0 1 0-2.1z" />
            </svg>
          </a>
          <!-- Agregás SVG para X/Twitter y YouTube aquí -->
          <a href="https://x.com/MiFolkloreArg" target="_blank" aria-label="X">
            <svg class="w-6 h-6 text-[#ff661f] hover:text-orange-600 transition-colors" viewBox="0 0 24 24"
              fill="currentColor">
              <path
                d="M20.662 3H17.54l-3.95 5.14-4.05-5.14H3.338l6.625 8.407L3 21h3.122l4.456-5.73L15 21h5l-7.012-8.902L20.662 3Zm-3.013 2h1.288l-4.713 6.063 4.725 5.967H17.65l-4.21-5.31-4.237 5.31H7.703l5.092-6.476L5.65 5h1.322l4.19 5.267L17.65 5Z" />
            </svg>
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
