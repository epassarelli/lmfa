<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="album">Nombre del Álbum</label>
      <input type="text" name="album" id="album" class="form-control"
        value="{{ old('album', $album->album ?? '') }}" required>
      @error('album')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    @if (Auth::user()->hasRole('administrador'))
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $album->slug ?? '') }}" required>
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    @endif
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="anio">Año</label>
      <input type="number" name="anio" id="anio" min="1900" max="2100" step="1"
        class="form-control" value="{{ old('anio', $album->anio ?? '') }}" required>
      @error('anio')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label for="interprete_id">Intérprete</label>
      <select name="interprete_id" class="form-control" required>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ old('interprete_id', $album->interprete_id ?? '') == $interprete->id ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
      @error('interprete_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="foto">Foto</label>
      <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
      @if (isset($album) && $album->foto)
        <div class="mt-2">
          <img src="{{ asset('storage/albunes/' . $album->foto) }}" alt="Foto de {{ $album->album }}"
            style="max-height: 80px;">
        </div>
      @endif
      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación</label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($album) ? $album->publicar->format('Y-m-d\TH:i') : '') }}" required>
      @error('publicar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>



<div class="form-group">
  <label for="spotify">Enlace de Spotify</label>
  <input type="text" name="spotify" id="spotify" class="form-control"
    value="{{ old('spotify', $album->spotify ?? '') }}">
  @error('spotify')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>
