@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <!-- Más visitados -->

  {{-- <h2 class="text-xl font-semibold mb-2">Intérpretes Más Visitados</h2> --}}
  {{-- <p class="text-base mb-6">
        Explora los perfiles de los intérpretes de folklore argentino que han capturado la mayor atención de nuestros
        visitantes...
      </p> --}}

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    @foreach ($visitados as $interprete)
      <x-biografia-card :interprete="$interprete" />
    @endforeach
  </div>

  <section class="bg-white p-2 rounded shadow-sm mt-4 mb-4">
    <h1 class="text-xl font-semibold mb-4 border-b-2 border-[#ff661f]">Biografías de artistas folklóricos más visitadas</h1>


    <!-- Índice alfabético -->
    {{-- <section class="mb-12">
      <h2 class="text-xl font-semibold mb-2">Buscar por Orden Alfabético</h2>
      <p class="text-base text-gray-300 mb-4">
        Encuentra fácilmente a tu intérprete favorito de folklore argentino utilizando nuestro índice alfabético...
      </p>

      <div class="border-t border-gray-600 my-4"></div>
      <ul class="flex flex-wrap justify-center gap-2 text-sm">
        @foreach (range('a', 'z') as $letra)
          <li>
            <a href="{{ route('interprete.letra', $letra) }}"
              class="px-3 py-1 bg-gray-700 rounded hover:bg-yellow-500 hover:text-black transition">
              {{ $letra }}
            </a>
          </li>
        @endforeach
      </ul>
      <div class="border-t border-gray-600 my-4"></div>
    </section> --}}

    <!-- Últimos agregados -->
    {{-- <section class="mb-12">
      <h2 class="text-xl font-semibold mb-2">Últimos Intérpretes Agregados</h2>
      <p class="text-base text-gray-300 mb-6">
        Descubre los nuevos talentos y las voces emergentes del folklore argentino...
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($ultimos as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>
    </section> --}}

    <!-- Textos finales -->

    <p>Bienvenidos a nuestra sección dedicada a los intérpretes de folklore argentino...</p>
    <p>Nuestra colección incluye biografías completas, letras de canciones emblemáticas...</p>
    <p>Ya seas un apasionado del folklore, un investigador o simplemente un amante...</p>
    <p>Explora la rica diversidad de artistas y cantantes que han dado vida a la música folklórica argentina...</p>

  </section>

@endsection


@section('sidebar')
  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.donate />
  <x-sidebar.social-links />
  {{-- 
      <x-sidebar.top-news :noticias="$noticiasMasLeidas" />
      <x-sidebar.upcoming-shows :eventos="$eventosSidebar" />
      <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}
@endsection
