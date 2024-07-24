<div class="row">

  <div class="col-md-4">
    <div class="mb-3">
      <label for="interprete" class="form-label">Interprete</label>
      <input type="text" name="interprete" class="form-control" id="interprete"
        value="{{ old('interprete', $interprete->interprete ?? '') }}" required>
      @error('interprete')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="mb-3">
      <label for="slug" class="form-label">Slug</label>
      <input type="text" name="slug" class="form-control" id="slug"
        value="{{ old('slug', $interprete->slug ?? '') }}">
      @error('slug')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">

    <div class="mb-3">
      <label for="foto" class="form-label">Foto</label>
      <input type="file" name="foto" class="form-control" id="foto"
        value="{{ old('foto', $interprete->foto ?? '') }}" {{ $action == 'create' ? 'required' : '' }}>
      @if ($action == 'edit')
        @if ($interprete->foto)
          <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}" alt="Foto actual" class="img-fluid mt-2"
            width="40">
        @endif
      @endif

      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

  </div>
</div>



<div class="mb-3">
  <label for="biografia" class="form-label">Biografia</label>
  <textarea name="biografia" class="form-control" id="editor" required>{{ old('biografia', $interprete->biografia ?? '') }}</textarea>
  @error('biografia')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>


<div class="row">
  <div class="col-md-4">
    <div class="mb-3">
      <label for="correo" class="form-label">Correo</label>
      <input type="email" name="correo" class="form-control" id="correo"
        value="{{ old('correo', $interprete->correo ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="mb-3">
      <label for="telefono" class="form-label">Telefono</label>
      <input type="text" name="telefono" class="form-control" id="telefono"
        value="{{ old('telefono', $interprete->telefono ?? '') }}">
    </div>
  </div>


  @if ($action == 'edit' and Auth::user()->hasRole('administrador'))
    <div class="col-md-4">
      <div class="mb-3">
        <label for="estado" class="form-label">Estado</label>
        <select name="estado" class="form-control" id="estado" required>
          <option value="1" {{ old('estado', $interprete->estado ?? '') == 1 ? 'selected' : '' }}>Activo</option>
          <option value="0" {{ old('estado', $interprete->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo
          </option>
        </select>
      </div>
    </div>
  @endif

</div>



<div class="row">
  <div class="col-md-4">
    <div class="mb-3">
      <label for="instagram" class="form-label">Instagram</label>
      <input type="text" name="instagram" class="form-control" id="instagram"
        value="{{ old('instagram', $interprete->instagram ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="mb-3">
      <label for="twitter" class="form-label">Twitter</label>
      <input type="text" name="twitter" class="form-control" id="twitter"
        value="{{ old('twitter', $interprete->twitter ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="mb-3">
      <label for="youtube" class="form-label">YouTube</label>
      <input type="text" name="youtube" class="form-control" id="youtube"
        value="{{ old('youtube', $interprete->youtube ?? '') }}">
    </div>
  </div>
</div>
