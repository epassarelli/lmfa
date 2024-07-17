<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-8">
    <div class="form-group">
      <label for="show">Titulo</label>
      <input type="text" name="show" id="show" class="form-control" value="{{ old('show', $show->show ?? '') }}"
        required>
      @error('show')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Columna 2 y 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="interprete_id">Intérprete</label>
      <select name="interprete_id" id="interprete_id" class="form-control" required>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ isset($show) && $show->interprete_id == $interprete->id ? 'selected' : '' }}>
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

<div class="form-group">
  <label for="detalle">Detalle</label>
  <textarea name="detalle" id="detalle" class="form-control" rows="4" required>{{ old('detalle', $show->detalle ?? '') }}</textarea>
  @error('detalle')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="fecha">Fecha del Show</label>
      <input type="datetime-local" name="fecha" id="fecha" class="form-control"
        value="{{ old('fecha', isset($show) ? $show->fecha : '') }}" required>
      @error('fecha')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="hora">Hora del Show</label>
      <input type="time" name="hora" id="hora" class="form-control"
        value="{{ old('hora', $show->hora ?? '') }}" required>
      @error('hora')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación</label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($show) ? $show->publicar : '') }}" required>
      @error('publicar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="lugar">Lugar del Show</label>
      <input type="text" name="lugar" id="lugar" class="form-control"
        value="{{ old('lugar', $show->lugar ?? '') }}" required>
      @error('lugar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="direccion">Dirección del Show</label>
      <input type="text" name="direccion" id="direccion" class="form-control"
        value="{{ old('direccion', $show->direccion ?? '') }}" required>
      @error('direccion')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="estado" class="form-label">Estado</label>
      <select name="estado" class="form-control" id="estado" required>
        <option value="1" {{ old('estado', $interprete->estado ?? '') == 1 ? 'selected' : '' }}>Activo</option>
        <option value="0" {{ old('estado', $interprete->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo</option>
      </select>
      @error('estado')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>
