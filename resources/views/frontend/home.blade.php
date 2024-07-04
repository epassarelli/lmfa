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

  {{-- <div class="bg-slate-400" style="background-color: gray">
    <div class="container mt-4">
      <!-- Primer Carousel -->
      <h2>Relacionado con tus visitas en Juegos y Juguetes</h2>
      <div id="carouselExampleIndicators1" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselExampleIndicators1" data-bs-slide-to="0" class="active"
            aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselExampleIndicators1" data-bs-slide-to="1"
            aria-label="Slide 2"></button>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="row">
              <!-- Aquí van las cards del primer slide -->
              <div class="col-md-3 m-2">
                <div class="card h-100">
                  <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore"
                    class="card-img-top">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 14px;">Bloques Para Armar Lego 42132 Technic Moto</h5>
                    <p class="card-text" style="font-size: 20px; font-weight: bold;">$45.500</p>
                    <p class="card-text" style="font-size: 14px;">en 6 cuotas de $10.033</p>
                    <p class="card-text" style="font-size: 14px; color: green;">Llega gratis mañana</p>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card h-100">
                  <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore"
                    class="card-img-top">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 14px;">Lego Technic - Jeep Wrangler</h5>
                    <p class="card-text" style="font-size: 20px; font-weight: bold;">$199.800</p>
                    <p class="card-text" style="font-size: 14px;">10% OFF</p>
                    <p class="card-text" style="font-size: 14px; color: green;">Llega gratis mañana</p>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card h-100">
                  <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore"
                    class="card-img-top">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 14px;">Lego Technic - Jeep Wrangler</h5>
                    <p class="card-text" style="font-size: 20px; font-weight: bold;">$199.800</p>
                    <p class="card-text" style="font-size: 14px;">10% OFF</p>
                    <p class="card-text" style="font-size: 14px; color: green;">Llega gratis mañana</p>
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="card h-100">
                  <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore"
                    class="card-img-top">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 14px;">Lego Technic - Jeep Wrangler</h5>
                    <p class="card-text" style="font-size: 20px; font-weight: bold;">$199.800</p>
                    <p class="card-text" style="font-size: 14px;">10% OFF</p>
                    <p class="card-text" style="font-size: 14px; color: green;">Llega gratis mañana</p>
                  </div>
                </div>
              </div>


              <!-- Añade más col-md-2 con cards según sea necesario -->
            </div>
          </div>


          <div class="carousel-item">
            <div class="row">
              <!-- Aquí van las cards del segundo slide -->
              <div class="col-md-2">
                <div class="card h-100">
                  <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore"
                    class="card-img-top">
                  <div class="card-body">
                    <h5 class="card-title" style="font-size: 14px;">Lego Technic - Jeep Wrangler</h5>
                    <p class="card-text" style="font-size: 20px; font-weight: bold;">$199.800</p>
                    <p class="card-text" style="font-size: 14px;">10% OFF</p>
                    <p class="card-text" style="font-size: 14px; color: green;">Llega gratis mañana</p>
                  </div>
                </div>
              </div>
              <!-- Añade más col-md-2 con cards según sea necesario -->
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators1"
          data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators1"
          data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>




      <!-- Segundo Carousel -->
      <div class="card my-4 p-4">
        <h2>Visto recientemente</h2>
        <div id="carouselExampleIndicators2" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators2" data-bs-slide-to="0" class="active"
              aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators2" data-bs-slide-to="1"
              aria-label="Slide 2"></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <div class="row">
                <!-- Aquí van las cards del primer slide -->
                <div class="col-md-2">
                  <div class="card h-100">
                    <img src="path_to_image3.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                      <h5 class="card-title" style="font-size: 14px;">Cpu</h5>
                      <p class="card-text" style="font-size: 20px; font-weight: bold;">$250.000</p>
                    </div>
                  </div>
                </div>
                <!-- Añade más col-md-2 con cards según sea necesario -->
              </div>
            </div>
            <div class="carousel-item">
              <div class="row">
                <!-- Aquí van las cards del segundo slide -->
                <div class="col-md-2">
                  <div class="card h-100">
                    <img src="path_to_image4.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                      <h5 class="card-title" style="font-size: 14px;">Escritorio Plegable Pared Rebatible</h5>
                      <p class="card-text" style="font-size: 20px; font-weight: bold;">$93.738</p>
                      <p class="card-text" style="font-size: 14px;">5% OFF</p>
                      <p class="card-text" style="font-size: 14px; color: green;">Envío gratis</p>
                    </div>
                  </div>
                </div>
                <!-- Añade más col-md-2 con cards según sea necesario -->
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators2"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators2"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>
  </div> --}}

  <div class="container">
    <!-- Noticias Destacadas -->
    <section class="noticias-destacadas mb-5">
      {{-- <h1 class="mb-4">Bienvenidos al Portal del Folklore Argentino</h1> --}}
      <div id="noticiasCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          @foreach ($noticias as $index => $noticia)
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
    <section class="shows-festivales mb-5">
      <h2 class="mb-4">Próximos Shows y Festivales de Folklore Argentino</h2>
      <div class="row">
        @foreach ($shows as $evento)
          <div class="col-md-6">
            <div class="card mb-3 bg-slate-200">
              <div class="row g-0">
                <div class="col-md-4 col-lg-2 d-flex align-items-center justify-content-center bg-light">
                  <div class="text-center">
                    <h5 class="card-title mb-1">{{ date('d', strtotime($evento->fecha)) }}</h5>
                    <p class="card-text mb-1">{{ date('M', strtotime($evento->fecha)) }}</p>
                    <p class="card-text">{{ date('Y', strtotime($evento->fecha)) }}</p>
                  </div>
                </div>
                <div class="col-md-8 col-lg-10">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                      <img src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                        alt="{{ $evento->interprete->interprete }}" class="rounded-circle me-3"
                        style="width: 50px; height: 50px;">
                      <h5 class="card-title m-0">{{ $evento->interprete->interprete }}</h5>
                    </div>
                    <h6 class="card-subtitle mb-2 text-muted"><strong>Show:</strong> {{ $evento->show }}</h6>
                    {{-- <p class="card-text"><strong>Show:</strong> {{ $evento->show }}</p> --}}
                    <p class="card-text"><strong>Detalles:</strong> {!! $evento->detalle !!}</p>
                    <p class="card-text"><strong>Lugar:</strong> {{ $evento->lugar }}</p>
                    <p class="card-text">{{ $evento->descripcion }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>



    <!-- Biografías de Artistas -->
    <section class="biografias-artistas mb-5">
      <h2 class="mb-4">Biografías de Artistas del Folklore Argentino</h2>
      <div class="row">
        @foreach ($interpretes as $artista)
          <div class="col-md-3 mb-4">
            <div class="card h-150">
              <img src="{{ asset('storage/interpretes/' . $artista->foto) }}" class="card-img-top"
                alt="{{ $artista->interprete }}">
              <div class="card-body">
                <h4 class="card-title">{{ $artista->interprete }}</h4>
                {{-- <p class="card-text">{{ Str::limit($artista->biografia, 100) }}</p> --}}
                <a href="{{ route('interpretes.index', $artista->id) }}" class="btn btn-primary">Leer más</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>


    <!-- Letras de Canciones Populares -->
    <section class="letras-canciones mb-5">
      <h2 class="mb-4">Letras de Canciones Populares del Folklore Argentino</h2>
      <div class="row">
        @foreach ($canciones as $cancion)
          <div class="col-md-4 mb-4">
            <a href="{{ route('canciones.show', [$cancion->interprete->slug, $cancion->slug]) }}"
              class="card h-100 shadow-sm text-decoration-none">
              <div class="row g-0">
                <div class="col-auto">
                  @if (file_exists(public_path('storage/interpretes/' . $cancion->interprete->foto)) && $cancion->interprete->foto !== '')
                    <img class="img-fluid rounded-start"
                      src="{{ asset('storage/interpretes/' . $cancion->interprete->foto) }}"
                      alt="{{ $cancion->interprete }}" style="width: 6rem; height: auto; object-fit: cover;">
                  @else
                    <img src="{{ asset('storage/img/imagennodisponible600x400.jpg') }}" alt="Imagen no disponible"
                      class="card-img-top" style="width: 6rem; height: auto; object-fit: cover;">
                  @endif
                </div>
                <div class="col">
                  <div class="card-body">
                    <h2 class="card-title h6 text-dark mb-2 hover:text-primary">
                      {{ $cancion->cancion }}
                    </h2>
                    <p class="card-text text-muted mb-2">
                      {{ $cancion->interprete->interprete }}
                    </p>
                    <p class="card-text">{{ number_format($cancion->visitas, 0, '', ',') }} visitas</p>
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </section>


    <!-- Letras de Canciones Populares -->
    <section class="letras-canciones mb-5">
      <h2 class="mb-4">Discos del Folklore Argentino agregados recientemente al portal</h2>
      <div class="row">
        @foreach ($discos as $disco)
          <div class="col-md-3 mb-4">
            <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
              class="text-decoration-none">
              <div class="card">
                <img class="card-img-top w-100 h-auto object-cover" src="{{ asset('storage/albunes/' . $disco->foto) }}"
                  alt="{{ $disco->album }}">
                <div class="card-body">
                  <h5 class="card-title">{{ $disco->album }}</h5>
                  <p class="card-text text-gray-500 text-sm">{{ $disco->interprete->interprete }}</p>
                  <p class="card-text">{{ number_format($disco->visitas, 0, '', ',') }} visitas</p>
                </div>
              </div>
            </a>
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
