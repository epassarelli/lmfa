@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')


  {{-- Contenido principal --}}

  {{-- <h2 class="text font-semibold mb-6">Noticias de {{ $categoria->nombre }}</h2> --}}

  @if ($noticias->isEmpty())
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
      No hay noticias disponibles para <strong>{{ $categoria->nombre }}</strong> aún.
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      @foreach ($noticias as $noticia)
        <x-noticia-card :noticia="$noticia" />
      @endforeach
    </div>
  @endif



@endsection

@section('sidebar')
  <!-- Categorías -->
  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h3 class="text-xl font-semibold text-gray-800  mb-4 border-b-2 border-[#ff661f]">Categorías</h3>
    <div class="flex flex-wrap gap-2">
      @foreach ($categorias as $cat)
        <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
          class="bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300">{{ $cat->nombre }}</a>
      @endforeach
    </div>
  </section>

  <x-sidebar.social-links />
@endsection
