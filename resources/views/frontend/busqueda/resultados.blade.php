@extends('layouts.app')

@section('metaTitle', 'Resultados de búsqueda')
@section('metaDescription', 'Explora los resultados de la búsqueda en Mi Folklore Argentino')

@section('content')
  <div class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6">Resultados para: <span class="text-[#ff661f]">"{{ $query }}"</span></h1>

    @php
      $secciones = [
          'noticias' => 'Noticias',
          'biografias' => 'Biografías',
          'discos' => 'Discos',
          'canciones' => 'Canciones',
          'festivales' => 'Festivales',
          'shows' => 'Cartelera',
          'recetas' => 'Comidas',
          'mitos' => 'Mitos y leyendas',
      ];
    @endphp

    @foreach ($secciones as $key => $titulo)
      @if ($resultados[$key]->isNotEmpty())
        <section class="mb-10">
          <h2 class="text-2xl font-semibold border-b-2 border-[#ff661f] pb-2 mb-4">{{ $titulo }}</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($resultados[$key] as $item)
              @switch($key)
                @case('noticias')
                  <x-noticia-card :noticia="$item" />
                @break

                @case('biografias')
                  <x-biografia-card :interprete="$item" />
                @break

                @case('discos')
                  <x-disco-card :disco="$item" />
                @break

                @case('canciones')
                  <x-letra-card :letra="$item" />
                @break

                @case('festivales')
                  <x-festival-card :festival="$item" />
                @break

                @case('shows')
                  <x-show-card :show="$item" />
                @break

                @case('recetas')
                  <x-receta-card :receta="$item" />
                @break

                @case('mitos')
                  <x-mito-card :mito="$item" />
                @break
              @endswitch
            @endforeach
          </div>
        </section>
      @endif
    @endforeach

    @if (collect($resultados)->flatten()->isEmpty())
      <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
        <p>No se encontraron resultados para tu búsqueda. Intenta con otras palabras clave.</p>
      </div>
    @endif
  </div>
@endsection
