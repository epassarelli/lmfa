@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonical)
@section('metaRobots', $metaRobots)

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif

  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">{{ $h1 }}</h1>
    <p class="text-base text-gray-700">
      {{ $introText }} Ya relevamos <strong>{{ $results->total() }}</strong> festivales en todo el país.
    </p>
  </section>

  @include('frontend.festivales._filters')

  <section class="mb-8">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Festivales encontrados</h2>
    @if ($results->count())
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($results as $festival)
          <x-festival-card :festival="$festival" />
        @endforeach
      </div>
      <x-public-pagination :paginator="$results" />
    @else
      <div class="bg-white rounded-xl shadow-sm p-6 text-slate-600">
        No se encontraron festivales para esta combinacion de filtros.
      </div>
    @endif
  </section>

  @if ($featured->isNotEmpty())
    <section class="mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Festivales destacados</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($featured as $festival)
          <x-festival-card :festival="$festival" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($currentMonthFestivals->isNotEmpty())
    <section class="mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Festivales del mes actual</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach ($currentMonthFestivals as $festival)
          <x-festival-card :festival="$festival" />
        @endforeach
      </div>
    </section>
  @endif

  @if ($provinceLinks->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm p-6 mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Explorar por provincia</h2>
      <div class="flex flex-wrap gap-2">
        @foreach ($provinceLinks as $province)
          @if ($province->enabled)
            <a href="{{ $province->url }}" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition {{ $province->active ? 'bg-orange-600 text-white hover:bg-orange-700' : 'bg-orange-100 text-orange-800 hover:bg-orange-200' }}">
              {{ $province->label }}
            </a>
          @else
            <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
              {{ $province->label }}
            </span>
          @endif
        @endforeach
      </div>
    </section>
  @endif

  @if ($monthLinks->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm p-6 mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Explorar por mes</h2>
      <div class="flex flex-wrap gap-2">
        @foreach ($monthLinks as $month)
          @if ($month->enabled)
            <a href="{{ $month->url }}" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition {{ $month->active ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
              {{ $month->label }}
            </a>
          @else
            <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
              {{ $month->label }}
            </span>
          @endif
        @endforeach
      </div>
    </section>
  @endif

  @if ($relatedNews->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm p-6 mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Ultimas noticias relacionadas</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($relatedNews as $item)
          <article class="border border-slate-200 rounded-xl p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-orange-600 mb-2">{{ $item->categoria?->nombre }}</p>
            <h3 class="text-lg font-semibold">
              <a href="{{ route('noticias.show', $item->slug) }}" class="hover:text-orange-700">{{ $item->title }}</a>
            </h3>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  @if ($relatedEvents->isNotEmpty())
    <section class="bg-white rounded-xl shadow-sm p-6 mb-8 cv-auto">
      <h2 class="text-lg font-semibold mb-3 text-gray-800">Proximos eventos relacionados</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($relatedEvents as $event)
          <article class="border border-slate-200 rounded-xl p-4">
            <h3 class="text-lg font-semibold">
              <a href="{{ route('cartelera.show', $event->slug) }}" class="hover:text-orange-700">{{ $event->title }}</a>
            </h3>
            <p class="text-sm text-slate-600 mt-2">
              {{ optional($event->start_at)->format('d/m/Y') }} · {{ $event->provincia?->nombre }}
            </p>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  <section class="bg-white rounded-xl shadow-sm p-6 cv-auto">
    <h2 class="text-lg font-semibold mb-3 text-gray-800">Sobre esta sección</h2>
    <p class="text-base text-gray-700">
      Acá reunimos las fiestas y festivales tradicionales de folklore de todo el país, con su historia, ubicación y fecha habitual, para que puedas planificar tu próximo viaje o descubrir la celebración más cercana a tu zona. Para la agenda con fechas confirmadas de esta edición, visitá la <a href="{{ route('cartelera.index') }}" class="text-[#ff661f] hover:underline">cartelera de eventos</a>.
    </p>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection
