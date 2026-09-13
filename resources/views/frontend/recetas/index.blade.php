@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Recetas de comidas típicas argentinas</h1>
    <p class="text-base text-gray-700">
      Recetas paso a paso de la cocina tradicional argentina, con ingredientes e instrucciones claras para cocinar en casa. Ya reunimos <strong>{{ $totalRecetas }}</strong> recetas en el portal.
    </p>
  </section>

  @if ($visitadas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Recetas más visitadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($visitadas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($ultimas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimas recetas agregadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimas as $receta)
          <x-receta-card :receta="$receta" />
        @endforeach
      </div>
    </section>
  @endif

  <x-alpha-filter
    class="mb-4"
    title="Buscar por orden alfabético"
    description="Encontrá fácilmente tus recetas favoritas utilizando nuestro índice alfabético."
    route-name="comidas.letra"
    :letters="$alphabet"
  />
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
@endsection
