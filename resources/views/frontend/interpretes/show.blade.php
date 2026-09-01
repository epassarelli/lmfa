@inject('editorialImageResolver', 'App\\Services\\EditorialImageResolver')
@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@php
  $resolvedImage = $editorialImageResolver->resolve($interprete);
  $schemaType = $interprete->artist_type === 'soloist' ? 'Person' : 'MusicGroup';
@endphp

@section('metaImage', $resolvedImage->url)

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@id": @json(\App\Support\CanonicalUrl::current()),
  "@type": @json($schemaType),
  "name": @json($interprete->interprete),
  "description": @json($metaDescription),
  "url": @json(\App\Support\CanonicalUrl::current()),
  "image": @json($resolvedImage->url),
  @if($schemaType === 'MusicGroup')
  "genre": "Folklore Argentino"
  @else
  "knowsAbout": "Folklore Argentino"
  @endif
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold mb-2">{{ $h1 }}</h1>
    <div class="mb-6">
      <a href="{{ route('backend.contributions.create', ['type' => 'interprete', 'id' => $interprete->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
        Sugerir edicion de biografia
      </a>
    </div>

    <div class="prose max-w-none prose-lg prose-slate">
      {!! $interprete->biografia !!}
    </div>
  </section>

  <div class="redes">
    <x-compartir-redes :titulo="$interprete->interprete" :url="Request::url()" />
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <div class="mb-4">
    <x-editorial-image
      :entity="$interprete"
      variant="card"
      class="rounded shadow-md object-cover w-full"
      loading="eager"
      fetchpriority="high"
    />
  </div>
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  <br>
  <x-sidebar.social-links />
@endsection
