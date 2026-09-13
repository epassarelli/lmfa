@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Mitos y leyendas argentinas</h1>
    <p class="text-base text-gray-700">
      Historias, mitos y leyendas urbanas transmitidas de generación en generación en distintas regiones de Argentina. Ya reunimos <strong>{{ $totalMitos }}</strong> mitos y leyendas en el portal.
    </p>
  </section>

  @if ($visitados->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Leyendas más visitadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitados as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($ultimos->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimos mitos y leyendas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimos as $mito)
          <x-mito-card :mito="$mito" />
        @endforeach
      </div>
    </section>
  @endif

  <x-alpha-filter
    class="mb-4"
    title="Buscar por orden alfabético"
    description="Encontrá fácilmente tus mitos y leyendas favoritos del folklore argentino utilizando nuestro índice alfabético."
    route-name="mitos.letra"
    :letters="$alphabet"
  />
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
