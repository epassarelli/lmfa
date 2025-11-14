@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  {{-- BLOQUE PRINCIPAL --}}
  <h1 class="text-2xl font-semibold mb-6">Cartelera folklórica</h1>

  @if ($shows->isEmpty())
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
      <p>No hay shows disponibles aún.</p>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach ($shows as $show)
        <x-show-card :show="$show" />
      @endforeach
    </div>
  @endif
  </div>

@endsection

@section('sidebar')

  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
