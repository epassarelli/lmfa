<div class="row">
  <div class="col-md-8">
    <div class="form-group">
      <label for="title">Título <span class="text-danger">*</span></label>
      <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $article->title ?? '') }}" onkeyup="autocompleteSlug(this, '#slug')" required>
      @error('title') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="slug">Slug <span class="text-danger">*</span></label>
      <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $article->slug ?? '') }}" required>
      @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="knowledge_category_id">Familia <span class="text-danger">*</span></label>
      <select name="knowledge_category_id" id="knowledge_category_id" class="form-control" required>
        <option value="">— Seleccionar —</option>
        @foreach ($categories as $category)
          <option value="{{ $category->id }}" @selected((int) old('knowledge_category_id', $article->knowledge_category_id ?? 0) === $category->id)>
            {{ $category->name }}
          </option>
        @endforeach
      </select>
      @error('knowledge_category_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="editorial_status">Estado <span class="text-danger">*</span></label>
      <select name="editorial_status" id="editorial_status" class="form-control" required>
        @foreach (['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'] as $value => $label)
          <option value="{{ $value }}" @selected(old('editorial_status', $article->editorial_status ?? 'draft') === $value)>{{ $label }}</option>
        @endforeach
      </select>
      @error('editorial_status') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="published_at">Publicación</label>
      <input type="datetime-local" name="published_at" id="published_at" class="form-control"
        value="{{ old('published_at', isset($article->published_at) ? $article->published_at?->format('Y-m-d\TH:i') : '') }}">
      @error('published_at') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="last_verified_at">Última verificación</label>
      <input type="datetime-local" name="last_verified_at" id="last_verified_at" class="form-control"
        value="{{ old('last_verified_at', isset($article->last_verified_at) ? $article->last_verified_at?->format('Y-m-d\TH:i') : '') }}">
      @error('last_verified_at') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="excerpt">Bajada</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="3">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
      @error('excerpt') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label for="body">Cuerpo <span class="text-danger">*</span></label>
      <textarea name="body" id="editor" class="form-control" rows="12" data-ckeditor-profile="editorial-body">{{ old('body', $article->body ?? '') }}</textarea>
      @error('body') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="image">Imagen destacada</label>
      <div class="custom-file">
        <input type="file" name="image" id="image" class="custom-file-input">
        <label class="custom-file-label" for="image">Elegir imagen...</label>
      </div>
      @error('image') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="image_alt">Texto alternativo</label>
      <input type="text" name="image_alt" id="image_alt" class="form-control" value="{{ old('image_alt', $article->image_alt ?? '') }}">
      @error('image_alt') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="seo_title">SEO title</label>
      <input type="text" name="seo_title" id="seo_title" class="form-control" maxlength="255" value="{{ old('seo_title', $article->seo_title ?? '') }}">
      <small class="text-muted">Objetivo editorial sugerido: hasta 60 caracteres.</small>
      @error('seo_title') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-8">
    <div class="form-group">
      <label for="meta_description">Meta description</label>
      <input type="text" name="meta_description" id="meta_description" class="form-control" maxlength="320" value="{{ old('meta_description', $article->meta_description ?? '') }}">
      <small class="text-muted">Objetivo editorial sugerido: entre 120 y 160 caracteres.</small>
      @error('meta_description') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="primary_keyword">Palabra clave principal</label>
      <input type="text" name="primary_keyword" id="primary_keyword" class="form-control" value="{{ old('primary_keyword', $article->primary_keyword ?? '') }}">
      @error('primary_keyword') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="secondary_keywords">Palabras clave secundarias</label>
      <input type="text" name="secondary_keywords" id="secondary_keywords" class="form-control" value="{{ old('secondary_keywords', $article->secondary_keywords ?? '') }}">
      @error('secondary_keywords') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  @foreach ([
    'interprete_ids' => ['label' => 'Intérpretes', 'collection' => $interpretes, 'value' => 'id', 'text' => 'interprete'],
    'cancion_ids' => ['label' => 'Canciones', 'collection' => $canciones, 'value' => 'id', 'text' => 'cancion'],
    'album_ids' => ['label' => 'Discos', 'collection' => $albums, 'value' => 'id', 'text' => 'album'],
    'festival_ids' => ['label' => 'Festivales', 'collection' => $festivales, 'value' => 'id', 'text' => 'title'],
    'event_ids' => ['label' => 'Eventos', 'collection' => $events, 'value' => 'id', 'text' => 'title'],
    'provincia_ids' => ['label' => 'Provincias', 'collection' => $provincias, 'value' => 'id', 'text' => 'nombre'],
    'related_article_ids' => ['label' => 'Otros artículos evergreen', 'collection' => $relatedArticles, 'value' => 'id', 'text' => 'title'],
  ] as $field => $config)
    <div class="col-md-6">
      <div class="form-group">
        <label for="{{ $field }}">{{ $config['label'] }}</label>
        @php
          $selected = old($field, isset($article) ? $article->{match ($field) {
            'interprete_ids' => 'interpretes',
            'cancion_ids' => 'canciones',
            'album_ids' => 'albums',
            'festival_ids' => 'festivales',
            'event_ids' => 'events',
            'provincia_ids' => 'provincias',
            'related_article_ids' => 'relatedArticles',
          }}->pluck('id')->toArray() : []);
        @endphp
        <select
          name="{{ $field }}[]"
          id="{{ $field }}"
          class="form-control select2"
          data-placeholder="Seleccionar {{ strtolower($config['label']) }}"
          multiple>
          @foreach ($config['collection'] as $item)
            <option value="{{ $item->{$config['value']} }}" @selected(in_array($item->{$config['value']}, $selected))>
              {{ $item->{$config['text']} }}
            </option>
          @endforeach
        </select>
        @error($field) <small class="text-danger">{{ $message }}</small> @enderror
        @error($field . '.*') <small class="text-danger">{{ $message }}</small> @enderror
      </div>
    </div>
  @endforeach
</div>
