@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto px-4 mt-10">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Discografías del Folklore Argentino</h1>

    {{-- Más Visitados --}}
    <section class="mb-10">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Discos folklóricos Más Visitados</h2>
      <p class="text-gray-700 mb-6 leading-relaxed">
        Descubre los discos folklóricos más visitados y populares del folklore argentino. Estos álbumes han capturado la
        atención y el corazón de los amantes de la música folklórica, destacándose por su calidad y autenticidad.
        Explora los trabajos más escuchados de los artistas más influyentes en el folklore argentino y sumérgete en las
        melodías que definen nuestra rica herencia cultural.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-6">
        @foreach ($visitados as $disco)
          <x-disco-card :disco="$disco" />
        @endforeach
      </div>
    </section>

    {{-- Últimos álbumes --}}
    <section class="mb-10">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Últimos álbumes del folklore agregados</h2>
      <p class="text-gray-700 mb-6 leading-relaxed">
        Mantente al día con los últimos discos agregados al portal de folklore argentino. Aquí encontrarás las novedades
        más recientes en la discografía de nuestros artistas, incluyendo los lanzamientos más frescos que enriquecen la
        tradición de nuestra música folklórica. No te pierdas la oportunidad de descubrir nuevos sonidos y talentos
        emergentes en el mundo del folklore argentino.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($ultimos as $disco)
          <x-disco-card :disco="$disco" />
        @endforeach
      </div>
    </section>

    {{-- Texto final --}}
    <div class="space-y-4 text-gray-700 leading-relaxed text-lg">
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
        sobre
        la trayectoria musical de sus ídolos y explorar nuevos sonidos. Ya sea que estés buscando un álbum en particular o
        simplemente quieras descubrir más sobre la historia musical del folklore argentino, aquí encontrarás todo lo que
        necesitas.
      </p>
    </div>

  </div>

@endsection
