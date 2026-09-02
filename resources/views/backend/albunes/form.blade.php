<div class="row">

  <div class="col-md-8">

    <div class="row">

      <div class="col-md-6">
        <div class="form-group">
          <label for="album">Nombre del Álbum</label>
          <input type="text" name="album" id="album" class="form-control"
            value="{{ old('album', $album->album ?? '') }}" required>
          @error('album')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-6">
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


      <div class="col-md-6">
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
          <label for="anio">Año</label>
          <input type="number" name="anio" id="anio" min="1900" max="2100" step="1"
            class="form-control" value="{{ old('anio', $album->anio ?? '') }}" required>
          @error('anio')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          <label for="album_type">Tipo</label>
          <select name="album_type" id="album_type" class="form-control">
            @foreach (['studio' => 'Estudio', 'live' => 'En vivo', 'compilation' => 'Compilado', 'ep' => 'EP', 'single' => 'Single', 'other' => 'Otro'] as $value => $label)
              <option value="{{ $value }}" {{ old('album_type', $album->album_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          <label for="release_date">Fecha de lanzamiento</label>
          <input type="date" name="release_date" id="release_date" class="form-control"
            value="{{ old('release_date', isset($album?->release_date) ? $album->release_date->format('Y-m-d') : '') }}">
        </div>
      </div>


      <div class="col-md-6">
        <div class="form-group">
          <label for="foto">Foto</label>
          <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
          <small class="form-text text-muted">JPEG, PNG o WebP. Máximo 5 MB.</small>
          @if (isset($album) && $album->images->isNotEmpty())
            <div class="mt-2 text-center">
              <label>Previsualización (Nueva):</label><br>
              <x-optimized-image :image="$album->images->first()" variant="main" style="max-height: 80px;" class="img-thumbnail" />
            </div>
          @elseif (isset($album) && $album->foto)
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


      <div class="col-md-6">
        <div class="form-group">
          <label for="label">Sello / edición</label>
          <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $album->label ?? '') }}">
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group">
          <label for="spotify">Enlace de Spotify</label>
          <input type="text" name="spotify" id="spotify" class="form-control"
            value="{{ old('spotify', $album->spotify ?? '') }}">
          @error('spotify')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
      </div>


      <div class="col-md-12">
        <div class="form-group">
          <label for="excerpt">Resumen editorial</label>
          <textarea name="excerpt" id="excerpt" rows="3" class="form-control">{{ old('excerpt', $album->excerpt ?? '') }}</textarea>
        </div>
      </div>

      <div class="col-md-12 mt-2"><h5 class="border-bottom pb-2">SEO y metadatos</h5></div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="seo_title">Título SEO</label>
          <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $album->seo_title ?? '') }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="meta_description">Meta description</label>
          <textarea name="meta_description" id="meta_description" rows="3" class="form-control">{{ old('meta_description', $album->meta_description ?? '') }}</textarea>
        </div>
      </div>
      <div class="col-md-12">
        <div class="form-group">
          <label for="image_alt">Texto alternativo de portada</label>
          <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $album->image_alt ?? '') }}">
        </div>
      </div>

    </div>


  </div>

  @if ($action == 'edit')


    <div class="col-md-4">

      <!-- Canciones -->
      <label for="canciones">Canciones</label>
      <ul id="canciones-list" class="list-group mb-3">
        @foreach ($album_canciones as $cancion)
          <li class="list-group-item d-flex justify-content-between align-items-center" style="cursor: grab;">
            <div class="d-flex align-items-center">
              <input type="hidden" name="canciones[]" value="{{ $cancion->id }}">
              <input type="number" class="form-control me-2" name="ordenes[]" value="{{ $cancion->pivot->orden }}"
                min="1" style="width: 60px;">
              {{ $cancion->cancion }}
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-cancion">Quitar</button>
          </li>
        @endforeach
      </ul>

      <div class="input-group mb-3">
        <select id="canciones-selector" class="form-select">
          @foreach ($canciones as $cancion)
            <option value="{{ $cancion->id }}">{{ $cancion->cancion }}</option>
          @endforeach
        </select>
        <button type="button" id="add-cancion" class="btn btn-success">+</button>
      </div>

      <div class="form-group">
        <label for="new-cancion">Agregar Nueva Canción</label>
        <input type="text" id="new-cancion" class="form-control" placeholder="Nombre de la canción">
        <button type="button" id="create-cancion" class="btn btn-success mt-2">Crear y Agregar Canción</button>
      </div>

      <ul id="canciones-list" class="list-group mt-3">
        <!-- Aquí se listarán las canciones seleccionadas para el álbum -->
      </ul>

    </div>
  @endif

</div>
