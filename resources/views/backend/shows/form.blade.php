<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-8">
    <div class="form-group">
      <label for="show">Titulo</label>
      <input type="text" name="show" id="show" class="form-control" value="{{ old('show', $show->show ?? '') }}"
        required>
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
    </div>
  </div>

</div>


<div class="form-group">
  <label for="detalle">Detalle</label>
  <textarea name="detalle" id="detalle" class="form-control" rows="4" required>{{ old('detalle', $show->detalle ?? '') }}</textarea>
</div>



<div class="row">
  <!-- Columna 1 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="fecha">Fecha del Show</label>
      <input type="datetime-local" name="fecha" id="fecha" class="form-control"
        value="{{ old('fecha', isset($show) ? $show->fecha : '') }}" required>
    </div>
  </div>
  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="hora">Hora del Show</label>
      <input type="time" name="hora" id="hora" class="form-control"
        value="{{ old('hora', $show->hora ?? '') }}" required>
    </div>
  </div>
  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación</label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($show) ? $show->publicar : '') }}" required>
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
    </div>
  </div>
  <!-- Columna 2 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="direccion">Dirección del Show</label>
      <input type="text" name="direccion" id="direccion" class="form-control"
        value="{{ old('direccion', $show->direccion ?? '') }}" required>
    </div>
  </div>
  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="foto">Foto</label>
      <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
      @if (isset($show) && $show->foto)
        <div class="mt-2">
          <img src="{{ asset('storage/' . $show->foto) }}" alt="Foto de {{ $show->show }}"
            style="max-height: 80px;">
        </div>
      @endif
    </div>
  </div>
</div>



{{-- 
@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $show->slug ?? '') }}" required>
  </div>
@endif 
--}}
