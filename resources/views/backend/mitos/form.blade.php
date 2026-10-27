<x-backend.form-help section="mito" />
@php
  $isAdmin = Auth::user()->hasRole('administrador');
  $isEdit = isset($mito);
  $currentImage = $isEdit ? $mito->images->first() : null;
  $legacyImage = $isEdit && ! $currentImage && $mito->foto ? $mito->foto : null;
  // Fila 1 (suma 12): con slug (administrador) Título 4 · Tipo 2 · Región 2 · Slug 2 · Publicación 2; sin slug Título 5 · Tipo 2 · Región 3 · Publicación 2.
  $titleCols = $isAdmin ? 4 : 5;
  $regionCols = $isAdmin ? 2 : 3;
@endphp
<div class="row">
  {{-- Fila 1 (grilla de 12) --}}
  <div class="col-md-12 col-xl-{{ $titleCols }}">
    <div class="form-group">
      <label for="titulo">Título del Mito <span class="text-danger">*</span></label>
      <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $mito->titulo ?? '') }}"
        required>
      @error('titulo')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-4 col-xl-2">
    <div class="form-group">
      <label for="content_type">Tipo</label>
      <select name="content_type" id="content_type" class="form-control">
        <option value="">Sin clasificar</option>
        <option value="myth" @selected(old('content_type', $mito->content_type ?? '') === 'myth')>Mito</option>
        <option value="legend" @selected(old('content_type', $mito->content_type ?? '') === 'legend')>Leyenda</option>
        <option value="urban_legend" @selected(old('content_type', $mito->content_type ?? '') === 'urban_legend')>Leyenda urbana</option>
      </select>
    </div>
  </div>

  <div class="col-md-8 col-xl-{{ $regionCols }}">
    <div class="form-group">
      <label for="region">Región / ámbito cultural</label>
      <input type="text" name="region" id="region" class="form-control" value="{{ old('region', $mito->region ?? '') }}">
    </div>
  </div>

  @if ($isAdmin)
    <div class="col-md-6 col-xl-2">
      <div class="form-group">
        <label for="slug">Slug</label>
        <x-backend.help-tip text="Opcional. Si queda vacío se genera desde el título. Es parte del enlace público." />
        <input type="text" name="slug" id="slug" class="form-control"
          value="{{ old('slug', $mito->slug ?? '') }}">
        @error('slug')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  <div class="col-md-6 col-xl-2">
    <div class="form-group">
      <label for="publicar">Publicación</label>
      <x-backend.help-tip text="Opcional. Fecha y hora de publicación." />
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($mito) && $mito->publicar ? $mito->publicar->format('Y-m-d\TH:i') : '') }}">
      @error('publicar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  {{-- Fila 2: Bajada 8 (o 6 con Estado) · Estado 2 (administrador, al editar) · Foto 4 --}}
  <div class="col-md-12 {{ $isEdit && $isAdmin ? 'col-xl-6' : 'col-xl-8' }}">
    <div class="form-group">
      <label for="excerpt">Bajada / resumen editorial</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="3">{{ old('excerpt', $mito->excerpt ?? '') }}</textarea>
    </div>
  </div>

  @if ($isEdit && $isAdmin)
    <div class="col-md-4 col-xl-2">
      <div class="form-group">
        <label for="estado">Estado</label>
        <select name="estado" id="estado" class="form-control">
          <option value="0" @selected((string) old('estado', $mito->estado) === '0')>Inactivo</option>
          <option value="1" @selected((string) old('estado', $mito->estado) === '1')>Activo</option>
        </select>
        @error('estado')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </div>
  @endif

  <div class="col-md-8 col-xl-4">
    <div class="form-group">
      <label for="foto">Foto</label>
      <x-backend.image-hint profile="mito" mode="tip" />
      <div class="d-flex align-items-start">
        @if ($currentImage)
          <x-optimized-image :image="$currentImage" variant="card" style="height: 36px; width: auto;" class="img-thumbnail mr-2" />
        @elseif ($legacyImage)
          <img src="{{ asset('storage/' . $legacyImage) }}" alt="Foto de {{ $mito->titulo }}" style="height: 36px; width: auto;" class="img-thumbnail mr-2">
        @endif
        <div class="custom-file flex-grow-1">
          <input type="file" name="foto" class="custom-file-input" id="foto" accept="image/jpeg,image/png,image/webp">
          <label class="custom-file-label" for="foto">{{ ($currentImage || $legacyImage) ? 'Elegir nueva foto...' : 'Elegir foto...' }}</label>
        </div>
      </div>
      <x-backend.image-hint profile="mito" mode="warnings" />
      @error('foto')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  {{-- Fila 3: Relato 12 --}}
  <div class="col-12">
    <div class="form-group">
      <label for="mito">Relato / contenido editorial <span class="text-danger">*</span></label>
      <textarea name="mito" id="editor" class="form-control" rows="8" required data-ckeditor-profile="editorial-body">{{ old('mito', $mito->mito ?? '') }}</textarea>
      @error('mito')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <x-backend.seo-box noun="este mito">
    <div class="col-md-6">
      <div class="form-group">
        <label for="seo_title">Título en Google</label>
        <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $mito->seo_title ?? '') }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label for="meta_description">Resumen en Google</label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description', $mito->meta_description ?? '') }}</textarea>
      </div>
    </div>
    <div class="col-md-12">
      <div class="form-group">
        <label for="image_alt">Descripción de la imagen</label>
        <x-backend.help-tip text="Dice qué se ve en la foto (para lectores de pantalla y Google). Si queda vacío se usa el título." />
        <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $mito->image_alt ?? '') }}">
      </div>
    </div>
  </x-backend.seo-box>
</div>
