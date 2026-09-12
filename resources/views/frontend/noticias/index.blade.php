@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold mb-2 border-b-2 border-[#ff661f] pb-2">Noticias del Folklore Argentino</h1>
    <p class="text-base text-gray-700">
      Actualidad, lanzamientos y agenda del folklore argentino. Ya publicamos <strong>{{ $totalNoticias }}</strong> noticias en el portal.
    </p>
  </section>

  @if ($categorias->isNotEmpty())
    <section class="mb-4">
      <div class="flex flex-wrap gap-2">
        @foreach ($categorias as $cat)
          <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
            class="bg-white border text-sm px-3 py-1 rounded-full hover:bg-gray-100 hover:border-[#ff661f] transition-colors">{{ $cat->nombre }}</a>
        @endforeach
      </div>
    </section>
  @endif

  @if ($masLeidas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Más leídas</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($masLeidas as $noticia)
          <x-noticia-card :noticia="$noticia" />
        @endforeach
      </div>
    </section>
  @endif

  <section class="mb-12">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimas noticias</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
      @foreach ($ultimas as $noticia)
        <x-noticia-card :noticia="$noticia" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$ultimas" />
  </section>

  <section class="bg-white p-2 rounded shadow-sm mb-4 cv-auto">
    <p class="text-base mb-2">
      Mantente al día con las últimas noticias del folklore argentino en nuestra sección dedicada a mantenerte informado
      sobre todo lo relacionado con la música folklórica de nuestro país.
      Aquí encontrarás las actualizaciones más recientes, incluyendo lanzamientos de nuevos álbumes, giras de conciertos y
      eventos especiales que destacan lo mejor del folklore argentino.
    </p>
    <p class="text-base mb-2">
      Descubre entrevistas exclusivas con tus artistas y cantantes favoritos, reportajes en profundidad sobre tendencias y
      movimientos dentro de la escena folklórica, y análisis detallados sobre la evolución de este género musical.
      Nuestra cobertura incluye tanto a los grandes íconos del folklore como a los nuevos talentos emergentes que están
      dando forma al futuro de la música tradicional argentina.
    </p>
    <p class="text-base mb-2">
      No te pierdas ninguna novedad del mundo del folklore argentino. Desde festivales y shows hasta proyectos
      colaborativos y homenajes, nuestra sección de noticias te mantendrá conectado con todo lo que está ocurriendo en el
      vibrante panorama de la música folklórica argentina.
    </p>
  </section>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />

  @if(isset($ultimasSidebar) && $ultimasSidebar->count() > 0)
    <x-sidebar.card-noticias :noticias="$ultimasSidebar" />
  @endif

  <x-sidebar.social-links />

  <x-sidebar.donate />

@endsection
