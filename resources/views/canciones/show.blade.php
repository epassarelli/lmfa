@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="w-full px-4">
    @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  </div>

  <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
      <div class="grid grid-cols-1 md:grid-cols-3">
        <div class="p-6">
          <h2 class="text-2xl font-bold mb-2">{{ $cancion->cancion }}</h2>
          <p class="text-lg font-medium mb-4">{{ $cancion->interprete->interprete }}</p>
          <div class="relative h-0 pb-16/9">
            <iframe src="{{ $cancion->youtube }}" frameborder="0" class="absolute top-0 left-0 w-full h-full"></iframe>
          </div>
          <p class="text-lg font-medium mt-4">{!! $cancion->letra !!}</p>
          <p class="text-lg font-medium mt-4">{{ $cancion->visitas }} veces vista</p>
        </div>
        <div class="p-6 md:col-span-2">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($relacionadas as $related_song)
              <div class="overflow-hidden bg-white shadow-md rounded-lg">
                <a href="{{ route('interprete.cancion.show', [$related_song->interprete->slug, $related_song->slug]) }}">
                  <img src="{{ asset('storage/interpretes/' . $related_song->interprete->foto) }}"
                    alt="{{ $related_song->artist }}" class="w-full h-48 object-cover">
                  <div class="p-6">
                    <h3 class="text-lg font-medium mb-2">{{ $related_song->cancion }}</h3>
                    <p class="text-sm text-gray-500">{{ $related_song->interprete->interprete }}</p>
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
