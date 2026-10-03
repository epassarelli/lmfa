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


  <section class="bg-white p-2 rounded shadow-sm mb-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2 border-b-2 border-[#ff661f] pb-2">{{ $heading }}</h1>
    <p class="text-base text-gray-700">
      {{ $introText }} <strong>{{ $shows->total() }}</strong> shows confirmados{{ $filters['provincia'] ? ' en '.$filters['provincia']->nombre : '' }}.
    </p>
  </section>


  @include('frontend.shows._filters', ['variant' => 'full'])

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
      @endphp
      <article class="overflow-hidden bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow p-3">
        <a href="{{ route('cartelera.show', $show->slug) }}" class="block">
          <x-editorial-image
            :entity="$show"
            variant="card"
            class="w-full h-64 object-cover"
            loading="lazy"
          />
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
              <a href="{{ route('cartelera.show', $show->slug) }}" class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 font-semibold text-white hover:bg-slate-800 transition">
                Ver detalle
              </a>
              @if($principal)
                <a href="{{ route('artista.show', $principal->slug) }}" class="inline-flex items-center rounded-lg border border-orange-200 px-5 py-2.5 font-semibold text-orange-700 hover:border-orange-300 hover:text-orange-800 transition">
                  Más sobre {{ $principal->interprete }}
                </a>
              @endif
              @if($provinceUrl)
                <a href="{{ $provinceUrl }}" class="inline-flex items-center rounded-lg border border-slate-200 px-5 py-2.5 font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900 transition">
                  Otros eventos en {{ $show->provincia->nombre }}
                </a>
              @endif
            </div>
          </div>
        </div>
      </article>
    @endforeach
  </section>

  <x-public-pagination :paginator="$shows" />

  @include('frontend.shows._province_links', ['provinceLinks' => $relatedProvinceLinks, 'wrapperClass' => 'mt-10'])
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.social-links />
  <x-sidebar.invite-to-publish />
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
