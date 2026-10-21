<x-backend.form-help section="album" />
@php
  $isAdmin = Auth::user()->hasRole('administrador');
  $currentImage = isset($album) ? $album->images->first() : null;
  $legacyImage = isset($album) && ! $currentImage && $album->foto ? $album->foto : null;
@endphp
<div class="row">
  {{-- Fila 1 (grilla de 12): Nombre 4 · Intérprete 3 · Año 2 · Tipo 3 --}}
  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="album">Nombre del Álbum <span class="text-danger">*</span></label>
      <input type="text" name="album" id="album" class="form-control"
        value="{{ old('album', $album->album ?? '') }}" required>
      @error('album')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="interprete_id">Intérprete <span class="text-danger">*</span></label>
      <select name="interprete_id" id="interprete_id" class="form-control select2" required>
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

  <div class="col-md-3 col-xl-2">
    <div class="form-group">
      <label for="anio">Año <span class="text-danger">*</span></label>
      <input type="number" name="anio" id="anio" min="1900" max="2100" step="1"
        class="form-control" value="{{ old('anio', $album->anio ?? '') }}" required>
      @error('anio')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-3 col-xl-3">
    <div class="form-group">
      <label for="album_type">Tipo</label>
      <select name="album_type" id="album_type" class="form-control">
        @foreach (['studio' => 'Estudio', 'live' => 'En vivo', 'compilation' => 'Compilado', 'ep' => 'EP', 'single' => 'Single', 'other' => 'Otro'] as $value => $label)
          <option value="{{ $value }}" {{ old('album_type', $album->album_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>

  {{-- Fila 2: Lanzamiento 3 · Sello 3 · Spotify 3 (o 6 sin slug) · Slug 3 (administrador) --}}
  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="release_date">Fecha de lanzamiento</label>
      <input type="date" name="release_date" id="release_date" class="form-control"
        value="{{ old('release_date', isset($album?->release_date) ? $album->release_date->format('Y-m-d') : '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="label">Sello / edición</label>
      <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $album->label ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 {{ $isAdmin ? 'col-xl-3' : 'col-xl-6' }}">
    <div class="form-group">
      <label for="spotify">Enlace de Spotify</label>
      <input type="text" name="spotify" id="spotify" class="form-control"
        value="{{ old('spotify', $album->spotify ?? '') }}">
      @error('spotify')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  @if ($isAdmin)
    <div class="col-md-6 col-xl-3">
      <div class="form-group">
        <label for="slug">Slug</label>
        <x-backend.help-tip text="Opcional. Si queda vacío se genera desde el nombre. Es parte del enlace público." />
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $album->slug ?? '') }}">
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  {{-- Fila 3: Resumen 8 · Foto 4 --}}
  <div class="col-md-12 col-xl-8">
    <div class="form-group">
      <label for="excerpt">Resumen editorial</label>
      <textarea name="excerpt" id="excerpt" rows="3" class="form-control">{{ old('excerpt', $album->excerpt ?? '') }}</textarea>
    </div>
  </div>

  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="foto">Foto</label>
      <x-backend.image-hint profile="album" mode="tip" />
      <div class="d-flex align-items-start">
        @if ($currentImage)
          <x-optimized-image :image="$currentImage" variant="main" style="height: 36px; width: auto;" class="img-thumbnail mr-2" />
        @elseif ($legacyImage)
          <img src="{{ asset('storage/albunes/' . $legacyImage) }}" alt="Foto de {{ $album->album }}" style="height: 36px; width: auto;" class="img-thumbnail mr-2">
        @endif
        <div class="custom-file flex-grow-1">
          <input type="file" name="foto" class="custom-file-input" id="foto" accept="image/jpeg,image/png,image/webp">
          <label class="custom-file-label" for="foto">{{ ($currentImage || $legacyImage) ? 'Elegir nueva foto...' : 'Elegir foto...' }}</label>
        </div>
      </div>
      <x-backend.image-hint profile="album" mode="warnings" />
      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  {{-- Fila 4 (solo al editar): Canciones del disco 6 · Agregar canciones 6 --}}
  @if ($action == 'edit')
    <div class="col-md-12 col-xl-6">
      <label for="canciones-list">Canciones</label>
      <x-backend.help-tip text="Arrastrá las canciones para cambiar el orden. El número de orden no puede repetirse." />
      <ul id="canciones-list" class="list-group mb-3">
        @foreach ($album_canciones as $cancion)
          <li class="list-group-item d-flex justify-content-between align-items-center py-2" style="cursor: grab;">
            <div class="d-flex align-items-center">
              <input type="hidden" name="canciones[]" value="{{ $cancion->id }}">
              <input type="number" class="form-control mr-2" name="ordenes[]" value="{{ $cancion->pivot->orden }}"
                min="1" style="width: 60px;">
              {{ $cancion->cancion }}
            </div>
            <button type="button" class="btn btn-danger btn-xs remove-cancion">Quitar</button>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="col-md-12 col-xl-6">
      <div class="form-group">
        <label for="canciones-selector">Agregar una canción existente</label>
        <div class="input-group">
          <select id="canciones-selector" class="form-control">
            @foreach ($canciones as $cancion)
              <option value="{{ $cancion->id }}">{{ $cancion->cancion }}</option>
            @endforeach
          </select>
          <div class="input-group-append">
            <button type="button" id="add-cancion" class="btn btn-success">+</button>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="new-cancion">Agregar nueva canción</label>
        <div class="input-group">
          <input type="text" id="new-cancion" class="form-control" placeholder="Nombre de la canción">
          <div class="input-group-append">
            <button type="button" id="create-cancion" class="btn btn-success">Crear y agregar</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  <x-backend.seo-box noun="este disco">
    <div class="col-md-6">
      <div class="form-group">
        <label for="seo_title">Título en Google</label>
        <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $album->seo_title ?? '') }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label for="meta_description">Resumen en Google</label>
        <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description', $album->meta_description ?? '') }}</textarea>
      </div>
    </div>
    <div class="col-md-12">
      <div class="form-group">
        <label for="image_alt">Descripción de la tapa</label>
        <x-backend.help-tip text="Dice qué se ve en la tapa (para lectores de pantalla y Google). Si queda vacío se usa el título del disco." />
        <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $album->image_alt ?? '') }}">
      </div>
    </div>
  </x-backend.seo-box>
</div>
