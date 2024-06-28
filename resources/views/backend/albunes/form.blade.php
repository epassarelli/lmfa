<div class="form-group">
  <label for="album">Nombre del Álbum</label>
  <input type="text" name="album" id="album" class="form-control" value="{{ old('album', $album->album ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="anio">Año</label>
  <input type="text" name="anio" id="anio" class="form-control" value="{{ old('anio', $album->anio ?? '') }}"
    required>
</div>

<div class="form-group">
  <label for="spotify">Enlace de Spotify</label>
  <input type="text" name="spotify" id="spotify" class="form-control"
    value="{{ old('spotify', $album->spotify ?? '') }}">
</div>

<div class="form-group">
  <label for="interprete_id">Intérprete</label>
  <select name="interprete_id" class="form-control" required>
    @foreach ($interpretes as $interprete)
      <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
    @endforeach
  </select>
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
  @if (isset($album) && $album->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/albunes/' . $album->foto) }}" alt="Foto de {{ $album->album }}"
        style="max-height: 80px;">
    </div>
  @endif
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $album->slug ?? '') }}" required>
  </div>
@endif

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($album) ? $album->publicar->format('Y-m-d\TH:i') : '') }}" required>
</div>
