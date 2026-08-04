@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  @if (isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <p class="text-sm font-semibold tracking-[0.18em] text-orange-600 uppercase mb-3">Enciclopedia</p>
    <h1 class="text-3xl font-bold text-slate-900 mb-3">{{ $category->name }}</h1>
    @if (! empty($landingCopy['intro']))
      <p class="text-lg text-slate-700">{{ $landingCopy['intro'] }}</p>
    @endif
  </section>

  <section class="mb-4">
    <h2 class="text-2xl font-semibold text-slate-900">
      {{ $landingCopy['list_heading'] ?? 'Articulos de esta familia' }}
    </h2>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @forelse ($articles as $article)
      <article class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-2xl font-semibold text-slate-900 mb-2">
          <a href="{{ route('enciclopedia.show', ['categorySlug' => $category->slug, 'articleSlug' => $article->slug]) }}" class="hover:text-orange-700">
            {{ $article->title }}
          </a>
        </h3>
        @if ($article->excerpt)
          <p class="text-slate-600 mb-3">{{ $article->excerpt }}</p>
        @endif
        <div class="flex items-center justify-between gap-4 text-sm text-slate-500">
          <span>Publicado: {{ $article->published_at?->format('d/m/Y') ?? '-' }}</span>
          <a href="{{ route('enciclopedia.show', ['categorySlug' => $category->slug, 'articleSlug' => $article->slug]) }}" class="font-medium text-orange-700 hover:text-orange-800">
            Leer mas
          </a>
        </div>
      </article>
    @empty
      <div class="col-span-full bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-slate-500">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">
          {{ $landingCopy['empty_heading'] ?? 'Esta familia todavia no tiene articulos publicados.' }}
        </h3>
        <p>{{ $landingCopy['empty_text'] ?? 'Esta familia todavia no tiene articulos publicados.' }}</p>
      </div>
    @endforelse
  </section>

  <div class="mt-6">
    {{ $articles->links() }}
  </div>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
