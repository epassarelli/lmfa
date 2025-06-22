@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <!-- Columna principal -->

  <h1 class="text-3xl font-bold mb-4">Shows de {{ $interprete->interprete }}</h1>

  <p class="text-gray-700 text-lg mb-6">
    Consulta la cartelera de shows y eventos de {{ $interprete->interprete }}, y no te pierdas la oportunidad de ver
    en vivo a uno de los mayores exponentes del folklore argentino. Aquí encontrarás fechas, lugares y detalles de
    todas sus presentaciones. Acompaña a {{ $interprete->interprete }} en sus giras y disfruta de una experiencia
    única con su música en directo.
  </p>

  @if ($shows->isEmpty())
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
      <p class="font-bold">Atención</p>
      <p>No hay shows disponibles para {{ $interprete->interprete }} aún.</p>
    </div>
  @else
    <div class="space-y-6">
      @foreach ($shows as $evento)
        <div class="flex flex-col md:flex-row bg-white shadow rounded overflow-hidden">
          <!-- Fecha -->
          <div class="w-full md:w-1/5 bg-gray-100 flex flex-col items-center justify-center p-4 text-center">
            <h5 class="text-2xl font-bold">{{ date('d', strtotime($evento->fecha)) }}</h5>
            <p class="uppercase text-sm">{{ date('M', strtotime($evento->fecha)) }}</p>
            <p class="text-gray-600">{{ date('Y', strtotime($evento->fecha)) }}</p>
          </div>

          <!-- Detalle -->
          <div class="w-full md:w-4/5 p-4">
            <div class="flex items-center gap-3 mb-3">
              <img src="{{ asset('storage/interpretes/' . $evento->interprete->foto) }}"
                alt="{{ $evento->interprete->interprete }}"
                class="w-12 h-12 rounded-full object-cover border border-gray-300">
              <h5 class="text-lg font-semibold">{{ $evento->interprete->interprete }}</h5>
            </div>
            <h6 class="text-gray-700 font-medium mb-2"><strong>Show:</strong> {{ $evento->show }}</h6>
            <p class="text-gray-700 mb-1"><strong>Detalles:</strong> {!! $evento->detalle !!}</p>
            <p class="text-gray-700 mb-1"><strong>Lugar:</strong> {{ $evento->lugar }}</p>
            <p class="text-gray-600">{{ $evento->descripcion }}</p>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  {{-- @include('layouts.partials.select-interprete') --}}



@endsection

@section('sidebar')

  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])

  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
