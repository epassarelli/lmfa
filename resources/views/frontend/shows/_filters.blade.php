{{--
  Filtros de la cartelera. Variantes:
  - full: índice (provincia, mes, intérprete con datalist, fecha y texto libre).
  - compact: ficha de evento (provincia, mes y texto libre), plegada en mobile y sin datalist.
--}}
@php
  $variant = $variant ?? 'full';
  $filters = $filters ?? [];
  $isCompact = $variant === 'compact';
  $fieldClass = 'w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500';
@endphp

@if ($isCompact)
  <details id="cartelera-buscador" class="group bg-white rounded-xl border border-slate-200 shadow-sm mb-6">
    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-base font-semibold text-slate-900">
      <span>Buscar otros eventos</span>
      <span aria-hidden="true" class="text-orange-700 transition group-open:rotate-180">&#9662;</span>
    </summary>
    <div class="px-4 pb-4">
@else
  <section class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 md:p-5 mb-8">
@endif

    <form method="GET" action="{{ route('cartelera.index') }}" class="space-y-3" id="cartelera-filters" role="search" aria-label="Buscar en la cartelera">
      <div class="grid grid-cols-1 {{ $isCompact ? 'md:grid-cols-[1fr_1fr_2fr_auto] items-end' : 'md:grid-cols-4' }} gap-3">
        <div>
          <label for="province_id" class="block text-sm font-medium text-slate-700 mb-1">Provincia</label>
          <select id="province_id" name="province_id" class="{{ $fieldClass }}">
            <option value="">Todas las provincias</option>
            @foreach ($provincias as $provincia)
              <option value="{{ $provincia->id }}" data-slug="{{ $provincia->slug }}" @selected(($filters['province_id'] ?? null) === $provincia->id)>{{ $provincia->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label for="mes" class="block text-sm font-medium text-slate-700 mb-1">Mes</label>
          <select id="mes" name="mes" class="{{ $fieldClass }}">
            <option value="">Próximos eventos</option>
            <option value="hoy" @selected($filters['is_today'] ?? false)>Hoy</option>
            @foreach ($monthOptions as $monthOption)
              <option value="{{ $monthOption['value'] }}" @selected(($filters['month_slug'] ?? null) === $monthOption['value'])>{{ $monthOption['label'] }}</option>
            @endforeach
          </select>
        </div>

        @if ($isCompact)
          <div>
            <label for="q" class="block text-sm font-medium text-slate-700 mb-1">Texto libre</label>
            <input id="q" type="search" name="q" value="{{ $filters['search'] ?? '' }}" class="{{ $fieldClass }}" placeholder="Artista, peña, ciudad">
          </div>
          <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#ff661f] px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition">
            Buscar eventos
          </button>
        @else
          <div>
            <label for="interprete" class="block text-sm font-medium text-slate-700 mb-1">Intérprete</label>
            <input id="interprete" name="interprete" list="interpretes-list" value="{{ old('interprete', $filters['interprete']?->interprete) }}" class="{{ $fieldClass }}" placeholder="Buscar artista">
            <input type="hidden" id="interprete_id" name="interprete_id" value="{{ old('interprete_id', $filters['interprete']?->id) }}">
            <datalist id="interpretes-list">
              @foreach ($interpretes as $interprete)
                <option value="{{ $interprete->interprete }}" data-id="{{ $interprete->id }}"></option>
              @endforeach
            </datalist>
          </div>

          <div>
            <label for="fecha" class="block text-sm font-medium text-slate-700 mb-1">Fecha específica</label>
            <input id="fecha" type="date" name="fecha" value="{{ optional($filters['specific_date'])->format('Y-m-d') }}" class="{{ $fieldClass }}">
          </div>
        @endif
      </div>

      @unless ($isCompact)
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_auto] gap-3 items-end">
          <div>
            <label for="q" class="block text-sm font-medium text-slate-700 mb-1">Texto libre</label>
            <input id="q" type="search" name="q" value="{{ $filters['search'] }}" class="{{ $fieldClass }}" placeholder="Peña, ciudad, festival">
          </div>

          <div class="flex flex-wrap gap-3 xl:justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#ff661f] px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 transition">
              Buscar eventos
            </button>
            <a href="{{ route('cartelera.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-900 transition">
              Limpiar filtros
            </a>
          </div>
        </div>
      @endunless
    </form>

@if ($isCompact)
    </div>
  </details>
  {{-- Abierto en desktop, plegado en mobile. --}}
  <script>if (window.matchMedia('(min-width: 768px)').matches) document.getElementById('cartelera-buscador').open = true;</script>
@else
  </section>
@endif
