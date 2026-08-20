@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)

@section('content')
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8">
    <header class="mb-8 border-b border-gray-200 pb-6">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Privacidad y control de datos</p>
      <h1 class="mt-3 text-3xl font-bold text-gray-900">Eliminacion de datos</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
        Si accediste a Mi Folklore Argentino con Facebook, puedes pedir la eliminacion o desvinculacion de los datos relacionados con esa cuenta por cualquiera de las siguientes vias.
      </p>
    </header>

    <div class="space-y-8 text-gray-700">
      <section class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">1. Eliminacion desde Facebook</h2>
        <ol class="mt-4 space-y-2 leading-7">
          <li>Ingresa en Facebook a <strong>Configuracion y privacidad</strong>.</li>
          <li>Abre <strong>Configuracion</strong>.</li>
          <li>Ve a <strong>Apps y sitios web</strong>.</li>
          <li>Selecciona <strong>Mi Folklore Argentino</strong>.</li>
          <li>Elige la opcion de eliminar la app o su acceso.</li>
        </ol>
        <p class="mt-4 leading-7">
          Cuando Meta nos envia el callback oficial de eliminacion, procesamos la desvinculacion o anonimización correspondiente y generamos un codigo publico de confirmacion para consultar el estado.
        </p>
      </section>

      <section class="rounded-2xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">2. Solicitud directa al portal</h2>
        <p class="mt-3 leading-7">
          Tambien puedes escribir a <a class="font-semibold text-[#ff661f] hover:underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> indicando, como minimo:
        </p>
        <ul class="mt-3 space-y-2 leading-7">
          <li>Tu nombre o alias de cuenta.</li>
          <li>El correo usado en el portal, si lo recuerdas.</li>
          <li>Que el acceso estaba asociado a Facebook.</li>
          <li>Cualquier dato adicional que ayude a identificar la cuenta sin exponer informacion sensible innecesaria.</li>
        </ul>
        <p class="mt-4 leading-7">
          El plazo de respuesta depende del caso, pero la solicitud se revisa y procesa en un tiempo razonable. La eliminacion incluye la desvinculacion de Facebook, limpieza de credenciales asociadas y, cuando corresponde, anonimización de datos personales que ya no sean necesarios.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">3. Que pasa con el contenido editorial</h2>
        <p class="mt-3 leading-7">
          El portal no elimina automaticamente noticias, biografias, canciones, festivales, articulos u otros contenidos editoriales si su conservacion es necesaria para la integridad historica o relacional del sitio. En esos casos, la cuenta se desvincula de Facebook y los datos personales pueden anonimizarse sin romper el contenido publico.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">4. Consulta de estado</h2>
        <p class="mt-3 leading-7">
          Si la solicitud fue iniciada por Meta/Facebook, recibiras un codigo de confirmacion asociado a una URL de estado. Esa pagina publica muestra solo el estado general del pedido, sin exponer datos personales.
        </p>
      </section>
    </div>

    <footer class="mt-10 border-t border-gray-200 pt-6 text-sm text-gray-500">
      Ultima actualizacion: {{ $lastUpdated }}
    </footer>
  </article>
@endsection
