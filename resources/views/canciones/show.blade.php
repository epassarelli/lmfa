@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>


      <div class="col-md-9">

        <div class="p-6">
          <h2 class="text-2xl font-bold mb-2">{{ $cancion->cancion }}</h2>
          <p class="text-lg font-medium mb-4">{{ $interprete->interprete }}</p>
          @if ($cancion->youtube !== '')
            <div class="relative h-0 pb-16/9">
              <iframe src="https://www.youtube.com/watch?v={{ $cancion->youtube }}" frameborder="0"
                class="absolute top-0 left-0 w-full h-full"></iframe>
            </div>
          @endif

          <p class="text-lg font-medium mt-4">{!! $cancion->letra !!}</p>
          <p class="text-lg font-medium mt-4">{{ $cancion->visitas }} veces vista</p>
        </div>




        <div class="row">
          <h2>Otras canciones de {{ $interprete->interprete }}</h2>
          <ul class="list-group">
            @foreach ($related as $index => $cancion)
              <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}"
                class="list-group-item @if ($index % 2 == 0) list-group-item-secondary @endif">
                {{ $cancion->interprete->interprete }} - {{ $cancion->cancion }}
              </a>
            @endforeach
          </ul>
        </div>

      </div>

    </div>
  </div>


@endsection
