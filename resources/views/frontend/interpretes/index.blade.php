@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Biografías de artistas del folklore argentino</h1>
    <p class="text-base text-gray-700">
      Biografía, discografía y trayectoria de cantantes, dúos y conjuntos del folklore argentino. Ya reunimos <strong>{{ $total }}</strong> artistas en el portal.
    </p>
  </section>

  <section class="bg-white p-4 rounded shadow-sm mb-6">
    <form method="GET" action="{{ route('interpretes.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
      <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Buscar artista por nombre</label>
        <input id="q" type="search" name="q" value="{{ $search }}"
          class="w-full rounded-lg border-gray-300 focus:border-[#ff661f] focus:ring-[#ff661f]"
          placeholder="Ej: Los Chalchaleros">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#ff661f] px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition">Buscar</button>
        @if ($search !== '')
          <a href="{{ route('interpretes.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:border-gray-400 transition">Limpiar</a>
        @endif
      </div>
    </form>
  </section>

  @if ($search === '')
    @if ($recientes->isNotEmpty())
      <section class="mb-8">
        <h2 class="text-lg font-semibold mb-3 text-gray-800">Agregados recientemente</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          @foreach ($recientes as $interprete)
            <x-biografia-card :interprete="$interprete" />
          @endforeach
        </div>
      </section>
    @endif

    @if ($masLeidos->isNotEmpty())
      <section class="mb-8">
        <h2 class="text-lg font-semibold mb-3 text-gray-800">Los más leídos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          @foreach ($masLeidos as $interprete)
            <x-biografia-card :interprete="$interprete" />
          @endforeach
        </div>
      </section>
    @endif
  @endif

  <section class="mb-8">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">
      @if ($search !== '')
        Resultados para "{{ $search }}"
      @else
        Todos los artistas (A-Z)
      @endif
    </h2>

    @if ($interpretes->isEmpty())
      <p class="text-gray-600 bg-white p-4 rounded shadow-sm">No encontramos artistas que coincidan con tu búsqueda.</p>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ($interpretes as $interprete)
          <x-biografia-card :interprete="$interprete" />
        @endforeach
      </div>

      <x-public-pagination :paginator="$interpretes" />
    @endif
  </section>

  <x-alpha-filter
    class="mb-4"
    title="Buscar por orden alfabético"
    description="Encontrá fácilmente a tu intérprete favorito de folklore argentino."
    route-name="interpretes.letra"
    :letters="$alphabet"
  />

@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.donate />
  <x-sidebar.social-links />
@endsection
