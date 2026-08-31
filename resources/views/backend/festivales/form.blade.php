<div class="row">
  <div class="col-md-8">
    <div class="form-group">
      <label for="title">Titulo del Festival <span class="text-danger">*</span></label>
      <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $festival->title ?? '') }}" onkeyup="autocompleteSlug(this, '#slug')" required>
      @error('title') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $festival->slug ?? '') }}">
      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label for="excerpt">Bajada</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="3">{{ old('excerpt', $festival->excerpt ?? '') }}</textarea>
      @error('excerpt') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label for="editor">Contenido <span class="text-danger">*</span></label>
      <textarea name="body" id="editor" class="form-control" rows="10" required data-ckeditor-profile="editorial-body">{{ old('body', $festival->body ?? '') }}</textarea>
      @error('body') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="province_id">Provincia <span class="text-danger">*</span></label>
      <select name="province_id" id="province_id" class="form-control" required>
        <option value="">Seleccionar</option>
        @foreach ($provincias as $provincia)
          <option value="{{ $provincia->id }}" @selected((int) old('province_id', $festival->province_id ?? 0) === (int) $provincia->id)>
            {{ $provincia->nombre }}
          </option>
        @endforeach
      </select>
      @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="locality_id">Localidad</label>
      <select name="locality_id" id="locality_id" class="form-control select2" data-placeholder="Seleccionar localidad">
        <option value="">Sin localidad</option>
        @foreach ($localities as $locality)
          <option value="{{ $locality->id }}" @selected((int) old('locality_id', $festival->locality_id ?? 0) === (int) $locality->id)>
            {{ $locality->name }}
          </option>
        @endforeach
      </select>
      @error('locality_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="mes_id">Mes <span class="text-danger">*</span></label>
      <select name="mes_id" id="mes_id" class="form-control" required>
        <option value="">Seleccionar</option>
        @foreach ($meses as $mes)
          <option value="{{ $mes->id }}" @selected((int) old('mes_id', $festival->mes_id ?? 0) === (int) $mes->id)>
            {{ $mes->nombre }}
          </option>
        @endforeach
      </select>
      @error('mes_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="foto">Imagen destacada</label>
      <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">\n      <small class="form-text text-muted">JPEG, PNG o WebP. Maximo 5 MB.</small>
      @if (isset($festival) && $festival->images->isNotEmpty())
        <div class="mt-2 text-center">
          <x-optimized-image :image="$festival->images->first()" variant="card" style="max-height: 80px;" class="img-thumbnail" />
        </div>
      @elseif (isset($festival) && $festival->featured_image_path)
        <div class="mt-2">
          <img src="{{ asset('storage/' . $festival->featured_image_path) }}" alt="Foto de {{ $festival->title }}" style="max-height: 80px;">
        </div>
      @endif
      @error('foto') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label for="published_at">Fecha de publicacion</label>
      <input type="datetime-local" name="published_at" id="published_at" class="form-control"
        value="{{ old('published_at', optional($festival->published_at)->format('Y-m-d\TH:i')) }}">
      @error('published_at') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label for="status">Estado</label>
      <select name="status" id="status" class="form-control" required>
        @foreach (['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'] as $value => $label)
          <option value="{{ $value }}" @selected(old('status', $festival->status ?? 'draft') === $value)>{{ $label }}</option>
        @endforeach
      </select>
      @error('status') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12 mt-2">
    <h5 class="border-bottom pb-2">SEO y metadatos</h5>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="seo_title">Titulo SEO</label>
      <input type="text" name="seo_title" id="seo_title" class="form-control" maxlength="255" value="{{ old('seo_title', $festival->seo_title ?? '') }}">
      <small class="form-text text-muted">Si queda vacio, el frontend usa el titulo del festival.</small>
      @error('seo_title') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="meta_description">Meta descripcion</label>
      <textarea name="meta_description" id="meta_description" class="form-control" rows="3" maxlength="320">{{ old('meta_description', $festival->meta_description ?? '') }}</textarea>
      <small class="form-text text-muted">Si queda vacia, se deriva de la bajada o del cuerpo.</small>
      @error('meta_description') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label for="image_alt">Texto alternativo de imagen</label>
      <input type="text" name="image_alt" id="image_alt" class="form-control" maxlength="255" value="{{ old('image_alt', $festival->image_alt ?? '') }}">
      <small class="form-text text-muted">Describe la imagen de forma breve. Si queda vacio, se usa el titulo del festival.</small>
      @error('image_alt') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  @php
    $selectedNews = old('news_ids', isset($festival) ? $festival->noticias->pluck('id')->all() : []);
    $selectedEvents = old('event_ids', isset($festival) ? $festival->events->pluck('id')->all() : []);
    $selectedArtists = old('interprete_ids', isset($festival) ? $festival->interpretes->pluck('id')->all() : []);
    $selectedKnowledge = old('knowledge_article_ids', isset($festival) ? $festival->knowledgeArticles->pluck('id')->all() : []);
  @endphp

  <div class="col-md-6">
    <div class="form-group">
      <label for="news_ids">Noticias relacionadas</label>
      <select name="news_ids[]" id="news_ids" class="form-control select2" data-placeholder="Seleccionar noticias" multiple>
        @foreach ($relatedNews as $item)
          <option value="{{ $item->id }}" @selected(in_array($item->id, $selectedNews))>{{ $item->title }}</option>
        @endforeach
      </select>
      @error('news_ids') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="event_ids">Eventos relacionados</label>
      <select name="event_ids[]" id="event_ids" class="form-control select2" data-placeholder="Seleccionar eventos" multiple>
        @foreach ($relatedEvents as $item)
          <option value="{{ $item->id }}" @selected(in_array($item->id, $selectedEvents))>{{ $item->title }}</option>
        @endforeach
      </select>
      @error('event_ids') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="interprete_ids">Artistas relacionados</label>
      <select name="interprete_ids[]" id="interprete_ids" class="form-control select2" data-placeholder="Seleccionar artistas" multiple>
        @foreach ($relatedArtists as $item)
          <option value="{{ $item->id }}" @selected(in_array($item->id, $selectedArtists))>{{ $item->interprete }}</option>
        @endforeach
      </select>
      @error('interprete_ids') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="knowledge_article_ids">Entradas de Enciclopedia relacionadas</label>
      <select name="knowledge_article_ids[]" id="knowledge_article_ids" class="form-control select2" data-placeholder="Seleccionar articulos" multiple>
        @foreach ($relatedKnowledgeArticles as $item)
          <option value="{{ $item->id }}" @selected(in_array($item->id, $selectedKnowledge))>{{ $item->title }}</option>
        @endforeach
      </select>
      @error('knowledge_article_ids') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>
</div>
