@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
{{-- 
@section('content')

  <div class="container">

    <div class="row g-4 mt-4">
      <div class="col-sm-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h2 class="card-title h5">&iquest;Qu&eacute; es el Folklore Argentino?</h2>
            <p class="card-text">En sus vertientes musicales, el folklore argentino es muy variado en r&iacute;tmicas,
              timbres, y letras relacionados directamente al lugar de origen.</p>
            <p class="card-text">La amplia extensi&oacute;n territorial da como resultado muchos estilos que difieren de
              una
              regi&oacute;n a otra. No s&oacute;lo en la m&uacute;sica e instrumentos, sino tambi&eacute;n involucra
              ceremonias y bailes t&iacute;picos.</p>
          </div>
        </div>
      </div>

      <div class="col-sm-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h2 class="card-title h5">&iquest;Qu&eacute; encontrar&aacute; en Mi Folklore Argentino?</h2>
            <p class="card-text">Letras de canciones de la&nbsp;m&uacute;sica&nbsp;popular argentina. Acordes de
              <em>canciones folkl&oacute;ricas</em>. Mitos, leyendas y costumbres de el gaucho argentino.
            </p>
            <p class="card-text">Historia, fotos, videos y discograf&iacute;as de <em><strong>grupos y solistas del
                  folklore
                  argentino</strong></em>. Comidas tipicas y populares asociadas a nuestro folklore argentino y destrezas
              varias.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-4 shadow-sm">
      <div class="card-body">
        <h2 class="card-title h5">Historia de la m&uacute;sica Folkl&oacute;rica Argentina</h2>
        <p class="card-text">La <strong>m&uacute;sica folkl&oacute;rica argentina</strong> tiene una historia centenaria
          que
          encuentra sus ra&iacute;ces en las culturas ind&iacute;genas originarias. Tres grandes acontecimientos
          hist&oacute;rico-culturales la fueron moldeando: la colonizaci&oacute;n espa&ntilde;ola, la inmigraci&oacute;n
          europea y la migraci&oacute;n interna.</p>
        <p class="card-text">Aunque estrictamente <b>folklore</b> s&oacute;lo es aquella expresi&oacute;n cultural que
          re&uacute;ne los requisitos de ser an&oacute;nima, popular y tradicional, en Argentina <b>folklore</b> o
          <b>m&uacute;sica folkl&oacute;rica</b> es la m&uacute;sica popular y tradicional de autor conocido, inspirada en
          ritmos y estilos caracter&iacute;sticos de las culturas provinciales, mayormente de ra&iacute;ces
          ind&iacute;genas.
        </p>
        <p class="card-text">En Argentina, el folklore comenz&oacute; a adquirir popularidad en los a&ntilde;os treinta y
          cuarenta, en coincidencia a una gran ola de migraci&oacute;n interna desde el campo a la ciudad y de las
          provincias a Buenos Aires, para instalarse en los a&ntilde;os cincuenta, con el <i>boom del folklore</i>, como
          g&eacute;nero principal de la m&uacute;sica popular nacional y tradicional junto al tango.</p>
        <p class="card-text">En los a&ntilde;os sesenta y setenta se expandi&oacute; la popularidad del <b>folklore
            argentino</b> y se vincul&oacute; a otras expresiones similares de Am&eacute;rica Latina, de la mano de
          diversos
          movimientos de renovaci&oacute;n musical y l&iacute;rica, y la aparici&oacute;n de grandes festivales de este
          g&eacute;nero, en particular, el <strong>Festival Nacional de Folklore de Cosqu&iacute;n</strong>, probablemente
          el m&aacute;s importantes del mundo en ese campo.</p>
        <p class="card-text">Luego de ser seriamente afectado por la represi&oacute;n cultural impuesta en la dictadura
          instalada entre 1976-1983, la m&uacute;sica folkl&oacute;rica resurgi&oacute; a partir de la Guerra de las
          Malvinas de 1982, aunque con expresiones relacionadas a otros g&eacute;neros de la m&uacute;sica popular
          argentina
          y latinoamericana, como el tango, el llamado &laquo;rock nacional&raquo;, la balada rom&aacute;ntica
          latinoamericana, el cuarteto y la cumbia.</p>
        <p class="card-text">La evoluci&oacute;n hist&oacute;rica fue conformando cuatro grandes regiones en la
          <strong>m&uacute;sica folkl&oacute;rica argentina</strong>: la cordobesa-noroeste, la cuyana, la litoralena y la
          surera pampeano-patag&oacute;nica, a su vez influenciadas, e influyentes en, las culturas musicales de
          pa&iacute;ses fronterizos: Bolivia, sur de Brasil, Chile, Paraguay y Uruguay. Atahualpa Yupanqui es
          un&aacute;nimemente considerado el artista m&aacute;s importante de la historia de la m&uacute;sica
          Folkl&oacute;rica Argentina.
        </p>
      </div>
    </div>


  </div>


@endsection --}}

@section('content')

  <div class="container">

    <!-- Noticias Destacadas -->
    <section class="noticias-destacadas mb-5">
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

    </section>




    <!-- Próximos Shows y Festivales -->
    <section class="shows-festivales mb-5">

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
    </section>








    <!-- Biografías de Artistas -->
    <section class="biografias-artistas mb-5">

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
    </section>



    <!-- Letras de Canciones Populares -->
    <section class="letras-canciones mb-5">

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
    </section>






    <!-- Discos del Folklore Argentino -->
    <section class="letras-canciones mb-4">

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
    </section>


    <h1 class="fs-4 text-center">El portal del Folklore Argentino</h1>

  </div>

@endsection
