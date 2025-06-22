@extends('layouts.app')

@section('title', 'Página no encontrada')

@section('content')
  <div class="container text-center">
    <h1 class="display-1">404</h1>
    <p class="lead">Lo sentimos, la página que estás buscando no se pudo encontrar.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a>
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
