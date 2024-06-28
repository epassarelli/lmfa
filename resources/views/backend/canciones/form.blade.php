<div class="form-group">
  <label for="cancion">Nombre de la Canción</label>
  <input type="text" name="cancion" id="cancion" class="form-control"
    value="{{ old('cancion', $cancion->cancion ?? '') }}" required>
</div>

<div class="form-group">
  <label for="letra">Letra</label>
  <textarea name="letra" id="letra" class="form-control" rows="4" required>{{ old('letra', $cancion->letra ?? '') }}</textarea>
</div>

<div class="form-group">
  <label for="youtube">Enlace de YouTube</label>
  <input type="text" name="youtube" id="youtube" class="form-control"
    value="{{ old('youtube', $cancion->youtube ?? '') }}">
</div>

<div class="form-group">
  <label for="spotify">Enlace de Spotify</label>
  <input type="text" name="spotify" id="spotify" class="form-control"
    value="{{ old('spotify', $cancion->spotify ?? '') }}">
</div>

<div class="form-group">
  <label for="interprete_id">Intérprete</label>
  <select name="interprete_id" id="interprete_id" class="form-control" required>
    @foreach ($interpretes as $interprete)
      <option value="{{ $interprete->id }}"
        {{ isset($cancion) && $cancion->interprete_id == $interprete->id ? 'selected' : '' }}>
        {{ $interprete->nombre }}
      </option>
    @endforeach
  </select>
</div>

<div class="form-group">
  <label for="album_id">Álbum</label>
  <select name="album_id" id="album_id" class="form-control" required>
    @if (isset($albums))
      @foreach ($albums as $album)
        <option value="{{ $album->id }}"
          {{ isset($cancion) && $cancion->album_id == $album->id ? 'selected' : '' }}>
          {{ $album->nombre }}
        </option>
      @endforeach
    @endif
  </select>
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $cancion->slug ?? '') }}" required>
  </div>
@endif

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($cancion) ? $cancion->publicar->format('Y-m-d\TH:i') : '') }}" required>
</div>
