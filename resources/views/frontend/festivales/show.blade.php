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

  @if ($festival->images->isNotEmpty())
    <x-optimized-image :image="$festival->images->first()" variant="hero" class="rounded shadow-lg w-full object-cover max-h-[500px]" />
  @elseif ($festival->foto && file_exists(public_path('storage/festivales/' . $festival->foto)))
    <img src="{{ asset('storage/festivales/' . $festival->foto) }}" alt="{{ $festival->titulo }}"
        class="rounded shadow-lg w-full object-cover max-h-[500px] mb-4" loading="lazy">
  @else
    <x-image-placeholder class="w-full rounded shadow-lg min-h-[200px] max-h-[500px] mb-4" />
  @endif

  <div class="bg-white p-2">
    <h1 class="text-3xl font-bold mb-2">{{ $h1 }}</h1>
    <div class="mb-4">
      <a href="{{ route('backend.contributions.create', ['type' => 'festival', 'id' => $festival->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
        Sugerir correccion o actualizacion
      </a>
    </div>

    <div class="prose max-w-none mb-6">
      {!! $festival->detalle !!}
    </div>

    <p class="text-sm text-gray-500">Visitas: {{ number_format($festival->visitas, 0, '', '.') }}</p>
  </div>

  <div class="redes">
    <x-compartir-redes :titulo="$festival->titulo" :url="Request::url()" />
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
