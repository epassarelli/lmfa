@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if (isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">Enciclopedia del folklore argentino</h1>
    <p class="text-base text-gray-700">
      Ritmos, danzas, instrumentos, regiones, historia y tradiciones del folklore argentino explicados de forma clara, organizados por categoría para consulta permanente.
    </p>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-10">
    @foreach ($categories as $category)
      <a href="{{ route('enciclopedia.category', $category->slug) }}" class="group bg-gradient-to-br from-orange-50 via-white to-amber-50 border border-orange-100 rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-xl font-semibold text-slate-900 group-hover:text-orange-700 transition">{{ $category->name }}</h2>
            <p class="text-sm text-slate-600 mt-2">{{ $category->description ?: 'Familia editorial de la enciclopedia.' }}</p>
          </div>
          <span class="inline-flex items-center justify-center h-10 min-w-[2.5rem] px-3 rounded-full bg-orange-100 text-orange-700 font-semibold">
            {{ $category->published_articles_count }}
          </span>
        </div>
      </a>
    @endforeach
  </section>

  <section class="bg-white rounded-xl shadow-sm p-6 cv-auto">
    <div class="flex items-center justify-between gap-4 mb-5">
      <h2 class="text-lg font-semibold text-gray-800">Artículos publicados</h2>
      <span class="text-sm text-slate-500">{{ $featuredArticles->count() }} resultados recientes</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      @foreach ($featuredArticles as $article)
        <article class="border border-slate-200 rounded-xl p-5 hover:border-orange-300 transition">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-600 mb-2">{{ $article->category?->name }}</p>
          <h3 class="text-xl font-semibold text-slate-900 mb-2">
            <a href="{{ route('enciclopedia.show', ['categorySlug' => $article->category?->slug, 'articleSlug' => $article->slug]) }}" class="hover:text-orange-700">
              {{ $article->title }}
            </a>
          </h3>
          @if ($article->excerpt)
            <p class="text-slate-600 mb-3">{{ $article->excerpt }}</p>
          @endif
          <a href="{{ route('enciclopedia.show', ['categorySlug' => $article->category?->slug, 'articleSlug' => $article->slug]) }}" class="text-orange-700 font-medium hover:text-orange-800">
            Leer artículo
          </a>
        </article>
      @endforeach
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
