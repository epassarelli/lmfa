<div class="row">

  <div class="col md-6">

    <div class="form-group">
      <label for="cancion">Nombre de la Canción</label>
      <input type="text" name="cancion" id="cancion" class="form-control"
        value="{{ old('cancion', $cancion->cancion ?? '') }}" required>
      @error('cancion')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label for="seo_title">Título SEO</label>
      <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $cancion->seo_title ?? '') }}">
    </div>

    <div class="form-group">
      <label for="meta_description">Meta description</label>
      <textarea name="meta_description" id="meta_description" rows="3" class="form-control">{{ old('meta_description', $cancion->meta_description ?? '') }}</textarea>
    </div>

    @if (Auth::user()->hasRole('administrador'))
      <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $cancion->slug ?? '') }}" required>
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    @endif

    <div class="form-group">
      <label for="interprete_id">Intérprete</label>
      <select name="interprete_id" id="interprete_id" class="form-control" required>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ isset($cancion) && $cancion->interprete_id == $interprete->id ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
      @error('interprete_id')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label for="composer">Compositor/a</label>
      <input type="text" name="composer" id="composer" class="form-control" value="{{ old('composer', $cancion->composer ?? '') }}">
    </div>

    <div class="form-group">
      <label for="lyricist">Autor/a de la letra</label>
      <input type="text" name="lyricist" id="lyricist" class="form-control" value="{{ old('lyricist', $cancion->lyricist ?? '') }}">
    </div>

    <div class="form-group">
      <label for="rights_status">Estado de derechos de la letra</label>
      <select name="rights_status" id="rights_status" class="form-control">
        @foreach (['unknown' => 'Desconocido', 'authorized' => 'Autorizada', 'licensed' => 'Licenciada', 'public_domain' => 'Dominio público', 'not_available' => 'No disponible'] as $value => $label)
          <option value="{{ $value }}" {{ old('rights_status', $cancion->rights_status ?? 'unknown') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="lyrics_source_url">Fuente / autorización de la letra</label>
      <input type="url" name="lyrics_source_url" id="lyrics_source_url" class="form-control" value="{{ old('lyrics_source_url', $cancion->lyrics_source_url ?? '') }}">
    </div>

    <div class="form-check mb-3">
      <input type="hidden" name="is_instrumental" value="0">
      <input type="checkbox" name="is_instrumental" id="is_instrumental" class="form-check-input" value="1" {{ old('is_instrumental', $cancion->is_instrumental ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_instrumental">Obra instrumental</label>
    </div>

    <div class="form-group">
      <label for="youtube">Enlace de YouTube</label>
      <input type="text" name="youtube" id="youtube" class="form-control"
        value="{{ old('youtube', $cancion->youtube ?? '') }}">
      @error('youtube')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-group">
      <label for="spotify">Enlace de Spotify</label>
      <input type="text" name="spotify" id="spotify" class="form-control"
        value="{{ old('spotify', $cancion->spotify ?? '') }}">
      @error('spotify')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

  </div>

  <div class="col md-6">

    <div class="form-group">
      <label for="excerpt">Resumen / contexto de la obra</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="4">{{ old('excerpt', $cancion->excerpt ?? '') }}</textarea>
    </div>

    <div class="form-group">
      <label for="letra">Letra (opcional)</label>
      <textarea name="letra" id="editor" class="form-control" rows="10">{{ old('letra', $cancion->letra ?? '') }}</textarea>
      <small class="form-text text-muted">No cargar letras sin fuente y estado de derechos verificable. Una ficha puede publicarse sin letra.</small>
      @error('letra')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>

    @if (Auth::user()->hasRole('administrador'))
      <div class="form-group">
        <label for="publicar">Fecha de Publicación</label>
        <input type="datetime-local" name="publicar" id="publicar" class="form-control"
          value="{{ old('publicar', isset($cancion) ? $cancion->publicar : '') }}">
        @error('publicar')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    @endif

  </div>

</div>









{{-- <div class="form-group">
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
  @error('album_id')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div> --}}
