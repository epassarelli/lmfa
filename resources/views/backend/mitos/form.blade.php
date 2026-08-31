<div class="form-group">
  <label for="titulo">Título del Mito</label>
  <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo', $mito->titulo ?? '') }}"
    required>
  @error('titulo')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

<div class="row">
  <div class="col-md-4">
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
  <div class="col-md-8">
    <div class="form-group">
      <label for="region">Región / ámbito cultural</label>
      <input type="text" name="region" id="region" class="form-control" value="{{ old('region', $mito->region ?? '') }}">
    </div>
  </div>
</div>

<div class="form-group">
  <label for="excerpt">Bajada / resumen editorial</label>
  <textarea name="excerpt" id="excerpt" class="form-control" rows="2">{{ old('excerpt', $mito->excerpt ?? '') }}</textarea>
</div>

<div class="form-group">
  <label for="mito">Relato / contenido editorial</label>
  <textarea name="mito" id="editor" class="form-control" rows="8" required data-ckeditor-profile="editorial-body">{{ old('mito', $mito->mito ?? '') }}</textarea>
  @error('mito')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>

<div class="form-group">
  <label for="foto">Foto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
  <small class="form-text text-muted">JPEG, PNG o WebP. Máximo 5 MB. La imagen es opcional.</small>
  @if (isset($mito) && $mito->images->isNotEmpty())
    <div class="mt-2 text-center">
      <label>Previsualización (Nueva):</label><br>
      <x-optimized-image :image="$mito->images->first()" variant="card" style="max-height: 80px;" class="img-thumbnail" />
    </div>
  @elseif (isset($mito) && $mito->foto)
    <div class="mt-2">
      <img src="{{ asset('storage/' . $mito->foto) }}" alt="Foto de {{ $mito->titulo }}" style="max-height: 80px;">
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
      value="{{ old('slug', $mito->slug ?? '') }}" required>
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
      <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ old('seo_title', $mito->seo_title ?? '') }}">
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label for="meta_description">Meta description</label>
      <textarea name="meta_description" id="meta_description" class="form-control" rows="2">{{ old('meta_description', $mito->meta_description ?? '') }}</textarea>
    </div>
  </div>
  <div class="col-md-12">
    <div class="form-group">
      <label for="image_alt">Texto alternativo de imagen</label>
      <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $mito->image_alt ?? '') }}">
    </div>
  </div>
</div>

<div class="form-group">
  <label for="publicar">Fecha de Publicación</label>
  <input type="datetime-local" name="publicar" id="publicar" class="form-control"
    value="{{ old('publicar', isset($mito) && $mito->publicar ? $mito->publicar->format('Y-m-d\TH:i') : '') }}">
  @error('publicar')
    <div class="text-danger">{{ $message }}</div>
  @enderror
</div>
