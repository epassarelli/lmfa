@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('styles')
  {{-- Tailwind ya se encarga del diseño --}}
@endsection

@section('content')
  <section class="bg-white p-2 mb-4">

    {{-- Contenido principal --}}
    <img src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="{{ $noticia->titulo }}"
      class="w-full rounded-lg shadow-md mb-6 object-cover">

    <h1 class="text-2xl font-semibold text-gray-800 mb-4">{{ $noticia->titulo }}</h1>

    <div class="prose prose-lg max-w-none mb-6 text-gray-800">
      {!! $noticia->noticia !!}
    </div>

    <p class="text-sm text-gray-500">Visitas: {{ $noticia->visitas }}</p>



    {{-- Muestro los buttons con interpretes paar ver noticis de ellos --}}
    <div class="more">

    </div>

    {{-- Muestro 3 noticis relcionads X ????????????? --}}
    <div class="related">
      @if ($noticia->interpretes->count() > 1)
        <div class="mt-6 border-t pt-4 text-sm text-gray-700">
          <p class="font-semibold text-gray-800 mb-2">También participan:</p>
          <ul class="flex flex-wrap gap-2">
            @foreach ($noticia->interpretes as $interprete)
              @if ($interprete->id !== $noticia->interprete_id)
                <li>
                  <a href="{{ route('artista.noticias', $interprete->slug) }}"
                    class="inline-block bg-orange-100 text-orange-700 px-3 py-1 rounded-full hover:bg-orange-200 transition">
                    {{ $interprete->interprete }}
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        </div>
      @endif

    </div>

    {{-- Posiblemente comments de FB --}}
    <div class="comments">

    </div>

  </section>

  {{-- Muestro ls redes p compartir --}}
  <div class="redes">
    <x-compartir-redes :titulo="$noticia->titulo" :url="Request::url()" />
  </div>

  {{-- Noticias relacionadas --}}
  @if ($relacionadas && $relacionadas->count() > 0)
    <section class="bg-white p-2 mb-4">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b-2 border-[#ff661f] pb-2">
        Noticias relacionadas
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($relacionadas as $noticiaRelacionada)
          <x-noticia-card :noticia="$noticiaRelacionada" />
        @endforeach
      </div>
    </section>
  @endif

@endsection


@section('sidebar')

  @if (isset($ultimasSidebar) && $ultimasSidebar->count() > 0)
    <x-sidebar.card-noticias :noticias="$ultimasSidebar" />
  @endif

  <x-sidebar.social-links />
  <x-sidebar.donate />




  {{-- <x-sidebar.card-biografias :interpretes="$ultimosArtistas" /> --}}

  {{-- <section class="mb-6">
    <h3 class="text-xl font-semibold mb-4 border-b pb-2">Últimas noticias</h3>

    @foreach ($ultimas_noticias as $n)

      <article class="flex items-start mb-4">
        <a href="{{ route('noticia.show', [$n->categoria->slug, $n->slug]) }}"
          class="block w-20 h-20 flex-shrink-0 rounded overflow-hidden shadow">
          <img
            src="{{ file_exists(public_path('storage/noticias/' . $n->foto)) && $n->foto ? asset('storage/noticias/' . $n->foto) : asset('img/album.jpg') }}"
            alt="{{ $n->titulo }}" class="w-full h-full object-cover">
        </a>
        <div class="ml-4">
          <h4 class="text-sm font-medium text-gray-800 leading-snug">
            <a href="{{ route('noticia.show', [$n->categoria->slug, $n->slug]) }}" class="hover:underline">
              {{ $n->titulo }}
            </a>
          </h4>
          <span class="text-xs text-gray-500">
            {{ $n->created_at ? $n->created_at->translatedFormat('d F, Y') : '' }}
          </span>
        </div>
      </article>
    @endforeach

  </section> --}}

@endsection
