@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('metaImage', $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto))

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@id": "{{ url()->current() }}",
  "@type": "MusicGroup",
  "name": "{{ $interprete->interprete }}",
  "description": "{{ $metaDescription }}",
  "url": "{{ url()->current() }}",
  "image": "{{ $interprete->images->isNotEmpty() ? $interprete->images->first()->original_path : asset('storage/interpretes/' . $interprete->foto) }}",
  "genre": "Folklore Argentino"
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  @php
    $sectionCtaClass = 'inline-flex items-center rounded-lg bg-[#ff661f] px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600';
    $bioPreview = \Illuminate\Support\Str::limit(strip_tags($interprete->biografia), 420);
  @endphp

  <div class="container my-5">
    <h1 class="fw-bold font-bold pb-4 text-3xl text-gray-800">{{ $interprete->interprete }}</h1>

    <section class="mb-8">
      <div>
        <p class="text-base text-gray-700">{!! $bioPreview !!}</p>
        <div class="mt-4">
          <a href="{{ route('artista.biografia', $interprete->slug) }}" class="{{ $sectionCtaClass }}">
            Ver biografía completa
          </a>
        </div>
      </div>
    </section>

    @if ($noticias->count())
      <section class="mb-8">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Últimas noticias</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($noticias as $noticia)
            <x-noticia-card :noticia="$noticia" />
          @endforeach
        </div>
        <div class="mt-5">
          <a href="{{ route('artista.noticias', $interprete->slug) }}" class="{{ $sectionCtaClass }}">
            Ver todas las noticias de {{ $interprete->interprete }}
          </a>
        </div>
      </section>
    @endif

    @if ($canciones->count())
      <section class="mb-8">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Letras destacadas</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($canciones as $cancion)
            <x-letra-card :letra="$cancion" />
          @endforeach
        </div>
        <div class="mt-5">
          <a href="{{ route('artista.canciones', $interprete->slug) }}" class="{{ $sectionCtaClass }}">
            Ver todas las canciones de {{ $interprete->interprete }}
          </a>
        </div>
      </section>
    @endif

    @if ($discos->count())
      <section class="mb-8">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Discografía reciente</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($discos as $disco)
            <x-disco-card :disco="$disco" />
          @endforeach
        </div>
        <div class="mt-5">
          <a href="{{ route('artista.discografia', $interprete->slug) }}" class="{{ $sectionCtaClass }}">
            Ver toda la discografía de {{ $interprete->interprete }}
          </a>
        </div>
      </section>
    @endif

    @if ($shows->count())
      <section class="mb-8">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Próximos shows</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($shows as $show)
            <x-show-card :show="$show" />
          @endforeach
        </div>
      </section>
    @endif
  </div>
@endsection

@section('sidebar')
  @include('layouts.partials.interpretes-header', ['interprete' => $interprete])
  <br>
  <x-sidebar.social-links />
@endsection
