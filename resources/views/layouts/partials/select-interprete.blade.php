<!-- resources/views/partials/select-interprete.blade.php -->
<div class="container mt-5 mb-4" id="interprete-container" data-section="{{ $section }}">


  @if ($section === 'noticias')
    <h2>Explora noticias de otros Intérpretes</h2>
    <p class="lead">
      Explora las últimas noticias de otros intérpretes del folklore argentino. Selecciona un artista de la lista a
      continuación y descubre las novedades sobre su carrera y su música.
    </p>
  @elseif($section === 'discografias')
    <h2>Explora más Discografías</h2>
    <p class="lead">
      Descubre la música de otros intérpretes del folklore argentino. Elige un artista de la lista a continuación y
      sumérgete en su discografía completa, desde sus primeras grabaciones hasta sus últimos éxitos.
    </p>
  @elseif($section === 'canciones')
    <h2>Encuentra más Letras de Canciones</h2>
    <p class="lead">
      Explora las letras de canciones de otros intérpretes del folklore argentino. Selecciona un artista de la lista
      a
      continuación y disfruta de las palabras y los mensajes que caracterizan su música.
    </p>
  @elseif($section === 'shows')
    <h2>Descubre más Shows y Eventos</h2>
    <p class="lead">
      No te pierdas la oportunidad de ver en vivo a otros intérpretes del folklore argentino. Elige un artista de
      la
      lista a continuación y consulta su cartelera de shows y eventos para disfrutar de su música en directo.
    </p>
  @elseif($section === 'biografias')
    <h2>Explora más Biografías</h2>
    <p class="lead">
      Conoce la historia y el legado de otros intérpretes del folklore argentino. Selecciona un artista de la
      lista a
      continuación y descubre los detalles de su vida y su carrera artística.
    </p>
  @endif

  <div class="card">
    <div class="card-body">
      {{-- <h5 class="card-title">Cambiar de Intérprete</h5> --}}
      <form id="change-interprete-form">
        <div class="form-group">
          {{-- <label for="interprete-select">Seleccione un intérprete:</label> --}}
          <select class="form-control" id="interprete-select">
            <option value="">-- Seleccione otro interprete --</option>
            @foreach ($interpretes as $interprete)
              <option value="{{ $interprete->slug }}">{{ $interprete->interprete }}</option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- <script>
  document.getElementById('interprete-select').addEventListener('change', function() {
    var slug = this.value;
    var currentUrl = window.location.href;
    var newUrl = currentUrl.replace(/\/[^\/]+$/, '/' + slug);
    window.location.href = newUrl;
  });
</script> --}}

<script>
  document.getElementById('interprete-select').addEventListener('change', function() {
    var slug = this.value;
    if (!slug) return; // Si no se selecciona un intérprete, no hacer nada

    // Obtener la sección desde el atributo data-section de PHP
    var sectionElement = document.getElementById('interprete-container');
    var section = sectionElement ? sectionElement.dataset.section : '';

    console.log("Sección detectada:", section); // Debug: Verificar qué sección está leyendo
    console.log("Slug seleccionado:", slug); // Debug: Verificar qué slug se está seleccionando

    var currentUrl = window.location.pathname; // Obtener la URL actual sin dominio
    var newUrl;

    if (section === "noticias") {
      // Si es noticias, agregar "interprete-" al slug
      newUrl = currentUrl.replace(/\/[^\/]+$/, '/interprete-' + slug);
    } else {
      // Para cualquier otra sección, solo reemplazar el slug
      newUrl = currentUrl.replace(/\/[^\/]+$/, '/' + slug);
    }

    console.log("Nueva URL generada:", newUrl); // Debug: Verificar qué URL se está generando

    window.location.href = newUrl;
  });
</script>
