@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)

@section('content')
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8">
    <header class="mb-8 border-b border-gray-200 pb-6">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Informacion legal</p>
      <h1 class="mt-3 text-3xl font-bold text-gray-900">Politica de privacidad</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
        Esta politica explica como Mi Folklore Argentino recibe, utiliza y protege los datos personales cuando navegas el portal, te comunicas con el equipo, te suscribis al newsletter o inicias sesion con proveedores externos como Facebook.
      </p>
    </header>

    <div class="space-y-8 text-gray-700">
      <section>
        <h2 class="text-xl font-semibold text-gray-900">1. Responsable del sitio</h2>
        <p class="mt-3 leading-7">
          El responsable del tratamiento de los datos es <strong>Mi Folklore Argentino</strong>. Para consultas sobre privacidad, acceso, correccion o eliminacion de datos puedes escribir a
          <a class="font-semibold text-[#ff661f] hover:underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">2. Datos que podemos recibir</h2>
        <p class="mt-3 leading-7">
          Si eliges iniciar sesion con Facebook, la aplicacion puede recibir el identificador de cuenta provisto por Facebook y, cuando el proveedor lo entrega, datos basicos de perfil como nombre y correo electronico para asociar o crear tu acceso local al portal.
        </p>
        <p class="mt-3 leading-7">
          Ademas, el portal puede recibir los datos que envias de forma directa en formularios publicos, por ejemplo en el formulario de contacto o al publicar contenidos y clasificados.
        </p>
        <p class="mt-3 leading-7">
          Si te suscribis al newsletter, recibimos tu correo electronico (y, si lo haces desde tu cuenta, lo asociamos a ella). Para poder demostrar tu consentimiento guardamos la fecha de tu solicitud, la fecha en que la confirmaste, el origen de la suscripcion y una huella cifrada (hash) de tu direccion IP; no guardamos la IP en claro. El detalle esta en la seccion 9.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">3. Finalidad del tratamiento</h2>
        <ul class="mt-3 space-y-2 leading-7 text-gray-700">
          <li>Permitir el acceso autenticado al portal mediante proveedores externos.</li>
          <li>Responder consultas enviadas desde el formulario de contacto.</li>
          <li>Enviarte el newsletter que solicitaste, junto con los correos de confirmacion y baja de ese servicio.</li>
          <li>Conocer que contenidos del newsletter interesan, mediante contadores agregados de clics que no te identifican.</li>
          <li>Administrar publicaciones, colaboraciones y funciones editoriales del sitio.</li>
          <li>Mejorar la estabilidad tecnica, la seguridad y la medicion general de uso del portal.</li>
        </ul>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">4. Cookies, medicion y publicidad</h2>
        <p class="mt-3 leading-7">
          El portal utiliza cookies tecnicas de sesion para mantener autenticacion y funcionamiento basico. Tambien carga scripts de terceros para analitica y monetizacion publicitaria cuando corresponden, de acuerdo con la implementacion actual del sitio.
        </p>
        <p class="mt-3 leading-7">
          En este momento, el codigo del portal evidencia integraciones con Google Analytics y Google AdSense en el frontend publico. Estos servicios pueden usar sus propias cookies o tecnologias equivalentes segun sus politicas.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">5. Servicios de terceros efectivamente utilizados</h2>
        <ul class="mt-3 space-y-2 leading-7 text-gray-700">
          <li>Facebook, como proveedor de autenticacion OAuth.</li>
          <li>Google, en funciones de autenticacion y servicios de medicion cuando la configuracion correspondiente esta activa.</li>
          <li>Proveedores de correo saliente configurados por el proyecto para enviar notificaciones o respuestas de contacto.</li>
        </ul>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">6. Conservacion y proteccion</h2>
        <p class="mt-3 leading-7">
          Conservamos los datos durante el tiempo necesario para operar el portal, cumplir funciones editoriales, resolver solicitudes y sostener la seguridad de la plataforma. Se aplican medidas razonables de seguridad propias del stack Laravel, incluyendo proteccion de sesiones, hash de contrasenas y limitacion de exposicion publica de datos internos.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">7. Derechos de las personas usuarias</h2>
        <p class="mt-3 leading-7">
          Puedes solicitar acceso, rectificacion, actualizacion o eliminacion de los datos personales asociados a tu cuenta. Si tu acceso fue realizado con Facebook, tambien puedes gestionar la desvinculacion y la eliminacion desde nuestra pagina de
          <a class="font-semibold text-[#ff661f] hover:underline" href="{{ route('legal.data-deletion') }}">eliminacion de datos</a>.
        </p>
        <p class="mt-3 leading-7">
          La Agencia de Acceso a la Informacion Publica, en su caracter de Organo de Control de la Ley 25.326, tiene la atribucion de atender las denuncias y reclamos que interpongan quienes resulten afectados en sus derechos por incumplimiento de las normas vigentes en materia de proteccion de datos personales.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">8. Procedimiento para solicitudes</h2>
        <p class="mt-3 leading-7">
          Para ejercer tus derechos, escribe a <a class="font-semibold text-[#ff661f] hover:underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> indicando el medio con el que te registraste y los datos minimos necesarios para identificar la cuenta. Si la solicitud proviene desde Meta/Facebook, tambien podra procesarse por el callback oficial configurado para la app.
        </p>
      </section>
      <section id="newsletter">
        <h2 class="text-xl font-semibold text-gray-900">9. Newsletter</h2>
        <ul class="mt-3 space-y-2 leading-7 text-gray-700">
          <li><strong>Suscripcion con doble confirmacion.</strong> Al pedir la suscripcion te enviamos un correo con un boton de confirmacion. Recien cuando lo confirmas empezas a recibir el newsletter. Si no confirmas, no recibis ninguna edicion; podemos reenviarte el mensaje de confirmacion.</li>
          <li><strong>Baja en cualquier momento.</strong> Cada edicion incluye un enlace para darte de baja con un clic; tambien podes hacerlo desde tu cuenta (Mis datos). Conservamos tu correo marcado como "baja" unicamente para no volver a escribirte; si preferis que lo eliminemos por completo, pedilo a <a class="font-semibold text-[#ff661f] hover:underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</li>
          <li><strong>Medicion de enlaces.</strong> Los enlaces de cada edicion pasan por una direccion de este portal que cuenta los clics por enlace de forma agregada y te lleva al contenido. No registramos quien hizo cada clic ni su direccion IP, y no usamos pixeles de apertura ni otras tecnologias para saber si leiste el correo.</li>
          <li><strong>Direcciones que no existen.</strong> Si el servidor de correo informa que una direccion no existe, la marcamos para no volver a escribirle.</li>
          <li><strong>Que enviamos.</strong> Un resumen semanal de novedades del portal (noticias, agenda de eventos, discos, canciones y artistas). Cada edicion la revisa y aprueba una persona del equipo antes de enviarse.</li>
        </ul>
      </section>
    </div>

    <footer class="mt-10 border-t border-gray-200 pt-6 text-sm text-gray-500">
      Ultima actualizacion: {{ $lastUpdated }}
    </footer>
  </article>
@endsection
