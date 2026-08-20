@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)

@section('content')
  <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8">
    <header class="mb-8 border-b border-gray-200 pb-6">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Informacion legal</p>
      <h1 class="mt-3 text-3xl font-bold text-gray-900">Condiciones del servicio</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-gray-600">
        Estas condiciones regulan el acceso y uso del portal Mi Folklore Argentino, sus contenidos editoriales y sus herramientas de autenticacion y colaboracion.
      </p>
    </header>

    <div class="space-y-8 text-gray-700">
      <section>
        <h2 class="text-xl font-semibold text-gray-900">1. Identificacion y finalidad</h2>
        <p class="mt-3 leading-7">
          Mi Folklore Argentino es un portal editorial dedicado a la difusion de noticias, artistas, canciones, festivales, recetas, mitos y otros contenidos vinculados al folklore argentino.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">2. Aceptacion de las condiciones</h2>
        <p class="mt-3 leading-7">
          El acceso y uso del sitio implica la aceptacion de estas condiciones. Si no estas de acuerdo con ellas, debes abstenerte de utilizar las funciones autenticadas o de enviar contenido al portal.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">3. Uso permitido</h2>
        <ul class="mt-3 space-y-2 leading-7">
          <li>Usar el portal con fines licitos y respetando la normativa aplicable.</li>
          <li>No intentar acceder sin autorizacion a cuentas, paneles o datos de terceros.</li>
          <li>No cargar contenido ilegal, enganoso, difamatorio o que infrinja derechos de terceros.</li>
        </ul>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">4. Cuentas y autenticacion mediante terceros</h2>
        <p class="mt-3 leading-7">
          Algunas funciones del portal pueden requerir autenticacion. El acceso puede realizarse mediante proveedores externos como Facebook o Google, segun la configuracion activa del sitio. Cada usuario es responsable del uso de su cuenta y de la informacion que mantiene actualizada.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">5. Propiedad intelectual</h2>
        <p class="mt-3 leading-7">
          Los contenidos editoriales, la seleccion, la organizacion del material y los recursos graficos del portal pertenecen a Mi Folklore Argentino o a sus respectivos titulares. El uso no autorizado de contenidos puede infringir derechos de autor, marca u otros derechos aplicables.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">6. Contenido editorial y contenido aportado por usuarios</h2>
        <p class="mt-3 leading-7">
          El portal puede publicar tanto contenido editorial propio como materiales enviados por colaboradores o usuarios. Mi Folklore Argentino puede moderar, editar, rechazar o retirar publicaciones cuando resulte necesario para preservar la calidad editorial, la legalidad o la seguridad del sitio.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">7. Enlaces y servicios de terceros</h2>
        <p class="mt-3 leading-7">
          El sitio puede contener enlaces a plataformas externas o depender de servicios de terceros para autenticacion, medicion, correo o integraciones. Mi Folklore Argentino no controla de manera integral esos servicios y cada uno se rige por sus propias politicas.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">8. Limitacion de responsabilidad</h2>
        <p class="mt-3 leading-7">
          Aunque se procura mantener la informacion actualizada y el sitio disponible, Mi Folklore Argentino no garantiza ausencia absoluta de errores, interrupciones o indisponibilidades temporales. En la medida permitida por la ley, la responsabilidad del portal se limita a la prestacion razonable del servicio.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">9. Cambios en estas condiciones</h2>
        <p class="mt-3 leading-7">
          Estas condiciones pueden actualizarse para reflejar cambios operativos, editoriales, tecnicos o legales. La version vigente sera siempre la publicada en esta misma URL.
        </p>
      </section>

      <section>
        <h2 class="text-xl font-semibold text-gray-900">10. Contacto</h2>
        <p class="mt-3 leading-7">
          Para consultas sobre estas condiciones o sobre el funcionamiento del portal, puedes escribir a
          <a class="font-semibold text-[#ff661f] hover:underline" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.
        </p>
      </section>
    </div>

    <footer class="mt-10 border-t border-gray-200 pt-6 text-sm text-gray-500">
      Ultima actualizacion: {{ $lastUpdated }}
    </footer>
  </article>
@endsection
