{{--
  Ficha técnica del evento. Solo se renderiza lo que está cargado: nunca "a confirmar" ni datos inferidos.
--}}
@php
  $start = $show->start_at;
  $end = $show->end_at && $start && $show->end_at->greaterThan($start) ? $show->end_at : null;
  $hasTime = fn ($date) => $date && $date->format('H:i') !== '00:00';
  $dateFormat = 'l j \d\e F \d\e Y';

  if ($start && $end && ! $end->isSameDay($start)) {
      $dateText = 'Del '.$start->translatedFormat('j \d\e F').' al '.$end->translatedFormat('j \d\e F \d\e Y');
      $timeText = $hasTime($start) ? 'Desde las '.$start->format('H:i').' h' : null;
  } else {
      $dateText = $start ? ucfirst($start->translatedFormat($dateFormat)) : null;
      $timeText = $hasTime($start)
          ? $start->format('H:i').($end && $hasTime($end) ? ' a '.$end->format('H:i') : '').' h'
          : null;
  }

  $venueName = $show->venue_id ? $show->venue?->name : null;
  $city = filled($show->city) ? trim($show->city) : null;
  $place = $venueName ?: $city;
  $address = filled($show->address) ? trim($show->address) : null;
  if ($address && $place && \Illuminate\Support\Str::lower($address) === \Illuminate\Support\Str::lower($place)) {
      $address = null;
  }
  $placeDetail = $venueName && $city ? $city : null;
  $province = $show->provincia;
  $priceText = $show->is_free ? 'Gratis' : (filled($show->price_text) ? trim($show->price_text) : null);

  $mapsUrl = null;
  if ($show->latitude !== null && $show->longitude !== null) {
      $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode($show->latitude.','.$show->longitude);
  } elseif ($address) {
      $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='.urlencode(implode(', ', array_filter([$address, $city, $province?->nombre, 'Argentina'])));
  }
@endphp

<dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
  @if ($dateText)
    <div>
      <dt class="font-semibold text-slate-900">Fecha</dt>
      <dd class="text-slate-700">
        <time datetime="{{ $start->toIso8601String() }}">{{ $dateText }}</time>@if ($timeText)<br>{{ $timeText }}@endif
      </dd>
    </div>
  @endif

  @if ($place)
    <div>
      <dt class="font-semibold text-slate-900">Lugar</dt>
      <dd class="text-slate-700">
        {{ $place }}@if ($placeDetail), {{ $placeDetail }}@endif
        @if ($address)<br><span class="text-slate-600">{{ $address }}</span>@endif
      </dd>
    </div>
  @elseif ($address)
    <div>
      <dt class="font-semibold text-slate-900">Dirección</dt>
      <dd class="text-slate-700">{{ $address }}</dd>
    </div>
  @endif

  @if ($province)
    <div>
      <dt class="font-semibold text-slate-900">Provincia</dt>
      <dd><a href="{{ url('/cartelera-de-eventos-folkloricos/'.$province->slug) }}" class="text-orange-700 hover:text-orange-800">{{ $province->nombre }}</a></dd>
    </div>
  @endif

  @if ($priceText)
    <div>
      <dt class="font-semibold text-slate-900">Entrada</dt>
      <dd class="text-slate-700">{{ $priceText }}</dd>
    </div>
  @endif

  @if ($show->organization_id && $show->organization)
    <div>
      <dt class="font-semibold text-slate-900">Organiza</dt>
      <dd class="text-slate-700">{{ $show->organization->name }}</dd>
    </div>
  @endif
</dl>

@if ($ticketUrl || $mapsUrl)
  <div class="mt-5 flex flex-wrap gap-3">
    @if ($ticketUrl)
      <a href="{{ $ticketUrl }}" target="_blank" rel="noopener nofollow" class="inline-flex items-center rounded-lg bg-[#ff661f] px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition">
        {{ $eventStatus === 'past' ? 'Más info' : 'Comprar entradas / Más info' }}
      </a>
    @endif
    @if ($mapsUrl)
      <a href="{{ $mapsUrl }}" target="_blank" rel="noopener nofollow" class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-900 transition">
        Cómo llegar
      </a>
    @endif
  </div>
@endif
