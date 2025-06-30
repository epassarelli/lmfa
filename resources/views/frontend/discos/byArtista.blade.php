@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  {{-- Contenido principal --}}
  <h1 class="text-2xl font-bold text-gray-900 mb-4">Discografía de {{ $interprete->interprete }}</h1>
  <p class="text-lg text-gray-700 mb-6">
    Descubre la discografía completa de {{ $interprete->interprete }}, una de las figuras más influyentes del
    folklore
    argentino. Explora cada álbum, canción por canción, y sumérgete en la evolución musical de este talentoso
    artista. Desde sus primeras grabaciones hasta sus últimas producciones, encuentra aquí una colección detallada
    de su obra musical.
  </p>

  {{-- Mostrar mensaje si no hay discos --}}
  @if ($discos->isEmpty())
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg mb-6 border border-yellow-300">
      No hay discos disponibles para {{ $interprete->interprete }} aún.
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($discos as $disco)
        <x-disco-card :disco="$disco" />
      @endforeach
    </div>
  @endif

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
