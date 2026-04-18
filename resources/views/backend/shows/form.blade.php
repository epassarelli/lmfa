<div class="row">
  <!-- Título -->
  <div class="col-md-8">
    <div class="form-group">
      <label for="title">Titulo <span class="text-danger">*</span></label>
      <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $show->title ?? '') }}"
        required>
      @error('title')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Intérprete -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="interprete_id">Intérprete</label>
      <select name="interprete_id" id="interprete_id" class="form-control">
        <option value="">Seleccionar</option>
        @foreach ($interpretes as $interprete)
          @php $currentInterpreteId = old('interprete_id', $show->interpretes->first()?->id ?? ''); @endphp
          <option value="{{ $interprete->id }}"
            {{ $currentInterpreteId == $interprete->id ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
      @error('interprete_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <!-- Fecha y hora (start_at) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="start_at">Fecha y hora <span class="text-danger">*</span></label>
      <input type="datetime-local" name="start_at" id="start_at" class="form-control" required
        value="{{ old('start_at', isset($show) && $show->start_at ? \Carbon\Carbon::parse($show->start_at)->format('Y-m-d\TH:i') : '') }}">
      @error('start_at')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Publicar el (published_at) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="published_at">Publicar el:</label>
      <input type="datetime-local" name="published_at" id="published_at" class="form-control"
        value="{{ old('published_at', isset($show) && $show->published_at ? \Carbon\Carbon::parse($show->published_at)->format('Y-m-d\TH:i') : '') }}">
      @error('published_at')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Estado -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
      @php
        $defaultEstado = isset($show) && $show->estado !== null
          ? $show->estado
          : (Auth::user()->hasAnyRole(['prensa', 'administrador']) ? 1 : 0);
        $selectedEstado = old('estado', $defaultEstado);
      @endphp
      <select name="estado" class="form-control" id="estado" required>
        <option value="1" {{ $selectedEstado == 1 ? 'selected' : '' }}>Activo</option>
        <option value="0" {{ $selectedEstado == 0 ? 'selected' : '' }}>Inactivo</option>
      </select>
      @error('estado')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <!-- Lugar (city) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="city">Lugar</label>
      <input type="text" name="city" id="city" class="form-control"
        value="{{ old('city', $show->city ?? '') }}">
      @error('city')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Dirección (address) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="address">Dirección</label>
      <input type="text" name="address" id="address" class="form-control"
        value="{{ old('address', $show->address ?? '') }}">
      @error('address')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Provincia (province_id) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="province_id">Provincia</label>
      <select name="province_id" id="province_id" class="form-control">
        <option value="">Seleccionar</option>
        @foreach ($provincias as $provincia)
          <option value="{{ $provincia->id }}"
            {{ old('province_id', $show->province_id ?? '') == $provincia->id ? 'selected' : '' }}>
            {{ $provincia->nombre }}
          </option>
        @endforeach
      </select>
      @error('province_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <!-- Link de entradas (ticket_url) -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="ticket_url">Link de entradas</label>
      <input type="url" name="ticket_url" id="ticket_url" class="form-control"
        value="{{ old('ticket_url', $show->ticket_url ?? '') }}">
      @error('ticket_url')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Precio (price_text) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="price_text">Precio</label>
      <input type="text" name="price_text" id="price_text" class="form-control"
        value="{{ old('price_text', $show->price_text ?? '') }}">
      @error('price_text')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Entrada libre (is_free) -->
  <div class="col-md-2 d-flex align-items-end pb-3">
    <div class="form-check">
      <input type="checkbox" name="is_free" class="form-check-input" id="is_free" value="1"
        {{ old('is_free', $show->is_free ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_free">Entrada libre</label>
    </div>
  </div>
</div>

<!-- Detalle (body) -->
<div class="form-group">
  <label for="body">Detalle <span class="text-danger">*</span></label>
  <textarea name="body" id="body" class="form-control" rows="4">{{ old('body', $show->body ?? '') }}</textarea>
  @error('body')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

@if(isset($show) && $show->id)
  <input type="hidden" name="slug" value="{{ old('slug', $show->slug ?? '') }}">
@endif
