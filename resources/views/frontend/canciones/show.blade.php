@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  <style>
    a.cancion-card,
    a.cancion-card:visited,
    a.cancion-card:hover,
    a.cancion-card:active {
      background-color: #f8f9fa !important;
      color: #000000 !important;
      text-decoration: none !important;
      border: 1px solid #dee2e6 !important;
      display: block;
      padding: 1rem;
      border-radius: 0.5rem;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* Asegura que todos los elementos hijos se vean en negro */
    a.cancion-card *,
    a.cancion-card span,
    a.cancion-card h3 {
      color: #000000 !important;
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
          {{-- @if (!empty($cancion->youtube))
            <div class="ratio ratio-16x9" style="min-height: 200px;">
              <iframe src="https://www.youtube.com/embed/{{ $cancion->youtube }}" frameborder="0" allowfullscreen></iframe>
            </div>
          @endif --}}

          <div class="letra-cancion fs-5 fw-medium mt-4">
            {!! $cancion->letra !!}
          </div>
          <p class="fs-5 fw-medium mt-4">{{ $cancion->visitas }} veces vista</p>



          @if (!empty($cancion->youtube))
            <div class="youtube-placeholder position-relative mb-4 ratio ratio-16x9" onclick="loadYouTubeIframe(this)"
              data-video-id="{{ $cancion->youtube }}"
              style="cursor: pointer; overflow: hidden; border-radius: 0.5rem; max-width: 100%;">

              <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}"
                alt="Reproducir video de {{ $cancion->cancion }}" class="img-fluid w-100"
                style="object-fit: cover; aspect-ratio: 16 / 9;">

              <div class="play-button d-flex align-items-center justify-content-center"
                style="position: absolute; top: 50%; left: 50%;
                   transform: translate(-50%, -50%);
                   background-color: rgba(255, 0, 0, 0.8);
                   width: 64px; height: 64px;
                   border-radius: 50%;
                   font-size: 2rem;
                   color: white;
                   box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);">
                ▶
              </div>
            </div>
          @endif



        </div>


        <div class="row g-3">

          <div class="col-12">
            <h2 class="h5 fw-bold">Otras canciones de {{ $interprete->interprete }}</h2>
          </div>

          @foreach ($related as $cancion)
            <div class="col-12 col-md-6">
              <a href="{{ route('canciones.show', [$interprete->slug, $cancion->slug]) }}"
                class="cancion-card d-block rounded shadow-sm p-3">
                <h3 class="fs-5 fw-semibold mb-0">{{ $cancion->titulo }}</h3>

              </a>
            </div>
          @endforeach

        </div>

      </div>

      <div class="col-md-3 my-4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div>

    </div>
  </div>


@endsection

@section('scripts')
  <script>
    function loadYouTubeIframe(container) {
      const videoId = container.getAttribute('data-video-id');
      const iframe = document.createElement('iframe');
      iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
      iframe.width = '100%';
      iframe.height = '315';
      iframe.frameBorder = '0';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      container.innerHTML = ''; // Borra la imagen y el botón
      container.appendChild(iframe);
    }
  </script>

@endsection
