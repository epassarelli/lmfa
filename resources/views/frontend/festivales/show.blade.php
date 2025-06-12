@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- CONTENIDO PRINCIPAL --}}
      <div class="w-full lg:w-2/3">
        <h1 class="text-3xl font-bold mb-4">{{ $festival->titulo }}</h1>

        <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
          class="mb-6 rounded shadow-lg w-full object-cover max-h-[500px]">

        <div class="prose max-w-none mb-6">
          {!! $festival->detalle !!}
        </div>

        <p class="text-sm text-gray-500">Visitas: {{ number_format($festival->visitas, 0, '', '.') }}</p>

        {{-- button pr ir l de ls provincis --}}
        <div class="more"></div>

        {{-- comprtir en redes --}}
        <div class="share"></div>

        {{-- comments --}}
        <div class="comments"></div>

        {{-- relted --}}
        <div class="related"></div>

      </div>

      {{-- SIDEBAR: ÚLTIMOS FESTIVALES --}}
      <aside class="w-full lg:w-1/3">
        <div class="bg-white shadow rounded p-6">
          <h3 class="text-xl font-semibold mb-4">Últimos Festivales</h3>

          @foreach ($ultimos_festivales as $ultimo_festival)
            <x-festival-card :festival="$ultimo_festival" />
          @endforeach

        </div>
      </aside>

    </div>
  </div>

@endsection
