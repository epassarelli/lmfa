@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  <style>
    a.cancion-card,
    a.cancion-card:visited,
    a.cancion-card:hover,
    a.cancion-card:active {
      background-color: #f8f9fa;
      color: #212529 !important;
      text-decoration: none !important;
      border: 1px solid #dee2e6;
      display: block;
      padding: 1rem;
      border-radius: 0.5rem;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
  </style>
@endsection

@section('content')

  <div class="container mt-5">
    <div class="row mb-4">

      <div class="col-md-9">

        <div class="p-3">
          <h1 class="fs-4 fw-bold mb-2">{{ $cancion->cancion }}</h1>
          <p class="fs-5 fw-medium mb-4">{{ $interprete->interprete }}</p>
          @if (!empty($cancion->youtube))
            <div class="ratio ratio-16x9" style="min-height: 200px;">
              <iframe src="https://www.youtube.com/embed/{{ $cancion->youtube }}" frameborder="0" allowfullscreen></iframe>
            </div>
          @endif

          <div class="letra-cancion fs-5 fw-medium mt-4" style="min-height: 300px;">
            {!! $cancion->letra !!}
          </div>
          <p class="fs-5 fw-medium mt-4">{{ $cancion->visitas }} veces vista</p>
        </div>


        <div class="row g-3">
          <div class="col-12">
            <h2 class="h5 fw-bold">Otras canciones de {{ $interprete->interprete }}</h2>
          </div>

          @foreach ($otrasCanciones as $cancion)
            <div class="col-12 col-md-6">
              <a href="{{ route('letras.show', [$interprete->slug, $cancion->slug]) }}"
                class="cancion-card d-block rounded shadow-sm p-3">
                <span class="fw-semibold fs-5">{{ $cancion->titulo }}</span>
              </a>
            </div>
          @endforeach

        </div>


      </div>

      <div class="col-md-3">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>
  </div>


@endsection
