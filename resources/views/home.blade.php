@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
<x-app-layout>


  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('canciones.index') }}">
          <img src="{{ asset('img/cancionero-folklorico.jpg') }}" alt="Cancionero folklorico" class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Letras de Canciones</h2>
          </div>
        </a>
      </div>
    </div>

    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('shows.index') }}">
          <img src="{{ asset('img/cartelera-folklorica.jpg') }}" alt="Cartelera folklorica" class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Cartelera Folklorica</h2>
          </div>
        </a>
      </div>
    </div>

    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('festivales.index') }}">
          <img src="{{ asset('img/fiestas-tradicionales-argentina.jpg') }}" alt="Fiestas y festivales folkloricos"
            class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Festivales Tradicionales</h2>
          </div>
        </a>
      </div>
    </div>
    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('interpretes.index') }}">
          <img src="{{ asset('img/biografias-folkloricas.jpg') }}" alt="Biografias de folklore" class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Biografías folklóricas</h2>
          </div>
        </a>
      </div>
    </div>
    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('comidas.index') }}">
          <img src="{{ asset('img/comidas-tipicas.jpg') }}" alt="Comidas tipicas folkloricas" class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Comidas Tradicionales</h2>
          </div>
        </a>
      </div>
    </div>
    <div class="mx-auto mb-6">
      <div class="bg-white shadow-md rounded-md overflow-hidden">
        <a href="{{ route('mitos.index') }}">
          <img src="{{ asset('img/mitos-leyendas-folklore.jpg') }}" alt="Mitos y leyendas" class="w-full">
          <div class="p-4">
            <h2 class="text-xl font-semibold">Mitos, Leyendas y Fabulas</h2>
          </div>
        </a>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">&iquest;Qu&eacute; es el Folklore Argentino?</h2>
      <div>
        <p class="mb-2">En sus vertientes musicales, el folklore argentino es muy variado en r&iacute;tmicas,
          timbres, y letras relacionados directamente al lugar de origen.</p>
        <p>La amplia extensi&oacute;n territorial da como resultado muchos estilos que difieren de una regi&oacute;n
          a otra. No s&oacute;lo en la m&uacute;sica e instrumentos, sino tambi&eacute;n involucra ceremonias y
          bailes t&iacute;picos.</p>
      </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">&iquest;Qu&eacute; encontrar&aacute; en Mi Folklore Argentino?</h2>
      <div>
        <p class="mb-2">Letras de canciones de la&nbsp;m&uacute;sica&nbsp;popular argentina. Acordes de
          <em>canciones folkl&oacute;ricas</em>. Mitos, leyendas y costumbres de el gaucho argentino.
        </p>
        <p>Historia, fotos, videos y discograf&iacute;as de <em><strong>grupos y solistas del folklore
              argentino</strong></em>. Comidas tipicas y populares asociadas a nuestro folklore argentino y
          destrezas varias.</p>
      </div>
    </div>

  </div>








  <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-4">Historia de la m&uacute;sica Folkl&oacute;rica Argentina</h2>
    <div>
      <p class="mb-4">La <strong>m&uacute;sica folkl&oacute;rica argentina</strong> tiene una historia centenaria
        que encuentra sus ra&iacute;ces en las culturas ind&iacute;genas originarias. Tres grandes acontecimientos
        hist&oacute;rico-culturales la fueron moldeando: la colonizaci&oacute;n espa&ntilde;ola, la
        inmigraci&oacute;n europea y la migraci&oacute;n interna .</p>
      <p class="mb-4">Aunque estrictamente <b>folklore</b> s&oacute;lo es aquella expresi&oacute;n cultural que
        re&uacute;ne los requisitos de ser an&oacute;nima, popular y tradicional, en Argentina <b>folklore</b> o
        <b>m&uacute;sica folkl&oacute;rica</b> es la m&uacute;sica popular y tradicional de autor conocido,
        inspirada en ritmos y estilos caracter&iacute;sticos de las culturas provinciales, mayormente de
        ra&iacute;ces ind&iacute;genas.
      </p>
      <p class="mb-4">En Argentina, el folklore comenz&oacute; a adquirir popularidad en los a&ntilde;os treinta y
        cuarenta, en coincidencia a una gran ola de migraci&oacute;n interna desde el campo a la ciudad y de las
        provincias a Buenos Aires, para instalarse en los a&ntilde;os cincuenta, con el <i>boom del folklore</i>,
        como g&eacute;nero principal de la m&uacute;sica popular nacional y tradicional junto al tango.</p>
      <p class="mb-4">En los a&ntilde;os sesenta y setenta se expandi&oacute; la popularidad del <b>folklore
          argentino</b> y se vincul&oacute; a otras expresiones similares de Am&eacute;rica Latina, de la mano de
        diversos movimientos de renovaci&oacute;n musical y l&iacute;rica, y la aparici&oacute;n de grandes
        festivales de este g&eacute;nero, en particular, el <strong>Festival Nacional de Folklore de
          Cosqu&iacute;n</strong>, probablemente el m&aacute;s importantes del mundo en ese campo.</p>
      <p class="mb-4">Luego de ser seriamente afectado por la represi&oacute;n cultural impuesta en la dictadura
        instalada entre 1976-1983, la m&uacute;sica folkl&oacute;rica resurgi&oacute; a partir de la Guerra de las
        Malvinas de 1982, aunque con expresiones relacionadas a otros g&eacute;neros de la m&uacute;sica popular
        argentina y latinoamericana, como el tango, el llamado &laquo;rock nacional&raquo;, la balada
        rom&aacute;ntica latinoamericana, el cuarteto y la cumbia.</p>
      <p>La evoluci&oacute;n hist&oacute;rica fue conformando cuatro grandes regiones en la <strong>m&uacute;sica
          folkl&oacute;rica argentina</strong>: la cordobesa-noroeste, la cuyana, la litoralena y la surera
        pampeano-patag&oacute;nica, a su vez influenciadas, e influyentes en, las culturas musicales de
        pa&iacute;ses fronterizos: Bolivia, sur de Brasil, Chile, Paraguay y Uruguay. Atahualpa Yupanqui es
        un&aacute;nimemente considerado el artista m&aacute;s importante de la historia de la m&uacute;sica
        Folkl&oacute;rica Argentina.</p>
    </div>
  </div>




</x-app-layout>
