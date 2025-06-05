@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto px-4 py-10">

    <h1 class="text-3xl font-bold mb-8">Festivales y Fiestas tradicionales del Folklore Argentino</h1>

    {{-- FESTIVALES MÁS VISITADOS --}}
    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Festivales Más Visitados</h2>
      <p class="text-lg text-gray-700 mb-6">
        Descubre los festivales de folklore argentino que han capturado la atención de los amantes de la música y la
        cultura.
        Estos eventos son los más populares en nuestro portal, atrayendo a miles de visitantes cada año.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $festival)
          <x-festival-card :festival="$festival" />
        @endforeach
      </div>
    </section>

    {{-- ÚLTIMOS FESTIVALES AGREGADOS --}}
    <section class="mb-16">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $festival)
          <x-festival-card :festival="$festival" />
        @endforeach
      </div>
    </section>

    {{-- TEXTO INFORMATIVO --}}
    <section class="prose prose-lg max-w-none">
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
    </section>
  </div>

@endsection
