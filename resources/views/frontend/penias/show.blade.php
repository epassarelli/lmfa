@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="max-w-5xl mx-auto px-4 py-8">
    <x-breadcrumbs :items="$breadcrumbs" />

    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
      @if ($penia->images->isNotEmpty())
        <x-optimized-image :image="$penia->images->first()" variant="detail" class="w-full max-h-[32rem] object-cover" />
      @else
        <img src="{{ asset('storage/noticias/' . $penia->foto) }}" alt="{{ $penia->titulo }}"
          class="w-full max-h-[32rem] object-cover" loading="eager" fetchpriority="high">
      @endif

      <div class="p-6">
        <h1 class="text-3xl font-bold mb-4">{{ $penia->titulo }}</h1>

        <div class="prose prose-lg max-w-none text-gray-800">
          {!! $penia->detalle !!}
        </div>
      </div>
    </article>
  </div>
@endsection
