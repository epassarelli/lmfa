@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MusicRecording",
  "name": @json($cancion->cancion),
  "byArtist": {
    "@type": "{{ $interprete->artist_type === 'soloist' ? 'Person' : 'MusicGroup' }}",
    "name": @json($interprete->interprete)
  }@if($cancion->composer),
  "composer": {"@type": "Person", "name": @json($cancion->composer)}@endif
}
</script>

@if (!empty($cancion->youtube))
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "{{ $cancion->cancion }} - {{ $interprete->interprete }}",
  "description": "Video y letra de la cancion {{ $cancion->cancion }} por {{ $interprete->interprete }}.",
  "thumbnailUrl": "{{ $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto) }}",
  "uploadDate": "{{ $cancion->created_at ? $cancion->created_at->toIso8601String() : '' }}",
  "contentUrl": "https://www.youtube.com/watch?v={{ $cancion->youtube }}",
  "embedUrl": "https://www.youtube.com/embed/{{ $cancion->youtube }}"
}
</script>
@endif
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $h1 }}</h1>
    <div class="mb-4">
      <a href="{{ route('backend.contributions.create', ['type' => 'cancion', 'id' => $cancion->id]) }}" class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1" data-nosnippet>
        Sugerir correccion de letra
      </a>
    </div>
    <p class="text-lg text-gray-700 mb-4">{{ $interprete->interprete }}</p>

    @if (filled($cancion->letra))
      <div class="prose prose-lg max-w-none text-gray-800">
        {!! $cancion->letra !!}
      </div>
    @elseif ($cancion->is_instrumental)
      <div class="bg-blue-50 text-blue-800 p-4 rounded">
        Esta obra es instrumental y no tiene letra.
      </div>
    @else
      <div class="bg-yellow-50 text-yellow-800 p-4 rounded">
        La letra no está disponible en Mi Folklore Argentino. La ficha conserva información de la obra, créditos y grabaciones relacionadas.
      </div>
    @endif

    @if ($cancion->composer || $cancion->lyricist)
      <div class="mt-5 text-sm text-gray-700">
        @if ($cancion->composer)<p><strong>Composición:</strong> {{ $cancion->composer }}</p>@endif
        @if ($cancion->lyricist)<p><strong>Letra:</strong> {{ $cancion->lyricist }}</p>@endif
      </div>
    @endif

    <p class="text-base text-gray-600 mt-4">{{ $cancion->visitas }} veces vista</p>
  </div>

  @if (!empty($cancion->youtube))
    <div class="relative mb-8 cursor-pointer aspect-video overflow-hidden rounded-lg shadow-md"
      onclick="loadYouTubeIframe(this)" data-video-id="{{ $cancion->youtube }}">
      @if ($interprete->images->isNotEmpty())
        <x-optimized-image :image="$interprete->images->first()" variant="card" class="w-full h-full object-cover" />
      @else
        <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="Reproducir video de {{ $cancion->cancion }}"
          class="w-full h-full object-cover" />
      @endif

      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-16 h-16 bg-red-600 text-white text-3xl rounded-full shadow-lg flex items-center justify-center">
          ▶
        </div>
      </div>
    </div>
  @endif

  <div class="redes">
    <x-compartir-redes :titulo="$cancion->cancion" :url="Request::url()" />
  </div>

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
