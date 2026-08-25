@props([
    'action',
    'searchPlaceholder' => 'Buscar...',
    'sortOptions' => [],
])

<form method="GET" action="{{ $action }}" class="mb-3">
  <div class="rounded border bg-light p-3">
  <div class="row align-items-end">
    <div class="col-md-4">
      <label for="search" class="small text-muted mb-1">Buscar</label>
      <input
        id="search"
        type="text"
        name="search"
        class="form-control"
        placeholder="{{ $searchPlaceholder }}"
        value="{{ request('search') }}"
      >
    </div>

    <div class="col-md-3">
      <label for="sort" class="small text-muted mb-1">Ordenar por</label>
      <select id="sort" name="sort" class="form-control">
        @foreach ($sortOptions as $value => $label)
          <option value="{{ $value }}" @selected(request('sort', array_key_first($sortOptions)) === $value)>
            {{ $label }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <label for="direction" class="small text-muted mb-1">Direccion</label>
      <select id="direction" name="direction" class="form-control">
        <option value="desc" @selected(request('direction', 'desc') === 'desc')>Descendente</option>
        <option value="asc" @selected(request('direction') === 'asc')>Ascendente</option>
      </select>
    </div>

    {{ $slot }}

    <div class="col-md-3">
      <div class="d-flex">
        <button type="submit" class="btn btn-primary btn-sm mr-2">
          <i class="fas fa-search mr-1"></i> Aplicar
        </button>
        <a href="{{ $action }}" class="btn btn-outline-secondary btn-sm">
          Limpiar
        </a>
      </div>
    </div>
  </div>
  </div>
</form>
