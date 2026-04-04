@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(isset($breadcrumbs))
      <x-breadcrumbs :items="$breadcrumbs" />
    @endif

    <h1 class="text-3xl font-bold mb-4">Letras de Canciones Folklóricas</h1>
    <h2 class="text-xl font-semibold mb-4 border-b-2 border-[#ff661f] inline-block uppercase pb-1">Letra {{ $letra }}</h2>

    <div class="space-y-4 text-lg text-gray-700">
      <p>Explora nuestra vasta colección de canciones folklóricas argentinas ordenadas alfabéticamente.</p>
    </div>

    <div class="mt-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
        @foreach ($canciones as $cancion)
          <x-letra-card :letra="$cancion" />
        @endforeach
      </div>
    </div>

    <div class="mt-8 mb-8">
      {{ $canciones->links() }}
    </div>

    {{-- Navegación alfabética --}}
    <div class="mt-16 bg-white p-4 rounded shadow-sm">
      <h2 class="text-2xl font-bold mb-4 border-b-2 border-[#ff661f] pb-2">Buscar por Orden Alfabético</h2>
      <p class="text-lg text-gray-700 mb-4">Utilizá nuestro índice alfabético para encontrar tu canción favorita.</p>

      <nav class="flex flex-wrap gap-2 justify-center mt-4">
        @foreach (range('a', 'z') as $lt)
          <a href="{{ route('canciones.letra', strtolower($lt)) }}"
            class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-[#ff661f] hover:text-white transition uppercase font-semibold">{{ $lt }}</a>
        @endforeach
      </nav>
    </div>

  </div>

@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />
@endsection
