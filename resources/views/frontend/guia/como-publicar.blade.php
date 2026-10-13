@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)

@section('content')
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8" data-publish-guide>
    <header class="mb-8 border-b border-gray-200 pb-6">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Participá</p>
      <h1 class="mt-3 text-3xl font-bold text-gray-900">Cómo publicar en Mi Folklore Argentino</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
        Si sos artista, organizás un festival o querés aportar información, podés cargarla vos mismo desde tu cuenta. Es gratis y un equipo editorial la revisa antes de mostrarla.
      </p>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('register') }}" class="inline-flex rounded-md bg-[#ff661f] px-4 py-2.5 font-semibold text-white transition hover:bg-orange-600">Crear mi cuenta</a>
        <a href="{{ route('dashboard') }}" class="inline-flex rounded-md border border-gray-300 px-4 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50">Ya tengo cuenta</a>
      </div>
    </header>

    <div class="space-y-10 text-gray-700">
      <section aria-labelledby="pasos">
        <h2 id="pasos" class="text-xl font-semibold text-gray-900">Cinco pasos</h2>
        <ol class="mt-4 space-y-4">
          <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff661f] font-bold text-white" aria-hidden="true">1</span>
            <div><h3 class="font-semibold text-gray-900">Creá tu cuenta</h3>
              <p class="mt-1 leading-7">Registrate con tu email y confirmalo desde el correo que te enviamos.</p></div></li>
          <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff661f] font-bold text-white" aria-hidden="true">2</span>
            <div><h3 class="font-semibold text-gray-900">Pedí tu ficha en «Mis fichas»</h3>
              <p class="mt-1 leading-7">Una <strong>ficha</strong> es la página de un artista o de un festival. Buscá la tuya y pedí vincularte. Si todavía no existe, pedí que la creemos.</p></div></li>
          <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff661f] font-bold text-white" aria-hidden="true">3</span>
            <div><h3 class="font-semibold text-gray-900">Esperá la aprobación</h3>
              <p class="mt-1 leading-7">Un administrador revisa cada pedido para evitar suplantaciones. Cuando se resuelve, te avisamos por email.</p></div></li>
          <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff661f] font-bold text-white" aria-hidden="true">4</span>
            <div><h3 class="font-semibold text-gray-900">Cargá tu información</h3>
              <p class="mt-1 leading-7">Desde tu panel completás los formularios. Cada uno tiene una ayuda que explica qué es obligatorio y qué medidas de imagen conviene usar.</p></div></li>
          <li class="flex gap-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#ff661f] font-bold text-white" aria-hidden="true">5</span>
            <div><h3 class="font-semibold text-gray-900">Revisión y publicación</h3>
              <p class="mt-1 leading-7">Lo que cargás queda como borrador hasta que el equipo lo revisa y lo publica. Las cuentas verificadas publican directamente.</p></div></li>
        </ol>
      </section>

      <section aria-labelledby="que-puedo-cargar">
        <h2 id="que-puedo-cargar" class="text-xl font-semibold text-gray-900">¿Qué puedo cargar?</h2>
        <p class="mt-3 leading-7">Depende de las fichas que administrás:</p>
        <div class="mt-3 overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-900"><tr><th class="py-2 pr-4">Si administrás…</th><th class="py-2">Podés cargar</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
              <tr><td class="py-2 pr-4 font-medium">un artista o grupo</td><td class="py-2">su ficha y biografía, discos, canciones, noticias y eventos</td></tr>
              <tr><td class="py-2 pr-4 font-medium">un festival</td><td class="py-2">su ficha y sus eventos</td></tr>
              <tr><td class="py-2 pr-4 font-medium">ninguna ficha todavía</td><td class="py-2">solo avisos clasificados; para el resto, pedí primero tu ficha</td></tr>
            </tbody>
          </table>
        </div>
        <p class="mt-3 leading-7">Dentro de cada ficha, el <strong>Responsable</strong> puede sumar a otras personas como Editor (edita) o Miembro (solo lectura).</p>
      </section>

      <section aria-labelledby="imagenes">
        <h2 id="imagenes" class="text-xl font-semibold text-gray-900">Imágenes</h2>
        <p class="mt-3 leading-7">Aceptamos JPG, PNG o WebP de hasta 5 MB. Los formularios ajustan la imagen por vos: la reducen si es muy grande y te dejan recortarla si tiene otra proporción. Medidas sugeridas:</p>
        <ul class="mt-3 list-disc space-y-1 pl-6">
          <li><strong>Noticias, festivales y eventos:</strong> horizontal 16:9, desde 1600 × 900 px.</li>
          <li><strong>Artistas:</strong> retrato vertical o cuadrado, desde 800 × 1024 px.</li>
          <li><strong>Discos:</strong> cuadrada, desde 800 × 800 px.</li>
        </ul>
      </section>

      <section aria-labelledby="derechos">
        <h2 id="derechos" class="text-xl font-semibold text-gray-900">Derechos y contenido</h2>
        <ul class="mt-3 list-disc space-y-1 pl-6 leading-7">
          <li>Cargá solo contenido propio o con permiso de quien lo posee (textos, fotos, audios).</li>
          <li>No publicamos letras completas de canciones sin autorización de sus titulares.</li>
          <li>Indicá la fuente cuando la información no sea tuya. No inventamos ni completamos datos que no estén verificados.</li>
          <li>El equipo puede editar o rechazar lo que no cumpla estas reglas. Más detalles en las <a href="{{ route('legal.terms') }}" class="font-semibold text-[#ff661f] hover:underline">condiciones del servicio</a>.</li>
        </ul>
      </section>

      <section aria-labelledby="preguntas">
        <h2 id="preguntas" class="text-xl font-semibold text-gray-900">Preguntas frecuentes</h2>
        <div class="mt-3 divide-y divide-gray-200 rounded-lg border border-gray-200">
          <details class="p-4"><summary class="cursor-pointer font-semibold text-gray-900">¿Tiene costo?</summary>
            <p class="mt-2 leading-7">No. Crear la cuenta y cargar información es gratis.</p></details>
          <details class="p-4"><summary class="cursor-pointer font-semibold text-gray-900">¿Por qué mi contenido no aparece enseguida?</summary>
            <p class="mt-2 leading-7">Porque pasa por una revisión editorial. Mientras tanto figura como borrador en tu panel.</p></details>
          <details class="p-4"><summary class="cursor-pointer font-semibold text-gray-900">¿Qué es una cuenta verificada?</summary>
            <p class="mt-2 leading-7">Es una cuenta de confianza que publica sin revisión previa. La otorga el equipo editorial.</p></details>
          <details class="p-4"><summary class="cursor-pointer font-semibold text-gray-900">No encuentro mi artista o festival</summary>
            <p class="mt-2 leading-7">En «Mis fichas» usá «Pedí que la creemos». Un administrador la revisa y, si corresponde, la crea y te la asigna.</p></details>
          <details class="p-4"><summary class="cursor-pointer font-semibold text-gray-900">Quiero corregir un dato puntual</summary>
            <p class="mt-2 leading-7">Escribinos desde <a href="{{ route('contacto') }}" class="font-semibold text-[#ff661f] hover:underline">Contacto</a> indicando la página y el dato correcto.</p></details>
        </div>
      </section>
    </div>
  </article>
@endsection
