@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <section class="py-10 px-4 max-w-7xl mx-auto">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- BLOQUE PRINCIPAL --}}
      <div class="w-full lg:w-2/3">

        <h1 class="text-3xl font-bold mb-6">Cartelera</h1>

        @if ($shows->isEmpty())
          <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
            <p>No hay shows disponibles aún.</p>
          </div>
        @else
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($shows as $show)
              <x-show-card :show="$show" />
            @endforeach
          </div>
        @endif
      </div>

      {{-- SIDEBAR --}}
      <aside class="w-full lg:w-1/3">
        <div class="bg-white shadow p-6 rounded">
          <h3 class="text-xl font-semibold mb-4">Eventos destacados</h3>
          {{-- Aquí podrías mostrar manualmente eventos con más visitas, próximos, etc. --}}
          <p class="text-gray-600">Muy pronto disponibles.</p>
        </div>
      </aside>

    </div>
  </section>

@endsection
