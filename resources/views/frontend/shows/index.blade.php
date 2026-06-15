@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)
@section('canonical', $canonicalUrl)
@section('metaRobots', $metaRobots)

@push('json-ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    @foreach ($shows as $show)
      {
        "@type": "Event",
        "name": @json($show->titulo),
        "startDate": @json(optional($show->fecha)->toIso8601String()),
        "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
        "eventStatus": "https://schema.org/EventScheduled",
        "url": @json(route('cartelera.show', $show->slug)),
        "description": @json(\Illuminate\Support\Str::limit(strip_tags($show->detalle), 160)),
        "location": {
          "@type": "Place",
          "name": @json(trim(($show->lugar ?? '') . ($show->provincia?->nombre ? ', ' . $show->provincia->nombre : '')))
        }@if($show->interpretes->isNotEmpty()),
        "performer": [
          @foreach($show->interpretes as $interprete)
            {
              "@type": "MusicGroup",
              "name": @json($interprete->interprete),
              "url": @json(route('artista.show', $interprete->slug))
            }@if(!$loop->last),@endif
          @endforeach
        ]@endif
      }@if(!$loop->last),@endif
    @endforeach
  ]
}
</script>
@endpush

@section('content')
  @if(isset($breadcrumbs))
    <x-breadcrumbs :items="$breadcrumbs" />
  @endif


      <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">{{ $heading }}</h1>
      <p class="text-lg text-gray-700 mb-8">{{ $introText }}</p>


  <section class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 md:p-5 mb-8">
    <form method="GET" action="{{ route('cartelera.index') }}" class="space-y-3" id="cartelera-filters">
      <div class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-4 gap-3">
        <div>
          <label for="province_id" class="block text-sm font-medium text-slate-700 mb-1">Provincia</label>
          <select id="province_id" name="province_id" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
          <option value="">Todas las provincias</option>
          @foreach($provincias as $provincia)
            <option value="{{ $provincia->id }}" data-slug="{{ $provincia->slug }}" @selected(($filters['province_id'] ?? null) === $provincia->id)>
              {{ $provincia->nombre }}
            </option>
          @endforeach
          </select>
        </div>

        <div>
          <label for="mes" class="block text-sm font-medium text-slate-700 mb-1">Mes</label>
          <select id="mes" name="mes" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
          <option value="">Próximos eventos</option>
          <option value="hoy" @selected($filters['is_today'])>Hoy</option>
          @foreach($monthOptions as $monthOption)
            <option value="{{ $monthOption['value'] }}" @selected(($filters['month_slug'] ?? null) === $monthOption['value'])>
              {{ $monthOption['label'] }}
            </option>
          @endforeach
          </select>
        </div>

        <div>
          <label for="interprete" class="block text-sm font-medium text-slate-700 mb-1">Intérprete</label>
          <input id="interprete" name="interprete" list="interpretes-list" value="{{ old('interprete', $filters['interprete']?->interprete) }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500" placeholder="Buscar artista">
          <input type="hidden" id="interprete_id" name="interprete_id" value="{{ old('interprete_id', $filters['interprete']?->id) }}">
          <datalist id="interpretes-list">
            @foreach($interpretes as $interprete)
              <option value="{{ $interprete->interprete }}" data-id="{{ $interprete->id }}"></option>
            @endforeach
          </datalist>
        </div>

        <div>
          <label for="fecha" class="block text-sm font-medium text-slate-700 mb-1">Fecha específica</label>
          <input id="fecha" type="date" name="fecha" value="{{ optional($filters['specific_date'])->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_auto] gap-3 items-end">
        <div>
          <label for="q" class="block text-sm font-medium text-slate-700 mb-1">Texto libre</label>
          <input id="q" type="search" name="q" value="{{ $filters['search'] }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500" placeholder="Peña, ciudad, festival">
        </div>

        <div class="flex flex-wrap gap-3 xl:justify-end">
          <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#ff661f] px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition">
            Buscar eventos
          </button>
          <a href="{{ route('cartelera.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-900 transition">
            Limpiar filtros
          </a>
        </div>
      </div>
    </form>
  </section>

  @if ($sinResultados)
    <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-5 mb-8">
      No encontramos eventos con esta combinación de filtros. Probá ampliar la provincia, cambiar el mes o buscar sin fecha exacta.
    </div>
  @endif

  <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach ($shows as $show)
      @php
        $principal = $show->interpretes->first();
        $provinceUrl = $show->provincia ? url('/cartelera-de-eventos-folkloricos/' . $show->provincia->slug) : null;
        $eventImage = $show->images->first();
      @endphp
      <article class="overflow-hidden bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow p-3">
        <a href="{{ route('cartelera.show', $show->slug) }}" class="block">
          @if($eventImage)
            <x-optimized-image :image="$eventImage" variant="card" class="w-full h-56 object-cover rounded-2xl" :alt="$show->titulo" />
          @else
            <x-image-placeholder class="w-full h-56 rounded-2xl" />
          @endif
        </a>

        <div class="p-4 md:p-5">
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-orange-700 mb-3">
              <span>{{ optional($show->fecha)->translatedFormat('d \\d\\e F \\d\\e Y') }}</span>
              @if($show->provincia)
                <span class="text-slate-300">•</span>
                <a href="{{ $provinceUrl }}" class="hover:text-orange-800">{{ $show->provincia->nombre }}</a>
              @endif
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-3">
              <a href="{{ route('cartelera.show', $show->slug) }}" class="hover:text-orange-700 transition">
                {{ $show->titulo }}
              </a>
            </h2>

            <div class="space-y-2 text-sm text-slate-700">
              <p><span class="font-semibold text-slate-900">Lugar:</span> {{ $show->lugar ?: 'Lugar a confirmar' }}</p>
              @if($principal)
                <p>
                  <span class="font-semibold text-slate-900">Intérprete:</span>
                  <a href="{{ route('artista.show', $principal->slug) }}" class="text-orange-700 hover:text-orange-800">
                    {{ $principal->interprete }}
                  </a>
                </p>
              @endif
              @if($show->interpretes->count() > 1)
                <p class="text-slate-600">
                  También participan:
                  @foreach($show->interpretes->skip(1) as $interprete)
                    <a href="{{ route('artista.show', $interprete->slug) }}" class="text-orange-700 hover:text-orange-800">{{ $interprete->interprete }}</a>@if(!$loop->last), @endif
                  @endforeach
                </p>
              @endif
            </div>

            <p class="mt-4 text-slate-600 leading-7">{{ \Illuminate\Support\Str::limit(strip_tags($show->detalle), 180) }}</p>

            <div class="mt-5 flex flex-wrap gap-3 text-sm">
              <a href="{{ route('cartelera.show', $show->slug) }}" class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2.5 font-semibold text-white hover:bg-slate-800 transition">
                Ver detalle
              </a>
              @if($principal)
                <a href="{{ route('artista.show', $principal->slug) }}" class="inline-flex items-center rounded-full border border-orange-200 px-5 py-2.5 font-semibold text-orange-700 hover:border-orange-300 hover:text-orange-800 transition">
                  Más sobre {{ $principal->interprete }}
                </a>
              @endif
              @if($provinceUrl)
                <a href="{{ $provinceUrl }}" class="inline-flex items-center rounded-full border border-slate-200 px-5 py-2.5 font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 transition">
                  Otros eventos en {{ $show->provincia->nombre }}
                </a>
              @endif
            </div>
          </div>
        </div>
      </article>
    @endforeach
  </section>

  <div class="mt-8">
    {{ $shows->links() }}
  </div>

  <section class="mt-10 bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
    <h2 class="text-xl font-bold text-slate-900 mb-4">Explorar por provincia</h2>
    <div class="flex flex-wrap gap-3">
      @foreach($relatedProvinceLinks as $provincia)
        <a href="{{ url('/cartelera-de-eventos-folkloricos/' . $provincia->slug) }}" class="rounded-full border border-orange-200 px-4 py-2 text-sm font-medium text-orange-700 hover:border-orange-300 hover:text-orange-800 transition">
          {{ $provincia->nombre }}
        </a>
      @endforeach
    </div>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const interpreteInput = document.getElementById('interprete');
    const interpreteIdInput = document.getElementById('interprete_id');
    const options = Array.from(document.querySelectorAll('#interpretes-list option'));

    const syncInterpreteId = () => {
      const match = options.find((option) => option.value === interpreteInput.value);
      interpreteIdInput.value = match ? match.dataset.id : '';
    };

    interpreteInput.addEventListener('input', syncInterpreteId);
    syncInterpreteId();
  });
</script>
@endsection
