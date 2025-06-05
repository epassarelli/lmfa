@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @php
      $bloques = [
          'Noticias de folklore argentino' => $ultimas,
          // 'Actualidad' => $actualidad,
          // 'Festivales' => $festivales,
          // 'Lanzamientos' => $lanzamientos,
          // 'Cartelera' => $cartelera,
      ];
    @endphp

    @foreach ($bloques as $titulo => $noticias)
      <section class="mb-12">
        <h1 class="text-3xl font-bold mb-6">{{ $titulo }}</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($noticias as $noticia)
            <x-noticia-card :noticia="$noticia" />
          @endforeach
        </div>
      </section>
    @endforeach

    <section class="prose max-w-none mt-12">
      <p>
        Mantente al día con las últimas noticias del folklore argentino en nuestra sección dedicada a mantenerte informado
        sobre todo lo relacionado con la música folklórica de nuestro país.
        Aquí encontrarás las actualizaciones más recientes, incluyendo lanzamientos de nuevos álbumes, giras de conciertos
        y eventos especiales que destacan lo mejor del folklore argentino.
      </p>
      <p>
        Descubre entrevistas exclusivas con tus artistas y cantantes favoritos, reportajes en profundidad sobre tendencias
        y movimientos dentro de la escena folklórica, y análisis detallados sobre la evolución de este género musical.
        Nuestra cobertura incluye tanto a los grandes íconos del folklore como a los nuevos talentos emergentes que están
        dando forma al futuro de la música tradicional argentina.
      </p>
      <p>
        No te pierdas ninguna novedad del mundo del folklore argentino. Desde festivales y shows hasta proyectos
        colaborativos y homenajes,
        nuestra sección de noticias te mantendrá conectado con todo lo que está ocurriendo en el vibrante panorama de la
        música folklórica argentina.
      </p>
    </section>

  </div>

@endsection
