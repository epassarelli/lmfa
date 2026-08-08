<section class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 md:p-5 mb-8">
  <h2 class="text-3xl font-bold text-slate-900 mb-4">{{ $filtersHeading ?? $h1 }}</h2>
  <p class="text-slate-700 mb-6">{{ $filtersIntro ?? $introText }}</p>

  <form method="GET" action="{{ route('festivales.index') }}" class="space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
      <div>
        <label for="province_id" class="block text-sm font-medium text-slate-700 mb-1">Provincia</label>
        <select id="province_id" name="province_id" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
          <option value="">Todas las provincias</option>
          @foreach ($availableProvinces as $province)
            <option value="{{ $province->id }}" @selected((int) ($filters['province_id'] ?? 0) === (int) $province->id)>{{ $province->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="locality_id" class="block text-sm font-medium text-slate-700 mb-1">Localidad</label>
        <select id="locality_id" name="locality_id" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
          <option value="">Todas las localidades</option>
          @foreach ($availableLocalities as $locality)
            <option value="{{ $locality->id }}" @selected((int) ($filters['locality_id'] ?? 0) === (int) $locality->id)>{{ $locality->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="mes_id" class="block text-sm font-medium text-slate-700 mb-1">Mes</label>
        <select id="mes_id" name="mes_id" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500">
          <option value="">Todos los meses</option>
          @foreach ($availableMonths as $month)
            <option value="{{ $month->id }}" @selected((int) ($filters['month_id'] ?? 0) === (int) $month->id)>{{ $month->nombre }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="q" class="block text-sm font-medium text-slate-700 mb-1">Texto libre</label>
        <input id="q" type="search" name="q" value="{{ $filters['search'] ?? '' }}" class="w-full rounded-lg border-slate-300 py-2.5 focus:border-orange-500 focus:ring-orange-500" placeholder="Festival, provincia o tema">
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_auto] gap-3 items-end">
      <div class="text-sm text-slate-500">
        Combiná provincia, localidad y mes para encontrar fiestas tradicionales y festivales permanentes sin ocupar media pantalla en desktop.
      </div>

      <div class="flex flex-wrap gap-3 xl:justify-end">
        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-orange-700 transition">
          Buscar festivales
        </button>
        <a href="{{ route('festivales.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:border-slate-400 hover:text-slate-900 transition">
          Limpiar filtros
        </a>
      </div>
    </div>
  </form>
</section>
