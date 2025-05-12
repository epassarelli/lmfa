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

  <div class="col-md-12">
    <x-select name="interprete_id[]" label="Intérpretes" :options="$interpretes" :selected="old('interprete_id', isset($noticia) ? $noticia->interpretes->pluck('id')->toArray() : [])" multiple
      class="js-example-basic-multiple" />

  </div>
</div>
