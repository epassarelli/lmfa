@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sección de contenido principal --}}
        <div class="w-full lg:w-2/3">
          @if ($show->images->isNotEmpty())
            <div class="mb-6">
              <x-optimized-image :image="$show->images->first()" variant="hero" class="rounded shadow-lg w-full object-cover max-h-[500px]" />
            </div>
          @elseif ($show->interprete && $show->interprete->images->isNotEmpty())
            <div class="mb-6">
              <x-optimized-image :image="$show->interprete->images->first()" variant="hero" class="rounded shadow-lg w-full object-cover max-h-[500px]" />
            </div>
          @elseif ($show->imagen_destacada)
            <img src="{{ asset('storage/' . $show->imagen_destacada) }}" alt="{{ $show->titulo }}"
              class="mb-6 rounded shadow-lg w-full object-cover max-h-[500px]">
          @endif
          
          <h1 class="text-3xl font-bold mb-4">{{ $show->titulo }}</h1>
          <div class="prose max-w-none mb-6">
            {!! $show->detalle !!}
          </div>
        </div>

      </div>
    </div>
  </section>
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
