<x-backend.form-help section="cancion" />
@php $isAdmin = Auth::user()->hasRole('administrador'); @endphp
<div class="row">
  {{-- Fila 1 (grilla de 12): Nombre 4 · Intérprete 3 · Slug 2 · Publicación 3 (administrador); sin ellos, Nombre 6 · Intérprete 6 --}}
  <div class="col-md-12 {{ $isAdmin ? 'col-xl-4' : 'col-xl-6' }}">
    <div class="form-group">
      <label for="cancion">Nombre de la Canción <span class="text-danger">*</span></label>
      <input type="text" name="cancion" id="cancion" class="form-control"
        value="{{ old('cancion', $cancion->cancion ?? '') }}" required>
      @error('cancion')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6 {{ $isAdmin ? 'col-xl-3' : 'col-xl-6' }}">
    <div class="form-group">
      <label for="interprete_id">Intérprete <span class="text-danger">*</span></label>
      <select name="interprete_id" id="interprete_id" class="form-control select2" required>
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
  </div>

  @if ($isAdmin)
    <div class="col-md-3 col-xl-2">
      <div class="form-group">
        <label for="slug">Slug</label>
        <x-backend.help-tip text="Opcional. Si queda vacío se genera desde el nombre. Es parte del enlace público." />
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $cancion->slug ?? '') }}">
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="col-md-3 col-xl-3">
      <div class="form-group">
        <label for="publicar">Fecha de Publicación</label>
        <input type="datetime-local" name="publicar" id="publicar" class="form-control"
          value="{{ old('publicar', isset($cancion) ? $cancion->publicar : '') }}">
        @error('publicar')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  {{-- Fila 2: Compositor 3 · Autor de la letra 3 · Derechos 3 · Instrumental 3 --}}
  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="composer">Compositor/a</label>
      <input type="text" name="composer" id="composer" class="form-control" value="{{ old('composer', $cancion->composer ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="lyricist">Autor/a de la letra</label>
      <input type="text" name="lyricist" id="lyricist" class="form-control" value="{{ old('lyricist', $cancion->lyricist ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="rights_status">Estado de derechos de la letra</label>
      <select name="rights_status" id="rights_status" class="form-control">
        @foreach (['unknown' => 'Desconocido', 'authorized' => 'Autorizada', 'licensed' => 'Licenciada', 'public_domain' => 'Dominio público', 'not_available' => 'No disponible'] as $value => $label)
          <option value="{{ $value }}" {{ old('rights_status', $cancion->rights_status ?? 'unknown') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="col-md-6 col-xl-3 d-flex align-items-end">
    <div class="form-check mb-2">
      <input type="hidden" name="is_instrumental" value="0">
      <input type="checkbox" name="is_instrumental" id="is_instrumental" class="form-check-input" value="1" {{ old('is_instrumental', $cancion->is_instrumental ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_instrumental">Obra instrumental</label>
    </div>
  </div>

  {{-- Fila 3: Fuente de la letra 4 · YouTube 4 · Spotify 4 --}}
  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="lyrics_source_url">Fuente / autorización de la letra</label>
      <input type="url" name="lyrics_source_url" id="lyrics_source_url" class="form-control" value="{{ old('lyrics_source_url', $cancion->lyrics_source_url ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-4">
    <div class="form-group">
      <label for="youtube">Enlace de YouTube</label>
      <input type="text" name="youtube" id="youtube" class="form-control"
        value="{{ old('youtube', $cancion->youtube ?? '') }}">
      @error('youtube')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-4">
    <div class="form-group">
      <label for="spotify">Enlace de Spotify</label>
      <input type="text" name="spotify" id="spotify" class="form-control"
        value="{{ old('spotify', $cancion->spotify ?? '') }}">
      @error('spotify')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  {{-- Fila 4: Resumen 4 · Letra 8 --}}
  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="excerpt">Resumen / contexto de la obra</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="10">{{ old('excerpt', $cancion->excerpt ?? '') }}</textarea>
    </div>
  </div>

  <div class="col-md-12 col-xl-8">
    <div class="form-group">
      <label for="editor">Letra (opcional)</label>
      <x-backend.help-tip text="No cargar letras sin fuente y estado de derechos verificable. Una ficha puede publicarse sin letra." />
      <textarea name="letra" id="editor" class="form-control" rows="10">{{ old('letra', $cancion->letra ?? '') }}</textarea>
      @error('letra')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <x-backend.seo-box noun="esta canción">
    <div class="col-md-6">
      <div class="form-group">
        <label for="seo_title">Título en Google</label>
        <x-backend.help-tip text="Si queda vacío se usa el nombre de la canción." />
        <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $cancion->seo_title ?? '') }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label for="meta_description">Resumen en Google</label>
        <x-backend.help-tip text="Si queda vacío se arma con los datos de la obra." />
        <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description', $cancion->meta_description ?? '') }}</textarea>
      </div>
    </div>
  </x-backend.seo-box>
</div>
