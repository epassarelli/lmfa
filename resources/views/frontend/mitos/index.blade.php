@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold mb-8">Mitos y leyendas argentinas</h1>

    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Leyendas urbanas más visitadas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Explora los mitos y leyendas urbanas del folklore argentino que han capturado la imaginación de nuestros visitantes...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    <x-alpha-filter
      class="mb-16"
      title="Buscar por Orden Alfabético"
      description="Encuentra fácilmente tus mitos y leyendas favoritos del folklore argentino utilizando nuestro índice alfabético..."
      route-name="mitos.letra"
      :letters="$alphabet"
    />

    <section class="mb-16">
      <h2 class="text-2xl font-semibold mb-2">Últimos mitos y leyendas</h2>
      <p class="text-lg text-gray-700 mb-6">
        Mantente al día con las nuevas adiciones a nuestro repertorio de mitos y leyendas urbanas del folklore argentino...
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimos as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>

    <section>
      <p class="text-lg text-gray-700 mb-4">Bienvenidos a nuestra sección de mitos y leyendas tradicionales...</p>
      <p class="text-lg text-gray-700 mb-4">Cada mito y leyenda está narrado con detalle...</p>
      <p class="text-lg text-gray-700">Sumérgete en el mundo mágico de los mitos y leyendas y conecta con las raíces profundas de nuestra identidad cultural...</p>
    </section>
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
