@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')

  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Contenido principal --}}
      <div class="w-full lg:w-2/3">
        <h2 class="text-3xl font-bold mb-6">Noticias de {{ $categoria->nombre }}</h2>

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
      </div>

      {{-- Sidebar --}}
      <div class="w-full lg:w-1/3">
        <aside class="space-y-6">
          <h3 class="text-xl font-semibold border-b pb-2">Últimas noticias</h3>

          @foreach ($ultimas as $ultima)
            <article class="flex items-start space-x-4">
              <a href="{{ route('noticia.show', [$ultima->categoria->slug, $ultima->slug]) }}"
                class="shrink-0 w-20 h-20 overflow-hidden rounded">
                <img
                  src="{{ file_exists(public_path('storage/noticias/' . $ultima->foto)) && $ultima->foto ? asset('storage/noticias/' . $ultima->foto) : asset('img/album.jpg') }}"
                  alt="{{ $ultima->titulo }}" class="object-cover w-full h-full">
              </a>
              <div class="flex-1">
                <h4 class="text-sm font-semibold leading-snug">
                  <a href="{{ route('noticia.show', [$ultima->categoria->slug, $ultima->slug]) }}"
                    class="hover:text-blue-600">
                    {{ $ultima->titulo }}
                  </a>
                </h4>
                <span
                  class="text-xs text-gray-500">{{ $ultima->created_at ? $ultima->created_at->translatedFormat('d F, Y') : '' }}</span>
              </div>
            </article>
          @endforeach
        </aside>
      </div>

    </div>
  </div>

@endsection
