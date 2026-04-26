<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="titulo">Título de la Noticia <span class="text-danger">*</span></label>
      <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $news->title ?? '') }}" required>
      @error('titulo') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="slug">Slug / URL Amigable</label>
      <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $news->slug ?? '') }}" required>
      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="categoria_id">Categoría <span class="text-danger">*</span></label>
      <select name="categoria_id" id="categoria_id" class="form-control" required>
        <option value="">— Seleccionar —</option>
        @foreach ($categorias as $categoria)
          <option value="{{ $categoria->id }}" {{ old('categoria_id', $news->categoria_id ?? '') == $categoria->id ? 'selected' : '' }}>
            {{ $categoria->nombre }}
          </option>
        @endforeach
      </select>
      @error('categoria_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="foto">Imagen Principal</label>
      <div class="custom-file">
        <input type="file" name="foto" id="foto" class="custom-file-input">
        <label class="custom-file-label" for="foto">Elegir imagen...</label>
      </div>
      @error('foto') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label for="noticia">Cuerpo de la noticia <span class="text-danger">*</span></label>
      <textarea name="noticia" id="noticia" class="form-control summernote" rows="10">{{ old('noticia', $news->body ?? '') }}</textarea>
      @error('noticia') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="interprete_principal_id">Intérprete Principal</label>
      <select name="interprete_principal_id" id="interprete_principal_id" class="form-control select2">
        <option value="">— Ninguno —</option>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}" {{ old('interprete_principal_id', $news->interprete_id ?? '') == $interprete->id ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="col-md-8">
    <div class="form-group">
      <label for="interprete_secundarios">Intérpretes Secundarios</label>
      <select name="interprete_secundarios[]" id="interprete_secundarios" class="form-control select2" multiple>
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ in_array($interprete->id, old('interprete_secundarios', isset($news) ? $news->interpretes->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  @if(Auth::user()->canPublish())
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación <span class="text-danger">*</span></label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="estado" class="form-label">Estado</label>
      <select name="estado" class="form-control" id="estado" required>
        <option value="1" {{ old('estado', $news->estado ?? '') == 1 ? 'selected' : '' }}>Activo (Público)</option>
        <option value="0" {{ old('estado', $news->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo (Borrador)</option>
      </select>
    </div>
  </div>
  @else
    <input type="hidden" name="estado" value="0">
    <div class="col-md-8">
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle mr-2"></i> Tu noticia será revisada por un moderador antes de ser publicada.
        </div>
    </div>
  @endif
</div>
