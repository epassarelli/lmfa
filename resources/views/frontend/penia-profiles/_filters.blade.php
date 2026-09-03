<form method="GET" action="{{ route('penia-profiles.index') }}" class="{{ $filterFormClass ?? 'mt-6 grid gap-3 md:grid-cols-4' }}">
  <input name="q" value="{{ request('q') }}" class="rounded-lg border-slate-300" placeholder="Nombre o ciudad">
  <select name="province_id" class="rounded-lg border-slate-300"><option value="">Todas las provincias</option>@foreach($provincias as $provincia)<option value="{{ $provincia->id }}" @selected((int) request('province_id') === $provincia->id)>{{ $provincia->nombre }}</option>@endforeach</select>
  <select name="venue_type" class="rounded-lg border-slate-300"><option value="">Todos los espacios</option>@foreach($venueTypes as $value => $label)<option value="{{ $value }}" @selected(request('venue_type') === $value)>{{ $label }}</option>@endforeach</select>
  <button class="rounded-lg bg-orange-600 px-4 py-2 font-semibold text-white hover:bg-orange-700">Buscar peñas</button>
</form>
