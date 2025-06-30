@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <h1 class="text-3xl font-bold mb-4 text-gray-900">Letras de Canciones Folklóricas</h1>
  <p class="text-lg text-gray-700 mb-8">Bienvenidos a nuestra sección de letras de canciones folklóricas</p>

  {{-- Canciones más visitadas --}}
  <section class="mb-16">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Canciones folklóricas con más visitas</h2>
    <p class="text-gray-700 mb-6">
      Descubre las letras de canciones folklóricas más visitadas por nuestros usuarios. Explora las canciones que han
      capturado los corazones de los amantes del folklore argentino, desde clásicos inolvidables hasta nuevos éxitos.
      Sumérgete en las palabras que reflejan la rica tradición cultural de nuestra música y conecta con los temas más
      populares de la escena folklórica.
    </p>

    {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($visitadas as $letra)
        <x-letra-card :letra="$letra" />
      @endforeach
    </div> --}}
  </section>

  {{-- Últimas letras agregadas --}}
  <section class="mb-16">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Últimas letras de canciones agregadas</h2>
    <p class="text-gray-700 mb-6">
      Mantente al día con las últimas letras de canciones folklóricas agregadas a nuestro portal. Descubre nuevas
      incorporaciones a nuestra colección y disfruta de lo más reciente del folklore argentino. Desde lanzamientos
      recientes hasta joyas redescubiertas, encuentra las palabras y melodías que están enriqueciendo la tradición de
      nuestra música popular.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
      @foreach ($ultimas as $letra)
        <x-letra-card :letra="$letra" />
      @endforeach
    </div>
  </section>

  {{-- Texto final --}}
  <div class="space-y-6 text-lg text-gray-700 leading-relaxed">
    <p>
      En nuestra sección de letras de canciones folklóricas podrás explorar las letras
      de las canciones más emblemáticas de la música folklórica argentina. Desde clásicos inmortales hasta las
      composiciones contemporáneas, aquí encontrarás una vasta colección de letras que reflejan la riqueza y diversidad
      de nuestro folklore.
    </p>
    <p>
      Cada letra está cuidadosamente transcrita y organizada por artista y álbum, facilitándote la búsqueda
      de tus canciones favoritas. Descubre el significado profundo y las historias detrás de cada letra, y cómo han
      influido en
      la cultura y la tradición del folklore argentino. Nuestra colección incluye letras de los artistas y cantantes más
      destacados, así como de talentos emergentes que están dando forma al futuro de la música folklórica.
    </p>
    <p>
      Sumérgete en las palabras que han dado vida a la música folklórica argentina y conecta con las emociones y relatos
      que cada canción transmite. Ya sea que estés buscando una letra específica o simplemente quieras explorar el vasto
      repertorio de nuestro folklore, nuestra sección de letras de canciones es el lugar perfecto para ti.
    </p>
  </div>


@endsection

@section('sidebar')

  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
