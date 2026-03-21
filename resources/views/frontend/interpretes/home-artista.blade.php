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
  <div class="container my-5">

    <h1 class="fw-bold font-bold pb-4 text-3xl text-gray-800">{{ $interprete->interprete }}</h1>

    <!-- Noticias -->
    @if ($noticias->count())
      <section class="mb-5">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Últimas noticias</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($noticias as $noticia)
            <x-noticia-card :noticia="$noticia" />
          @endforeach
        </div>
      </section>
    @endif

    <!-- Canciones -->
    @if ($canciones->count())
      <section class="mb-5">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Letras destacadas</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($canciones as $cancion)
            <x-letra-card :letra="$cancion" />
          @endforeach
        </div>
      </section>
    @endif

    <!-- Discos -->
    @if ($discos->count())
      <section class="mb-5">
        <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">Discografía reciente</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          @foreach ($discos as $disco)
            <x-disco-card :disco="$disco" />
          @endforeach

      </section>
    @endif

    <!-- Shows -->
    @if ($shows->count())
      <section class="mb-5">
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
