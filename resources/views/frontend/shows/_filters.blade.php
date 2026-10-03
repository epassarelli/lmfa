{{-- Buscador de la cartelera: el mismo en el índice y en la ficha de evento. --}}
@php
  $filters = $filters ?? [];
@endphp

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
        <option value="hoy" @selected($filters['is_today'] ?? false)>Hoy</option>
        @foreach($monthOptions as $monthOption)
          <option value="{{ $monthOption['value'] }}" @selected(($filters['month_slug'] ?? null) === $monthOption['value'])>
            {{ $monthOption['label'] }}
          </option>
        @endforeach
        </select>
      </div>

      <div>
        <label for="interprete" class="block text-sm font-medium text-slate-700 mb-1">Intérprete</label>
        <input id="interprete" name="interprete" list="interpretes-list" value="{{ old('interprete', ($filters['interprete'] ?? null)?->interprete) }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500" placeholder="Buscar artista">
        <input type="hidden" id="interprete_id" name="interprete_id" value="{{ old('interprete_id', ($filters['interprete'] ?? null)?->id) }}">
        <datalist id="interpretes-list">
          @foreach($interpretes as $interprete)
            <option value="{{ $interprete->interprete }}" data-id="{{ $interprete->id }}"></option>
          @endforeach
        </datalist>
      </div>

      <div>
        <label for="fecha" class="block text-sm font-medium text-slate-700 mb-1">Fecha específica</label>
        <input id="fecha" type="date" name="fecha" value="{{ optional($filters['specific_date'] ?? null)->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_auto] gap-3 items-end">
      <div>
        <label for="q" class="block text-sm font-medium text-slate-700 mb-1">Texto libre</label>
        <input id="q" type="search" name="q" value="{{ $filters['search'] ?? '' }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500" placeholder="Peña, ciudad, festival">
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
  </form>
</section>

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
