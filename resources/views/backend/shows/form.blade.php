<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-8">
    <div class="form-group">
      <label for="show">Titulo <span class="text-danger">*</span></label>
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
      <select name="interprete_id" id="interprete_id" class="form-control">
        <option value="">Seleccionar</option>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ old('interprete_id', $show->interprete_id ?? '') == $interprete->id ? 'selected' : '' }}>
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
  <!-- Columna 1 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="fecha">Fecha <span class="text-danger">*</span></label>
      <input type="date" name="fecha" value="{{ old('fecha', isset($show) && $show->fecha ? \Carbon\Carbon::parse($show->fecha)->format('Y-m-d') : '') }}" class="form-control" required>

      @error('fecha')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="hora">Hora</label>
      <input type="time" name="hora" id="hora" class="form-control"
        value="{{ old('hora', isset($show) && $show->hora ? $show->hora : '') }}">
      @error('hora')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Publicar el:</label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($show) && $show->publicar ? \Carbon\Carbon::parse($show->publicar)->format('Y-m-d\TH:i') : '') }}">
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
      <label for="lugar">Lugar</label>
      <input type="text" name="lugar" id="lugar" class="form-control"
        value="{{ old('lugar', $show->lugar ?? '') }}">
      @error('lugar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="direccion">Dirección</label>
      <input type="text" name="direccion" id="direccion" class="form-control"
        value="{{ old('direccion', $show->direccion ?? '') }}">
      @error('direccion')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <!-- Columna 3 -->
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

  {{-- Campos ocultos temporalmente --}}
  {{-- <div class="form-group col-md-4">
    <label for="precio_entrada">Precio de la entrada</label>
    <input type="text" name="precio_entrada" value="{{ old('precio_entrada', $show->precio_entrada ?? '') }}"
      class="form-control">
  </div>

  <div class="form-group col-md-4">
    <label for="link_entradas">Link de compra</label>
    <input type="url" name="link_entradas" value="{{ old('link_entradas', $show->link_entradas ?? '') }}"
      class="form-control">
  </div>

  <div class="form-group form-check col-md-4">
    <input type="checkbox" name="destacado" class="form-check-input" id="destacado"
      {{ old('destacado', $show->destacado ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="destacado">Marcar como destacado</label>
  </div> --}}
  {{-- 
  <div class="form-group col-md-8">
    <label for="imagen_destacada">Imagen destacada</label>
    <input type="file" name="imagen_destacada" class="form-control-file">
    @if (!empty($show->imagen_destacada))
      <img src="{{ asset('storage/' . $show->imagen_destacada) }}" alt="Imagen actual" width="150">
    @endif
  </div>
  --}}
  {{-- Campos ocultos temporalmente --}}
  {{-- <div class="form-group col-md-4">
    <label for="slug">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $show->slug ?? '') }}" class="form-control">
  </div>

  <div class="form-row">
    <div class="form-group col-md-6">
      <label for="lat">Latitud</label>
      <input type="number" step="any" name="lat" value="{{ old('lat', $show->lat ?? '') }}"
        class="form-control">
    </div>
    <div class="form-group col-md-6">
      <label for="lng">Longitud</label>
      <input type="number" step="any" name="lng" value="{{ old('lng', $show->lng ?? '') }}"
        class="form-control">
    </div>
  </div> --}}
  {{-- 
  <div class="form-group col-md-4">
    <label for="provincia_id">Provincia</label>
    <select name="provincia_id" class="form-control">
      <option value="">Seleccionar</option>
      @foreach ($provincias as $provincia)
        <option value="{{ $provincia->id }}"
          {{ old('provincia_id', $show->provincia_id ?? '') == $provincia->id ? 'selected' : '' }}>
          {{ $provincia->nombre }}
        </option>
      @endforeach
    </select>
  </div>
  --}}
</div>

{{-- Campo Detalle movido al final --}}
<div class="form-group">
  <label for="detalle">Detalle <span class="text-danger">*</span></label>
  <textarea name="detalle" id="detalle" class="form-control" rows="4">{{ old('detalle', $show->detalle ?? '') }}</textarea>
  @error('detalle')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

{{-- Campos hidden para preservar valores de campos ocultos temporalmente --}}
@if(isset($show) && $show->id)
  <input type="hidden" name="precio_entrada" value="{{ old('precio_entrada', $show->precio_entrada ?? '') }}">
  <input type="hidden" name="link_entradas" value="{{ old('link_entradas', $show->link_entradas ?? '') }}">
  <!-- <input type="hidden" name="destacado" value="{{ old('destacado', $show->destacado ?? 0) }}"> -->
  <input type="hidden" name="slug" value="{{ old('slug', $show->slug ?? '') }}">
  <input type="hidden" name="lat" value="{{ old('lat', $show->lat ?? '') }}">
  <input type="hidden" name="lng" value="{{ old('lng', $show->lng ?? '') }}">
@endif
