<div class="row">
  <div class="col-md-6">
    <x-input name="titulo" label="Título" :value="$noticia->titulo ?? ''" onkeyup="autocompleteSlug(this, '#slug')" required />
  </div>

  <div class="col-md-6">
    <x-input name="slug" label="Slug" :value="$noticia->slug ?? ''" required />
  </div>

  <div class="col-md-6">
    <x-select name="categoria_id" label="Categoría" :options="$categorias" :selected="$noticia->categoria_id ?? ''" required />
  </div>

  <div class="col-md-6">
    <x-file name="foto" label="Foto" :value="$noticia->foto ?? null" path="noticias" :required="$action == 'create'" />
    <small class="form-text text-muted">Debe ser formato .jpg, 800 x 450px y no superar los 200 Kb.</small>
  </div>

  <div class="col-md-12">
    <x-textarea name="noticia" label="Contenido" :value="$noticia->noticia ?? ''" editor />
  </div>


  <div class="col-md-4">
    <x-select name="interprete_principal_id" label="Intérprete principal" :options="$interpretes" :selected="old('interprete_principal_id', $noticia->interprete_id ?? '')" />

  </div>

  <div class="col-md-8">
    <x-select name="interprete_secundarios[]" label="Intérpretes secundarios" :options="$interpretes" :selected="old('interprete_secundarios', isset($noticia) ? $noticia->interpretes->pluck('id')->toArray() : [])"
      multiple class="js-example-basic-multiple" />
  </div>


  <!-- Columna 3 -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="publicar">Fecha de Publicación</label>
      <input type="datetime-local" name="publicar" id="publicar" class="form-control"
        value="{{ old('publicar', isset($noticia) ? $noticia->publicar : '') }}" required>
      @error('publicar')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>


  <div class="col-md-4">
    <div class="form-group">
      <label for="estado" class="form-label">Estado</label>
      <select name="estado" class="form-control" id="estado" required>
        <option value="1" {{ old('estado', $noticia->estado ?? '') == 1 ? 'selected' : '' }}>Activo</option>
        <option value="0" {{ old('estado', $noticia->estado ?? '') == 0 ? 'selected' : '' }}>Inactivo</option>
      </select>
      @error('estado')
        <div class="text-danger">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>
