@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $show->images->isNotEmpty() ? $show->images->first()->original_path : ($show->interprete && $show->interprete->images->isNotEmpty() ? $show->interprete->images->first()->original_path : asset('storage/' . $show->imagen_destacada)))

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $show->titulo }}",
  "startDate": "{{ $show->fecha ? $show->fecha->toIso8601String() : '' }}",
  "location": {
    "@type": "Place",
    "name": "{{ $show->lugar }}",
    "address": "{{ $show->direccion ?? $show->lugar }}"
  },
  "image": "{{ $show->images->isNotEmpty() ? $show->images->first()->original_path : ($show->interprete && $show->interprete->images->isNotEmpty() ? $show->interprete->images->first()->original_path : asset('storage/' . $show->imagen_destacada)) }}",
  "description": "{{ $metaDescription }}",
  "performer": {
    "@type": "MusicGroup",
    "name": "{{ $show->interprete->interprete ?? 'Artistas Varios' }}"
  }
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <div class="container mx-auto px-4 mt-4">
      <x-breadcrumbs :items="$breadcrumbs" />
    </div>
  @endif
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
          @else
            <x-image-placeholder class="mb-6 w-full rounded shadow-lg min-h-[200px] max-h-[500px]" />
          @endif
          
          <h1 class="text-3xl font-bold mb-2">{{ $show->titulo }}</h1>
          <div class="mb-4">
            <a href="{{ route('backend.contributions.create', ['type' => 'show', 'id' => $show->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1">
              📅 Sugerir corrección de datos del evento
            </a>
          </div>
          <div class="prose max-w-none mb-6">
            {!! $show->detalle !!}
          </div>
        </div>

      </div>
    </div>
  </section>
@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
