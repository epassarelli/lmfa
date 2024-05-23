@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-4">
        {{-- <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" class="img-fluid rounded"
          alt="{{ $interprete->interprete }}"> --}}
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

      <div class="col-md-8">


        <div class="p-6">
          <h2 class="text-2xl font-bold mb-2">{{ $cancion->cancion }}</h2>
          <p class="text-lg font-medium mb-4">{{ $cancion->interprete->interprete }}</p>

          <div class="relative h-0 pb-16/9">
            <iframe src="{{ $cancion->youtube }}" frameborder="0" class="absolute top-0 left-0 w-full h-full"></iframe>
          </div>

          <p class="text-lg font-medium mt-4">{!! $cancion->letra !!}</p>
          <p class="text-lg font-medium mt-4">{{ $cancion->visitas }} veces vista</p>
        </div>


        <div class="row">
          <h2>Canciones relacionadas</h2>
          @foreach ($relacionadas as $related_song)
            <div class="col-md-4 mb-4">
              <div class="card mb-3 h-100">
                <a href="{{ route('canciones.show', [$related_song->interprete->slug, $related_song->slug]) }}"
                  class="text-decoration-none">
                  <img src="{{ asset('storage/interpretes/' . $related_song->interprete->foto) }}"
                    alt="{{ $related_song->artist }}" class="card-img-top">
                  <div class="card-body">
                    <h3 class="card-title h5 text-dark">{{ $related_song->cancion }}</h3>
                    <p class="text-sm text-gray-500">{{ $related_song->interprete->interprete }}</p>
                  </div>
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>


@endsection
