@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <div class="max-w-5xl mx-auto px-4 py-8">
    <x-breadcrumbs :items="$breadcrumbs" />

    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
      @if ($radio->images->isNotEmpty())
        <x-optimized-image :image="$radio->images->first()" variant="detail" class="w-full max-h-[32rem] object-cover" />
      @else
        <img src="{{ asset('storage/radios/' . $radio->foto) }}" alt="{{ $radio->titulo }}"
          class="w-full max-h-[32rem] object-cover" loading="eager" fetchpriority="high">
      @endif

      <div class="p-6">
        <h1 class="text-3xl font-bold mb-4">{{ $radio->titulo }}</h1>

        <div class="prose prose-lg max-w-none text-gray-800 mb-6">
          {!! $radio->detalle !!}
        </div>

        @if (filled($radio->escucharOnline))
          <a href="{{ $radio->escucharOnline }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center rounded-lg bg-orange-600 px-5 py-3 font-semibold text-white hover:bg-orange-700">
            Escuchar online
          </a>
        @endif
      </div>
    </article>
  </div>
@endsection
