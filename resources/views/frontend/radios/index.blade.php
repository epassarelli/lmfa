@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mx-auto py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold mb-8">Radios de Folklore Argentino</h1>
    @foreach ($radios as $radio)
      <div class="w-full sm:w-1/2 md:w-1/3 p-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <a href="{{ route('noticias.show', $radio->slug) }}">
            @if ($radio->images->isNotEmpty())
              <x-optimized-image :image="$radio->images->first()" variant="card" class="w-full h-48 object-cover" />
            @else
              <img src="{{ asset('storage/radios/' . $radio->foto) }}" alt="{{ $radio->titulo }}"
                class="w-full h-48 object-cover">
            @endif
            <div class="p-4">
              <h3 class="font-bold text-xl mb-2">{{ $radio->titulo }}</h3>
            </div>
          </a>
        </div>
      </div>
    @endforeach
  </div>




@endsection
