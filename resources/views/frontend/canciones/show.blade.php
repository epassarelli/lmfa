@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  {{-- Contenido principal --}}

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $cancion->cancion }}</h1>
    <p class="text-lg text-gray-700 mb-4">{{ $interprete->interprete }}</p>

    <div class="prose prose-lg max-w-none text-gray-800">
      {!! $cancion->letra !!}
    </div>

    <p class="text-base text-gray-600 mt-4">{{ $cancion->visitas }} veces vista</p>
  </div>

  {{-- Video YouTube --}}
  @if (!empty($cancion->youtube))
    <div class="relative mb-8 cursor-pointer aspect-video overflow-hidden rounded-lg shadow-md"
      onclick="loadYouTubeIframe(this)" data-video-id="{{ $cancion->youtube }}">
      <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="Reproducir video de {{ $cancion->cancion }}"
        class="w-full h-full object-cover" />

      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-16 h-16 bg-red-600 text-white text-3xl rounded-full shadow-lg flex items-center justify-center">
          ▶

        </div>
      </div>
    </div>
  @endif

  {{-- Muestro ls redes p compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$cancion->cancion" :url="Request::url()" />
  </div>

  {{-- Otras canciones --}}
  <div class="mt-10">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Otras canciones de {{ $interprete->interprete }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach ($related as $cancion)
        <a href="{{ route('artista.cancion', [$interprete->slug, $cancion->slug]) }}"
          class="block bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg p-2 transition duration-200 shadow-sm">
          <h3 class="text-md font-medium text-gray-800">{{ $cancion->cancion }}</h3>
        </a>
      @endforeach
    </div>
  </div>


  {{-- Sidebar --}}
  {{-- <div class="w-full lg:w-1/4">
        @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
      </div> --}}


@endsection

@section('sidebar')

  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])


@endsection

@section('scripts')
  <script>
    function loadYouTubeIframe(container) {
      const videoId = container.getAttribute('data-video-id');
      const iframe = document.createElement('iframe');
      iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
      iframe.width = '100%';
      iframe.height = '100%';
      iframe.className = 'w-full h-full';
      iframe.frameBorder = '0';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      container.innerHTML = '';
      container.appendChild(iframe);
    }
  </script>
@endsection
