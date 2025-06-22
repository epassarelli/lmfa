@extends('layouts.app')

@section('title', 'Error interno del servidor')

@section('content')
  <div class="container text-center">
    <h1 class="display-1">500</h1>
    <p class="lead">Oops, algo salió mal en nuestro servidor.</p>
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
