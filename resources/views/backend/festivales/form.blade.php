<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="titulo">Título del Festival</label>
      <input type="text" name="titulo" id="titulo" class="form-control"
        value="{{ old('titulo', $festival->titulo ?? '') }}" required>
      @error('titulo')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  @if (Auth::user()->hasRole('administrador'))
    <div class="col-md-6">
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $festival->slug ?? '') }}" required>
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif
</div>

<div class="form-group">
  <label for="detalle">Detalle</label>
  <textarea name="detalle" id="detalle" class="form-control" rows="4">{{ old('detalle', $festival->detalle ?? '') }}</textarea>
  @error('detalle')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>



<div class="row">


  <div class="form-group col-md-4">
    <label for="provincia_id">Provincia</label>
    <select name="provincia_id" id="provincia_id" class="form-control" required>
      @foreach ($provincias as $provincia)
        <option value="{{ $provincia->id }}"
          {{ isset($festival) && $festival->provincia_id == $provincia->id ? 'selected' : '' }}>
          {{ $provincia->nombre }}
        </option>
      @endforeach
    </select>
    @error('provincia_id')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group col-md-4">
    <label for="mes_id">Mes</label>
    <select name="mes_id" id="mes_id" class="form-control" required>
      @foreach ($meses as $mes)
        <option value="{{ $mes->id }}" {{ isset($festival) && $festival->mes_id == $mes->id ? 'selected' : '' }}>
          {{ $mes->nombre }}
        </option>
      @endforeach
    </select>
    @error('mes_id')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>



  <div class="form-group col-md-4">
    <label for="publicar">Fecha de Publicación</label>
    <input type="datetime-local" name="publicar" id="publicar" class="form-control"
      value="{{ old('publicar', isset($festival) ? $festival->publicar : '') }}" required>
    @error('publicar')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>

</div>

<div class="row">

  <div class="form-group col-md-8">
    <label for="foto">Foto</label>
    <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
    @if (isset($festival) && $festival->foto)
      <div class="mt-2">
        <img src="{{ asset('storage/' . $festival->foto) }}" alt="Foto de {{ $festival->titulo }}"
          style="max-height: 80px;">
      </div>
    @endif
    @error('foto')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group col-md-4">
    <label for="estado" class="form-label">Estado</label>
    <select name="estado" class="form-control" id="estado" required>
      <option value="1" {{ old('estado', $festival->estado ?? '') == 1 ? 'selected' : '' }}>Activo</option>
      <option value="0" {{ old('estado', $festival->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo</option>
    </select>
    @error('estado')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>


</div>
