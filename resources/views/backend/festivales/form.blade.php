<div class="form-group">
  <label for="titulo">Título del Festival</label>
  <input type="text" name="titulo" id="titulo" class="form-control"
    value="{{ old('titulo', $festival->titulo ?? '') }}" required>
</div>

<div class="form-group">
  <label for="detalle">Detalle</label>
  <textarea name="detalle" id="detalle" class="form-control" rows="4">{{ old('detalle', $festival->detalle ?? '') }}</textarea>
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
  @if (isset($festival) && $festival->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/' . $festival->foto) }}" alt="Foto de {{ $festival->titulo }}"
        style="max-height: 80px;">
    </div>
  @endif
</div>

<div class="form-group">
  <label for="provincia_id">Provincia</label>
  <select name="provincia_id" id="provincia_id" class="form-control" required>
    @foreach ($provincias as $provincia)
      <option value="{{ $provincia->id }}"
        {{ isset($festival) && $festival->provincia_id == $provincia->id ? 'selected' : '' }}>
        {{ $provincia->nombre }}
      </option>
    @endforeach
  </select>
</div>

<div class="form-group">
  <label for="mes_id">Mes</label>
  <select name="mes_id" id="mes_id" class="form-control" required>
    @foreach ($meses as $mes)
      <option value="{{ $mes->id }}" {{ isset($festival) && $festival->mes_id == $mes->id ? 'selected' : '' }}>
        {{ $mes->nombre }}
      </option>
    @endforeach
  </select>
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $festival->slug ?? '') }}" required>
  </div>
@endif

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($festival) ? $festival->publicar->format('Y-m-d\TH:i') : '') }}" required>
</div>
