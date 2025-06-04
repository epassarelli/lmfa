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

          @if (!empty($cancion->youtube))
            <div class="ratio ratio-16x9 youtube-placeholder" style="position: relative; cursor: pointer;"
              data-id="{{ $cancion->youtube }}">
              <img src="https://i.ytimg.com/vi/{{ $cancion->youtube }}/hqdefault.jpg" class="img-fluid"
                alt="Miniatura del video" width="560" height="315"
                style="object-fit: cover; width: 100%; height: 100%; border-radius: 0.5rem;">
              <div class="youtube-play-button">
                <svg width="68" height="48" viewBox="0 0 68 48">
                  <path
                    d="M66.52 7.58a8 8 0 0 0-5.64-5.65C56.09 0 34 0 34 0s-22.09 0-26.88 1.93a8 8 0 0 0-5.64 5.65C0 12.36 0 24 0 24s0 11.64 1.48 16.42a8 8 0 0 0 5.64 5.65C11.91 48 34 48 34 48s22.09 0 26.88-1.93a8 8 0 0 0 5.64-5.65C68 35.64 68 24 68 24s0-11.64-1.48-16.42zM27 34V14l18 10-18 10z"
                    fill="#f00" />
                </svg>
              </div>
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
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.youtube-placeholder').forEach(div => {
        div.addEventListener('click', function() {
          const videoId = this.dataset.id;
          const iframe = document.createElement('iframe');
          iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1`);
          iframe.setAttribute('frameborder', '0');
          iframe.setAttribute('allowfullscreen', 'true');
          iframe.setAttribute('loading', 'lazy');
          iframe.style.width = '100%';
          iframe.style.height = '100%';
          this.innerHTML = '';
          this.appendChild(iframe);
        });
      });
    });
  </script>

@endsection
