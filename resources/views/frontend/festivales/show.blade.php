@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $festival->images->isNotEmpty() ? $festival->images->first()->original_path : asset('storage/festivales/' . $festival->foto))

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "{{ $festival->titulo }}",
  "image": "{{ $festival->images->isNotEmpty() ? $festival->images->first()->original_path : asset('storage/festivales/' . $festival->foto) }}",
  "description": "{{ $metaDescription }}"
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  {{-- CONTENIDO PRINCIPAL --}}


  @if ($festival->images->isNotEmpty())
    <x-optimized-image :image="$festival->images->first()" variant="hero" class="rounded shadow-lg w-full object-cover max-h-[500px]" />
  @else
    <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
      class="rounded shadow-lg w-full object-cover max-h-[500px]">
  @endif
  
    <div class="bg-white p-2">


    <h1 class="text-3xl font-bold mb-2">{{ $festival->titulo }}</h1>
    <div class="mb-4">
      <a href="{{ route('contributions.create', ['type' => 'festival', 'id' => $festival->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1">
        🎡 Sugerir corrección o actualización
      </a>
    </div>

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


  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  {{-- <x-sidebar.top-news :noticias="$noticiasMasLeidas" /> --}}
  {{-- <x-sidebar.upcoming-shows :eventos="$eventosSidebar" /> --}}
  {{-- <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  {{-- <x-sidebar.advertisement /> --}}
  {{-- <x-sidebar.invite-to-publish /> --}}

@endsection
