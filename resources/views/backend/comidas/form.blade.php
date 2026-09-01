<div class="form-group">
  <label for="titulo">Título de la Comida</label>
  <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $comida->titulo ?? '') }}"
    required>
  @error('titulo')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

<div class="form-group">
  <label for="excerpt">Bajada / resumen editorial</label>
  <textarea name="excerpt" id="excerpt" class="form-control" rows="2">{{ old('excerpt', $comida->excerpt ?? '') }}</textarea>
  @error('excerpt') <div class="text-danger">{{ $message }}</div> @enderror
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="ingredients">Ingredientes estructurados</label>
      <textarea name="ingredients" id="ingredients" class="form-control" rows="8" placeholder="Una línea por ingrediente">{{ old('ingredients', isset($comida) && is_array($comida->ingredients) ? implode("\n", $comida->ingredients) : '') }}</textarea>
      <small class="form-text text-muted">Una línea por ingrediente. No inventar cantidades ausentes.</small>
      @error('ingredients') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="instructions">Preparación estructurada</label>
      <textarea name="instructions" id="instructions" class="form-control" rows="8" placeholder="Un paso por línea">{{ old('instructions', isset($comida) && is_array($comida->instructions) ? implode("\n", $comida->instructions) : '') }}</textarea>
      <small class="form-text text-muted">Un paso por línea, en orden.</small>
      @error('instructions') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-3">
    <div class="form-group">
      <label for="prep_time_minutes">Preparación (min)</label>
      <input type="number" min="0" max="1440" name="prep_time_minutes" id="prep_time_minutes" class="form-control" value="{{ old('prep_time_minutes', $comida->prep_time_minutes ?? '') }}">
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="cook_time_minutes">Cocción (min)</label>
      <input type="number" min="0" max="1440" name="cook_time_minutes" id="cook_time_minutes" class="form-control" value="{{ old('cook_time_minutes', $comida->cook_time_minutes ?? '') }}">
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="servings">Porciones</label>
      <input type="text" name="servings" id="servings" class="form-control" value="{{ old('servings', $comida->servings ?? '') }}">
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-group">
      <label for="region">Región</label>
      <input type="text" name="region" id="region" class="form-control" value="{{ old('region', $comida->region ?? '') }}">
    </div>
  </div>
</div>

<div class="form-group">
  <label for="receta">Contenido editorial / receta</label>
  <textarea name="receta" id="editor" class="form-control" rows="8" required data-ckeditor-profile="editorial-body">{{ old('receta', $comida->receta ?? '') }}</textarea>
  @error('receta')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
  <small class="form-text text-muted">JPEG, PNG o WebP. Máximo 5 MB. La imagen es opcional.</small>
  @if (isset($comida) && $comida->images->isNotEmpty())
    <div class="mt-2 text-center">
      <label>Previsualización (Nueva):</label><br>
      <x-optimized-image :image="$comida->images->first()" variant="card" style="max-height: 80px;" class="img-thumbnail" />
    </div>
  @elseif (isset($comida) && $comida->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/' . $comida->foto) }}" alt="Foto de {{ $comida->titulo }}" style="max-height: 80px;">
    </div>
  @endif
  @error('foto')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

@if (Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="slug">Slug</label>
    <input type="text" name="slug" id="slug" class="form-control"
      value="{{ old('slug', $comida->slug ?? '') }}" required>
    @error('slug')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>
@endif

<div class="row mt-3">
  <div class="col-md-12"><h5 class="border-bottom pb-2">SEO y metadatos</h5></div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="seo_title">Título SEO</label>
      <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $comida->seo_title ?? '') }}">
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="meta_description">Meta description</label>
      <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description', $comida->meta_description ?? '') }}</textarea>
    </div>
  </div>
  <div class="col-md-12">
    <div class="form-group">
      <label for="image_alt">Texto alternativo de imagen</label>
      <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $comida->image_alt ?? '') }}">
    </div>
  </div>
</div>

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($comida) && $comida->publicar ? $comida->publicar->format('Y-m-d\TH:i') : '') }}">
  @error('publicar')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

@if (isset($comida) && Auth::user()->hasRole('administrador'))
  <div class="form-group">
    <label for="estado">Estado</label>
    <select name="estado" id="estado" class="form-control" required>
      <option value="0" @selected((string) old('estado', $comida->estado) === '0')>Inactiva</option>
      <option value="1" @selected((string) old('estado', $comida->estado) === '1')>Activa</option>
    </select>
    @error('estado')
      <div class="text-danger">{{ $message }}</div>
    @enderror
  </div>
@endif
