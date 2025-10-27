@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section>

    <!-- Sección de noticias -->
    @php
      $bloques = [
          'El portal del folklore argentino' => $ultimasNoticias,
      ];
    @endphp

    @foreach ($bloques as $titulo => $noticias)
      <div class="mb-8">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          @foreach ($noticias as $noticia)
            <x-noticia-card :noticia="$noticia" />
          @endforeach
        </div>
      </div>
    @endforeach

    <section class="bg-white p-2 rounded shadow-sm mb-4">
      <h1 class="text-xl font-semibold text-gray-900 mb-4 border-b-2 border-[#ff661f]">Mi Folklore Argentino | Todo sobre
        Nuestras Tradiciones y
        Costumbres</h1>
      <p class="text-gray-700 text-lg">Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de
        Argentina. Descubre música, danzas y más. ¡Visítanos hoy!</p>
    </section>

  </section>
@endsection


@section('sidebar')
  {{-- tu sidebar específico --}}
  <x-sidebar.social-links />

  <x-sidebar.donate />

  <!-- Categorías -->
  {{-- <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h3 class="text-xl font-semibold text-gray-800  mb-4 border-b-2 border-[#ff661f]">Categorías</h3>
    <div class="flex flex-wrap gap-2">
      @foreach ($categorias as $cat)
        <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
          class="bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300">{{ $cat->nombre }}</a>
      @endforeach
    </div>
  </section> --}}

  {{-- <x-sidebar.card-discos :discos="$ultimosDiscos" /> --}}


  {{-- <x-sidebar.card-biografias :interpretes="$ultimosArtistas" /> --}}


@endsection
