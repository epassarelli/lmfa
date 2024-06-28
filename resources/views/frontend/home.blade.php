@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
{{-- 
@section('content')

  <div class="container">

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('canciones.index') }}">
            <img src="{{ asset('img/cancionero-folklorico.jpg') }}" alt="Cancionero folklorico" class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Letras de Canciones</h2>
            </div>
          </a>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('shows.index') }}">
            <img src="{{ asset('img/cartelera-folklorica.jpg') }}" alt="Cartelera folklorica" class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Cartelera Folklorica</h2>
            </div>
          </a>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('festivales.index') }}">
            <img src="{{ asset('img/fiestas-tradicionales-argentina.jpg') }}" alt="Fiestas y festivales folkloricos"
              class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Festivales Tradicionales</h2>
            </div>
          </a>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('interpretes.index') }}">
            <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore" class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Biografías folklóricas</h2>
            </div>
          </a>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('comidas.index') }}">
            <img src="{{ asset('img/comidas-tipicas.jpg') }}" alt="Comidas tipicas folkloricas" class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Comidas Tradicionales</h2>
            </div>
          </a>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 shadow-sm">
          <a href="{{ route('mitos.index') }}">
            <img src="{{ asset('img/mitos-leyendas-folklore.jpg') }}" alt="Mitos y leyendas" class="card-img-top">
            <div class="card-body">
              <h2 class="card-title h5">Mitos, Leyendas y Fabulas</h2>
            </div>
          </a>
        </div>
      </div>
    </div>

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
    <!-- Introducción -->
    {{-- <section class="intro mb-5">
      <h1 class="mb-4">Bienvenidos a Nuestro Portal de Folklore Argentino</h1>
      <p>Descubre las últimas noticias, eventos, letras de canciones, biografías de artistas y mucho más sobre el folklore
        argentino. Nuestro objetivo es preservar y promover la rica cultura del folklore en Argentina.</p>
    </section> --}}

    <!-- Noticias Destacadas -->
    <section class="noticias-destacadas mb-5">
      <h1 class="mb-4">Bienvenidos al Portal del Folklore Argentino</h1>
      <div id="noticiasCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          @foreach ($noticiasDestacadas as $index => $noticia)
            <div class="carousel-item @if ($index == 0) active @endif">
              <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" class="d-block w-100"
                alt="{{ $noticia->titulo }}">
              <div class="carousel-caption d-none d-md-block">
                <h3>{{ $noticia->titulo }}</h3>
                <p>{{ $noticia->resumen }}</p>
                <a href="{{ route('noticias.index', $noticia->id) }}" class="btn btn-primary">Leer más</a>
              </div>
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
    {{-- <section class="shows-festivales mb-5">
      <h2 class="mb-4">Próximos Shows y Festivales de Folklore Argentino</h2>
      <div class="row">
        @foreach ($eventos as $evento)
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="{{ asset('storage/' . $evento->imagen) }}" class="card-img-top" alt="{{ $evento->nombre }}">
              <div class="card-body">
                <h4 class="card-title">{{ $evento->nombre }}</h4>
                <p class="card-text">{{ $evento->fecha }} - {{ $evento->lugar }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section> --}}

    <!-- Letras de Canciones Populares -->
    {{-- <section class="letras-canciones mb-5">
      <h2 class="mb-4">Letras de Canciones Populares del Folklore Argentino</h2>
      <div class="row">
        @foreach ($canciones as $cancion)
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="{{ asset('storage/' . $cancion->imagen) }}" class="card-img-top" alt="{{ $cancion->titulo }}">
              <div class="card-body">
                <h4 class="card-title">{{ $cancion->titulo }}</h4>
                <p class="card-text">{{ Str::limit($cancion->letra, 100) }}</p>
                <a href="{{ route('canciones.show', $cancion->id) }}" class="btn btn-primary">Leer más</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section> --}}

    <!-- Biografías de Artistas -->
    <section class="biografias-artistas mb-5">
      <h2 class="mb-4">Biografías de Artistas del Folklore Argentino</h2>
      <div class="row">
        @foreach ($artistas as $artista)
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="{{ asset('storage/interpretes/' . $artista->imagen) }}" class="card-img-top"
                alt="{{ $artista->nombre }}">
              <div class="card-body">
                <h4 class="card-title">{{ $artista->interprete }}</h4>
                <p class="card-text">{{ Str::limit($artista->biografia, 100) }}</p>
                <a href="{{ route('interpretes.index', $artista->id) }}" class="btn btn-primary">Leer más</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>

    <!-- Artículos y Blog -->
    {{-- <section class="articulos-blog">
      <h2 class="mb-4">Artículos y Blog sobre la Cultura del Folklore Argentino</h2>
      <div class="row">
        @foreach ($articulos as $articulo)
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="{{ asset('storage/' . $articulo->imagen) }}" class="card-img-top" alt="{{ $articulo->titulo }}">
              <div class="card-body">
                <h4 class="card-title">{{ $articulo->titulo }}</h4>
                <p class="card-text">{{ Str::limit($articulo->contenido, 100) }}</p>
                <a href="{{ route('blog.show', $articulo->id) }}" class="btn btn-primary">Leer más</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section> --}}

  </div>
@endsection
