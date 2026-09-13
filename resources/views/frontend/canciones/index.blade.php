@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Letras de Canciones Folklóricas</h1>
    <p class="text-base text-gray-700">
      Letras completas de canciones del folklore argentino, organizadas por artista y disco para consulta rápida. Ya reunimos <strong>{{ $totalCanciones }}</strong> letras en el cancionero.
    </p>
  </section>

  @if ($masVisitadas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Canciones con más visitas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($masVisitadas as $letra)
          <x-letra-card :letra="$letra" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($ultimasAgregadas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimas letras agregadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($ultimasAgregadas as $letra)
          <x-letra-card :letra="$letra" />
        @endforeach
      </div>
    </section>
  @endif

  <section class="mb-12">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Todas las letras (A-Z)</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
      @foreach ($canciones as $letra)
        <x-letra-card :letra="$letra" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$canciones" />

    <x-alpha-filter
      class="mt-8"
      title="Buscar por orden alfabético"
      description="Encontrá fácilmente una canción de folklore argentino utilizando nuestro índice alfabético."
      route-name="canciones.letra"
      :letters="range('a', 'z')"
    />
  </section>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />

@endsection
