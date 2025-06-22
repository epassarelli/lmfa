@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
    @foreach ($visitados as $festival)
      <x-festival-card :festival="$festival" />
    @endforeach
  </div>


  {{-- ÚLTIMOS FESTIVALES AGREGADOS --}}
  {{-- <section class="mb-16">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($visitados as $festival)
        <x-festival-card :festival="$festival" />
      @endforeach
    </div>
  </section> --}}

  {{-- TEXTO INFORMATIVO --}}
  <h1 class="text-xl font-semibold mb-8">Festivales y Fiestas tradicionales del Folklore Argentino</h1>

  {{-- FESTIVALES MÁS VISITADOS --}}
  <p class="text-lg text-gray-700 mb-6">
    Descubre los festivales de folklore argentino que han capturado la atención de los amantes de la música y la
    cultura.
    Estos eventos son los más populares en nuestro portal, atrayendo a miles de visitantes cada año.
  </p>
  <p>
    Descubre la magia de las fiestas y festivales folklóricos en nuestra sección dedicada a los eventos que celebran
    la música folklórica argentina. Aquí encontrarás información completa sobre los festivales más importantes,
    las fiestas tradicionales y los eventos culturales que destacan la riqueza y diversidad del folklore argentino.
  </p>
  <p>
    Cada festival y fiesta está detalladamente descrito, ofreciendo información sobre las fechas, ubicaciones,
    actividades y artistas participantes.
    Desde festivales nacionales que atraen a miles de visitantes hasta celebraciones locales llenas de encanto y
    autenticidad.
  </p>
  <p>
    Sumérgete en el espíritu festivo del folklore argentino y únete a las celebraciones que honran nuestras
    tradiciones y cultura.
    Nuestra sección es tu recurso definitivo para conocer los próximos eventos y vivir la alegría del folklore.
  </p>


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
