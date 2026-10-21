<x-backend.form-help section="comida" />
@php
  $isAdmin = Auth::user()->hasRole('administrador');
  $isEdit = isset($comida);
  // Anchos de la fila 1 (suman 12 según lo que ve cada rol): título · slug (admin) · publicación · estado (admin, al editar).
  $titleCols = $isAdmin ? ($isEdit ? 4 : 5) : 8;
  $slugCols = $isEdit ? 2 : 3;
  $publishCols = $isAdmin ? ($isEdit ? 3 : 4) : 4;
  $currentImage = $isEdit ? $comida->images->first() : null;
  $legacyImage = $isEdit && ! $currentImage && $comida->foto ? $comida->foto : null;
@endphp
<div class="row">
  {{-- Fila 1 (grilla de 12): Título · Slug (admin) · Publicación · Estado (admin, al editar) --}}
  <div class="col-md-12 col-xl-{{ $titleCols }}">
    <div class="form-group">
      <label for="titulo">Título de la Comida <span class="text-danger">*</span></label>
      <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $comida->titulo ?? '') }}"
        required>
      @error('titulo')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  @if ($isAdmin)
    <div class="col-md-6 col-xl-{{ $slugCols }}">
      <div class="form-group">
        <label for="slug">Slug</label>
        <x-backend.help-tip text="Opcional. Si queda vacío se genera desde el título. Es parte del enlace público." />
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $comida->slug ?? '') }}">
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  <div class="col-md-6 col-xl-{{ $publishCols }}">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación</label>
      <x-backend.help-tip text="Opcional." />
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($comida) && $comida->publicar ? $comida->publicar->format('Y-m-d\TH:i') : '') }}">
      @error('publicar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  @if ($isEdit && $isAdmin)
    <div class="col-md-6 col-xl-3">
      <div class="form-group">
        <label for="estado">Estado</label>
        <select name="estado" id="estado" class="form-control">
          <option value="0" @selected((string) old('estado', $comida->estado) === '0')>Inactiva</option>
          <option value="1" @selected((string) old('estado', $comida->estado) === '1')>Activa</option>
        </select>
        @error('estado')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  {{-- Fila 2: Región 3 · Porciones 3 · Preparación 3 · Cocción 3 --}}
  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="region">Región</label>
      <input type="text" name="region" id="region" class="form-control" value="{{ old('region', $comida->region ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="servings">Porciones</label>
      <input type="text" name="servings" id="servings" class="form-control" value="{{ old('servings', $comida->servings ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="prep_time_minutes">Preparación (min)</label>
      <input type="number" min="0" max="1440" name="prep_time_minutes" id="prep_time_minutes" class="form-control" value="{{ old('prep_time_minutes', $comida->prep_time_minutes ?? '') }}">
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="cook_time_minutes">Cocción (min)</label>
      <input type="number" min="0" max="1440" name="cook_time_minutes" id="cook_time_minutes" class="form-control" value="{{ old('cook_time_minutes', $comida->cook_time_minutes ?? '') }}">
    </div>
  </div>

  {{-- Fila 3: Bajada 8 · Foto 4 --}}
  <div class="col-md-12 col-xl-8">
    <div class="form-group">
      <label for="excerpt">Bajada / resumen editorial</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="3">{{ old('excerpt', $comida->excerpt ?? '') }}</textarea>
      @error('excerpt') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="foto">Foto</label>
      <x-backend.image-hint profile="recipe" mode="tip" />
      <div class="d-flex align-items-start">
        @if ($currentImage)
          <x-optimized-image :image="$currentImage" variant="card" style="height: 36px; width: auto;" class="img-thumbnail mr-2" />
        @elseif ($legacyImage)
          <img src="{{ asset('storage/' . $legacyImage) }}" alt="Foto de {{ $comida->titulo }}" style="height: 36px; width: auto;" class="img-thumbnail mr-2">
        @endif
        <div class="custom-file flex-grow-1">
          <input type="file" name="foto" class="custom-file-input" id="foto" accept="image/jpeg,image/png,image/webp">
          <label class="custom-file-label" for="foto">{{ ($currentImage || $legacyImage) ? 'Elegir nueva foto...' : 'Elegir foto...' }}</label>
        </div>
      </div>
      <x-backend.image-hint profile="recipe" mode="warnings" />
      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  {{-- Fila 4: Ingredientes 3 · Preparación 3 · Contenido 6 --}}
  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="ingredients">Ingredientes estructurados</label>
      <x-backend.help-tip text="Una línea por ingrediente. No inventar cantidades ausentes." />
      <textarea name="ingredients" id="ingredients" class="form-control" rows="10" placeholder="Una línea por ingrediente">{{ old('ingredients', isset($comida) && is_array($comida->ingredients) ? implode("\n", $comida->ingredients) : '') }}</textarea>
      @error('ingredients') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="instructions">Preparación estructurada</label>
      <x-backend.help-tip text="Un paso por línea, en orden." />
      <textarea name="instructions" id="instructions" class="form-control" rows="10" placeholder="Un paso por línea">{{ old('instructions', isset($comida) && is_array($comida->instructions) ? implode("\n", $comida->instructions) : '') }}</textarea>
      @error('instructions') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>

  {{-- (el contenido va en la misma fila que los dos textarea anteriores) --}}
  <div class="col-md-12 col-xl-6">
    <div class="form-group">
      <label for="receta">Contenido editorial / receta <span class="text-danger">*</span></label>
      <textarea name="receta" id="editor" class="form-control" rows="8" required data-ckeditor-profile="editorial-body">{{ old('receta', $comida->receta ?? '') }}</textarea>
      @error('receta')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <x-backend.seo-box noun="esta comida">
    <div class="col-md-6">
      <div class="form-group">
        <label for="seo_title">Título en Google</label>
        <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $comida->seo_title ?? '') }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label for="meta_description">Resumen en Google</label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description', $comida->meta_description ?? '') }}</textarea>
      </div>
    </div>
    <div class="col-md-12">
      <div class="form-group">
        <label for="image_alt">Descripción de la imagen</label>
        <x-backend.help-tip text="Dice qué se ve en la foto (para lectores de pantalla y Google). Si queda vacío se usa el título." />
        <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $comida->image_alt ?? '') }}">
      </div>
    </div>
  </x-backend.seo-box>
</div>
