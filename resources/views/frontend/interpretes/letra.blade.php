@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4">Intérpretes de folklore argentino con la letra {{ strtoupper($letra) }}</h1>
    <h2 class="text-xl font-semibold mb-4">Filtrar artistas por letra</h2>

    <div class="space-y-4 text-lg text-gray-700">
      <p>Encontrá artistas del folklore argentino ordenados alfabéticamente para navegar más rápido por la sección de intérpretes.</p>
      <p>Esta vista reúne biografías publicadas cuyos nombres comienzan con la letra seleccionada.</p>
    </div>

    <div class="mt-12">
      <h2 class="text-2xl font-bold mb-4">Intérpretes con la letra {{ strtoupper($letra) }}</h2>
      <p class="text-lg text-gray-700 mb-4">Explorá los perfiles disponibles para esta letra.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($interpretes as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>

      @if ($interpretes->isEmpty())
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
          No encontramos intérpretes publicados que comiencen con la letra {{ strtoupper($letra) }}.
        </div>
      @endif
    </div>

    <div class="mt-8 mb-8">
      {{ $interpretes->links() }}
    </div>

    <x-alpha-filter
      class="mt-16"
      title="Buscar por orden alfabético"
      description="Usá el índice alfabético para cambiar de letra."
      route-name="interpretes.letra"
      :letters="$alphabet"
      :active-letter="$letra"
    />
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
