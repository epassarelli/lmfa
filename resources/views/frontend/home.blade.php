@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section class="py-12">

    <div class="container mx-auto px-4">
      <div class="flex flex-col lg:flex-row lg:space-x-8">

        <!-- Sección de noticias -->
        <div class="w-full lg:w-2/3">
          @php
            $bloques = [
                'Ultimas noticias de folklore argentino' => $ultimasNoticias,
            ];
          @endphp

          @foreach ($bloques as $titulo => $noticias)
            <div class="mb-8">
              <h2 class="text-2xl font-bold text-gray-800 mb-4">{{ $titulo }}</h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($noticias as $noticia)
                  <x-noticia-card :noticia="$noticia" />
                @endforeach
              </div>
            </div>
          @endforeach

          <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Mi Folklore Argentino | Todo sobre Nuestras Tradiciones y
            Costumbres</h1>
          <p class="text-gray-700 text-lg">Bienvenido a Mi Folklore Argentino, tu portal sobre la cultura y tradiciones de
            Argentina. Descubre música,
            danzas y más. ¡Visítanos hoy!</p>
        </div>

        <!-- Sección lateral -->
        <div class="w-full lg:w-1/3 mt-10 lg:mt-0">
          <aside class="space-y-10">

            <!-- Categorías -->
            <section>
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Categorías</h3>
              <div class="flex flex-wrap gap-2">
                @foreach ($categorias as $cat)
                  <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
                    class="bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300">{{ $cat->nombre }}</a>
                @endforeach
              </div>
            </section>

            <!-- Últimos discos -->
            <section>
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Últimos álbumes</h3>
              <div class="space-y-4">
                @foreach ($discos as $disco)
                  <article class="flex gap-4 items-start">
                    <a href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}"
                      class="shrink-0">
                      <img
                        src="{{ file_exists(public_path('storage/albunes/' . $disco->foto)) && $disco->foto ? asset('storage/albunes/' . $disco->foto) : asset('img/album.jpg') }}"
                        alt="{{ $disco->album }}" class="w-20 h-20 object-cover rounded">
                    </a>
                    <div class="text-sm text-gray-700">
                      <h4 class="font-semibold"><a
                          href="{{ route('interprete.album.show', [$disco->interprete->slug, $disco->slug]) }}">{{ $disco->album }}</a>
                      </h4>
                      <span>{{ $disco->interprete->interprete }}</span><br>
                      <span>{{ number_format($disco->visitas, 0, '', ',') }} visitas</span>
                    </div>
                  </article>
                @endforeach
              </div>
            </section>

            <!-- Últimas biografías -->
            <section>
              <h3 class="text-xl font-semibold text-gray-800 mb-4">Últimas biografías</h3>
              <div class="space-y-4">
                @foreach ($interpretes as $artista)
                  <article class="flex gap-4 items-start">
                    <a href="{{ route('interprete.show', $artista->slug) }}" class="shrink-0">
                      <img
                        src="{{ file_exists(public_path('storage/interpretes/' . $artista->foto)) && $artista->foto ? asset('storage/interpretes/' . $artista->foto) : asset('img/interprete.jpg') }}"
                        alt="{{ $artista->interprete }}" class="w-20 h-20 object-cover rounded">
                    </a>
                    <div class="text-sm text-gray-700">
                      <h4 class="font-semibold"><a
                          href="{{ route('interprete.show', $artista->slug) }}">{{ $artista->interprete }}</a></h4>
                      <span>{{ number_format($artista->visitas, 0, '', ',') }} visitas</span>
                    </div>
                  </article>
                @endforeach
              </div>
            </section>

          </aside>
        </div>
      </div>
    </div>
  </section>
@endsection
