@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  @php
    $bloques = [
        'Noticias de folklore argentino' => $ultimas,
    ];
  @endphp

  @foreach ($bloques as $titulo => $noticias)
    <section class="mb-12">
      {{-- <h1 class="text-2xl font-semibold mb-6">{{ $titulo }}</h1> --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
        @foreach ($noticias as $noticia)
          <x-noticia-card :noticia="$noticia" />
        @endforeach
      </div>
    </section>
  @endforeach

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold  mb-4 border-b-2 border-[#ff661f]">Noticias del Folklore Argentino</h1>
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

  {{-- <!-- Categorías -->
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h3 class="text-xl font-semibold text-gray-800  mb-4 border-b-2 border-[#ff661f]">Categorías</h3>
    <div class="flex flex-wrap gap-2">
      @foreach ($categorias as $cat)
        <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
          class="bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300">{{ $cat->nombre }}</a>
      @endforeach
    </div>
  </section> --}}

  
  <x-sidebar.social-links />

  <x-sidebar.donate />
  
@endsection
