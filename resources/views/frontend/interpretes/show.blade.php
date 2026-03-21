@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto))

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@id": "{{ url()->current() }}",
  "@type": "MusicGroup",
  "name": "{{ $interprete->interprete }}",
  "description": "{{ $metaDescription }}",
  "url": "{{ url()->current() }}",
  "image": "{{ $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto) }}",
  "genre": "Folklore Argentino"
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "¿Quién es {{ $interprete->interprete }}?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "{{ Str::limit(strip_tags($interprete->biografia), 200) }}"
      }
    },
    {
      "@type": "Question",
      "name": "¿Cuál es la trayectoria de {{ $interprete->interprete }} en el folklore?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Conoce la biografía completa, discos y canciones de {{ $interprete->interprete }} en Mi Folklore Argentino."
      }
    }
  ]
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    {{-- Contenido principal --}}
    <h1 class="text-2xl font-semibold mb-6">Biografía de {{ $interprete->interprete }}</h1>

    <div class="prose max-w-none prose-lg prose-slate">
      {!! $interprete->biografia !!}
    </div>


  </section>

  {{-- Muestro las redes p/ compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$interprete->interprete" :url="Request::url()" />
  </div>
  {{-- Selector de intérprete --}}
  {{-- @include('layouts.partials.select-interprete') --}}



@endsection

@section('sidebar')
  @if ($interprete->images->isNotEmpty())
    <div class="mb-4">
      <x-optimized-image :image="$interprete->images->first()" variant="card" width="400" height="400"
        class="rounded shadow-md object-cover w-full" :alt="$interprete->interprete" fetchpriority="high" />
    </div>
  @endif
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  <br>
  <x-sidebar.social-links />

@endsection
