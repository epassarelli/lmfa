@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  {{-- CONTENIDO PRINCIPAL --}}


  <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
    class="rounded shadow-lg w-full object-cover max-h-[500px]">
  
    <div class="bg-white p-2">


    <h1 class="text-3xl font-bold mb-4">{{ $festival->titulo }}</h1>

    <div class="prose max-w-none mb-6">
      {!! $festival->detalle !!}
    </div>

    <p class="text-sm text-gray-500">Visitas: {{ number_format($festival->visitas, 0, '', '.') }}</p>
  </div>
  {{-- button pr ir l de ls provincis --}}
  <div class="more"></div>

  {{-- Muestro ls redes p compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$festival->titulo" :url="Request::url()" />
  </div>

  {{-- comments --}}
  <div class="comments"></div>

  {{-- relted --}}
  <div class="related"></div>

@endsection

@section('sidebar')

  {{-- <div class="grid grid-cols-1 mb-4">
    @foreach ($ultimos_festivales as $ultimo)
      <x-festival-card :festival="$ultimo" />
    @endforeach
  </div> --}}


  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
