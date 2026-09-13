@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Noticias del Folklore Argentino</h1>
    <p class="text-base text-gray-700">
      Lanzamientos, giras y agenda de festivales del folklore argentino, actualizados a diario. Ya publicamos <strong>{{ $totalNoticias }}</strong> noticias en el portal.
    </p>
  </section>

  @if ($categorias->isNotEmpty())
    <section class="mb-4">
      <div class="flex flex-wrap gap-2">
        @foreach ($categorias as $cat)
          <a href="{{ route('noticias.byCategoria', [$cat->slug]) }}"
            class="bg-white border text-sm px-3 py-1 rounded-full hover:bg-gray-100 hover:border-[#ff661f] transition-colors">{{ $cat->nombre }}</a>
        @endforeach
      </div>
    </section>
  @endif

  @if ($masLeidas->isNotEmpty())
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Más leídas</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($masLeidas as $noticia)
          <x-noticia-card :noticia="$noticia" />
        @endforeach
      </div>
    </section>
  @endif

  <section class="mb-12">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Últimas noticias</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4">
      @foreach ($ultimas as $noticia)
        <x-noticia-card :noticia="$noticia" />
      @endforeach
    </div>

    <x-public-pagination :paginator="$ultimas" />
  </section>

@endsection

@section('sidebar')

  <x-sidebar.newsletter-form />

  @if(isset($ultimasSidebar) && $ultimasSidebar->count() > 0)
    <x-sidebar.card-noticias :noticias="$ultimasSidebar" />
  @endif

  <x-sidebar.social-links />

  <x-sidebar.donate />

@endsection
