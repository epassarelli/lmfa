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


      <div class="col-md-6">
        <div class="form-group">
          <label for="anio">Año</label>
          <input type="number" name="anio" id="anio" min="1900" max="2100" step="1"
            class="form-control" value="{{ old('anio', $album->anio ?? '') }}" required>
          @error('anio')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>
      </div>


      <div class="col-md-6">
        <div class="form-group">
          <label for="foto">Foto</label>
          <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png">
          <small class="form-text text-muted">La imagen debe ser en formato .jpg y no debe superar los 200KB.</small>
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


    </div>
  @endif

</div>
