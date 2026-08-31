@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@php
  $resolvedEditorialImage = app(\App\Services\EditorialImageResolver::class)->resolve($article);
  $metaImage = $resolvedEditorialImage->isMedia()
    ? $resolvedEditorialImage->media->original_path
    : $resolvedEditorialImage->url;
@endphp
@section('metaImage', $metaImage)
@section('ogType', 'article')

@section('ogArticleTags')
  @if ($article->published_at)
    <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
  @endif
  @if ($article->updated_at)
    <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
  @endif
  <meta property="article:section" content="{{ $article->category?->name }}">
@endsection

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": @json($article->title),
  "description": @json($metaDescription),
  "url": @json(\App\Support\CanonicalUrl::normalize($canonical)),
  "datePublished": @json(optional($article->published_at)->toIso8601String()),
  "dateModified": @json(optional($article->updated_at)->toIso8601String()),
  "dateReviewed": @json(optional($article->last_verified_at)->toIso8601String()),
  "author": {
    "@type": "Person",
    "name": @json($article->author?->name ?? 'Redaccion')
  }
}
</script>
@endpush

@section('content')
  @if (isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <article class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <p class="text-sm font-semibold tracking-[0.18em] text-orange-600 uppercase mb-3">{{ $article->category?->name }}</p>
    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">{{ $h1 }}</h1>

    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500 mb-5">
      @if ($article->published_at)
        <span>Publicado: {{ $article->published_at->format('d/m/Y') }}</span>
      @endif
      @if ($article->last_verified_at)
        <span>Ultima revision: {{ $article->last_verified_at->format('d/m/Y') }}</span>
      @endif
      @if ($article->author)
        <span>Autor: {{ $article->author->name }}</span>
      @endif
    </div>

    <div class="mb-5">
      <x-editorial-image
        :entity="$article"
        variant="detail"
        class="rounded-lg shadow-md w-full"
        loading="eager"
        fetchpriority="high"
      />
    </div>

    @if ($article->excerpt)
      <p class="text-lg text-slate-700 mb-6">{{ $article->excerpt }}</p>
    @endif

    <div class="prose prose-lg max-w-none text-slate-800">
      {!! $article->body !!}
    </div>
  </article>

  @foreach ([
    'Interpretes relacionados' => $article->interpretes,
    'Canciones relacionadas' => $article->canciones,
    'Discos relacionados' => $article->albums,
    'Festivales relacionados' => $article->festivales,
    'Eventos relacionados' => $article->events,
    'Provincias relacionadas' => $article->provincias,
  ] as $label => $items)
    @if ($items->isNotEmpty())
      <section class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ $label }}</h2>
        <div class="flex flex-wrap gap-2">
          @foreach ($items as $item)
            @php
              $name = $item->interprete ?? $item->cancion ?? $item->album ?? $item->titulo ?? $item->title ?? $item->nombre ?? 'Relacionado';
              $url = method_exists($item, 'getUrl') ? $item->getUrl() : null;
            @endphp
            @if ($url)
              <a href="{{ $url }}" class="inline-flex items-center rounded-full bg-orange-100 px-4 py-2 text-sm font-medium text-orange-800 hover:bg-orange-200">
                {{ $name }}
              </a>
            @else
              <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                {{ $name }}
              </span>
            @endif
          @endforeach
        </div>
      </section>
    @endif
  @endforeach

  @if ($article->relatedArticles->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm p-6 mb-6">
      <h2 class="text-xl font-semibold text-slate-900 mb-4">Otros articulos de la enciclopedia</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($article->relatedArticles as $related)
          <article class="border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-orange-600 mb-2">{{ $related->category?->name }}</p>
            <h3 class="text-lg font-semibold text-slate-900">
              <a href="{{ route('enciclopedia.show', ['categorySlug' => $related->category?->slug, 'articleSlug' => $related->slug]) }}" class="hover:text-orange-700">
                {{ $related->title }}
              </a>
            </h3>
          </article>
        @endforeach
      </div>
    </section>
  @endif
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.donate />
@endsection
