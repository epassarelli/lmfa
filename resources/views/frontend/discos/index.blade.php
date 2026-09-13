@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Discografías del Folklore Argentino</h1>
    <p class="text-base text-gray-700">
      Álbumes, sencillos y obras completas de artistas del folklore argentino, organizados por intérprete y año. Ya sumamos <strong>{{ $totalDiscos }}</strong> discos en el catálogo.
    </p>
  </section>

  @if ($masEscuchados->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Más escuchados</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($masEscuchados as $disco)
          <x-disco-card :disco="$disco" />
        @endforeach
      </div>
    </section>
  @endif

  <section class="mb-8">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimos discos agregados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      @foreach ($discos as $disco)
        <x-disco-card :disco="$disco" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$discos" />
  </section>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  @if ($noticiasMasLeidas->isNotEmpty())
    <x-sidebar.top-news :noticias="$noticiasMasLeidas" />
  @endif

@endsection
