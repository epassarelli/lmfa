@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <div class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Discografías del Folklore Argentino</h1>
    <p class="text-gray-700">
      Álbumes y obras de los artistas del folklore argentino. Ya sumamos <strong>{{ $totalDiscos }}</strong> discos en el catálogo.
    </p>
  </div>

  @if ($masEscuchados->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Más escuchados</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($masEscuchados as $disco)
          <x-disco-card :disco="$disco" />
        @endforeach
      </div>
    </section>
  @endif

  <section class="mb-8">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimos discos agregados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      @foreach ($discos as $disco)
        <x-disco-card :disco="$disco" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$discos" />
  </section>

  <div class="bg-white p-2 rounded shadow-sm my-4">
    {{-- Texto final --}}
    <div class="space-y-4 text-gray-700 leading-relaxed text-lg">
      <p>
        Mantente al día con los últimos discos agregados al portal de folklore argentino. Aquí encontrarás las novedades
        más recientes en la discografía de nuestros artistas, incluyendo los lanzamientos más frescos que enriquecen la
        tradición de nuestra música folklórica. No te pierdas la oportunidad de descubrir nuevos sonidos y talentos
        emergentes en el mundo del folklore argentino.
      </p>
      <p>
        Bienvenidos a nuestra sección de discografías del folklore argentino, donde encontrarás una completa
        colección de álbumes y grabaciones de los más destacados artistas y cantantes de la música folklórica argentina.
        Explora las obras maestras que han definido y enriquecido este género musical, desde los clásicos inmortales
        hasta los lanzamientos más recientes.
      </p>
      <p>
        Cada discografía está detalladamente organizada para ofrecerte información sobre los álbumes, incluyendo listas
        de canciones, fechas de lanzamiento y colaboraciones especiales. Descubre la evolución musical de tus artistas
        favoritos a través de sus producciones discográficas, y sumérgete en la riqueza y diversidad de la música
        folklórica
        argentina.
      </p>
      <p>
        Nuestra sección de discografías es el recurso definitivo para los amantes del folklore que desean conocer más
        sobre la trayectoria musical de sus ídolos y explorar nuevos sonidos. Ya sea que estés buscando un álbum en
        particular o simplemente quieras descubrir más sobre la historia musical del folklore argentino, aquí encontrarás
        todo lo que necesitas.
      </p>
    </div>
  </div>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  @if ($noticiasMasLeidas->isNotEmpty())
    <x-sidebar.top-news :noticias="$noticiasMasLeidas" />
  @endif

@endsection
