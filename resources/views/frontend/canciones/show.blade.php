@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">

        <div class="p-3">
          <h1 class="fs-4 fw-bold mb-2">{{ $cancion->cancion }}</h1>
          <p class="fs-5 fw-medium mb-4">{{ $interprete->interprete }}</p>
          @if ($cancion->youtube !== '')
            <div class="ratio ratio-16x9">
              <iframe src="https://www.youtube.com/embed/{{ $cancion->youtube }}" frameborder="0" allowfullscreen></iframe>
            </div>
          @endif

          <p class="fs-5 fw-medium mt-4">{!! $cancion->letra !!}</p>
          <p class="fs-5 fw-medium mt-4">{{ $cancion->visitas }} veces vista</p>
        </div>




        <div class="row">
          <h2>Otras canciones de {{ $interprete->interprete }}</h2>
          @foreach ($related as $index => $cancion)
            <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}" class="col-md-6">
              {{ $cancion->cancion }}
            </a>
          @endforeach
        </div>

      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>
  </div>


@endsection
