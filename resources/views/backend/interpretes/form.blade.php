<div class="row">
  <div class="col-md-4">
    <x-input name="interprete" label="Interprete" :value="$interprete->interprete ?? ''" onkeyup="autocompleteSlug(this, '#slug')" required />
  </div>

  <div class="col-md-4">
    <x-input name="slug" label="Slug" :value="$interprete->slug ?? ''" required />
  </div>

  <div class="col-md-4">
    <x-file name="foto" label="Foto" :value="$interprete->foto ?? null" path="interpretes" :required="false" />
    <small class="form-text text-muted">JPEG, PNG o WebP. Máximo 5 MB. Si no se carga una imagen, el frontend utilizará media relacionada o fallback.</small>
    @if (isset($interprete) && $interprete->images->isNotEmpty())
      <div class="mt-2 text-center">
        <label>Previsualizacion (Nueva):</label><br>
        <x-optimized-image :image="$interprete->images->first()" variant="main" style="width: 100px; height: auto;" class="img-thumbnail" />
      </div>
    @endif
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <x-select name="artist_type" label="Tipo de artista" :options="['soloist' => 'Solista', 'group' => 'Grupo']" :selected="$interprete->artist_type ?? ''" />
  </div>
  <div class="col-md-8">
    <x-input name="excerpt" label="Bajada / resumen editorial" :value="$interprete->excerpt ?? ''" />
  </div>
</div>

<x-textarea name="biografia" label="Biografia" :value="$interprete->biografia ?? ''" editor editor-profile="editorial-body" />

<div class="row mt-3">
  <div class="col-md-12">
    <h5 class="border-bottom pb-2">SEO y metadatos</h5>
  </div>
  <div class="col-md-6">
    <x-input name="seo_title" label="Titulo SEO" :value="$interprete->seo_title ?? ''" />
    <small class="form-text text-muted">Si queda vacío se deriva del nombre del artista.</small>
  </div>
  <div class="col-md-6">
    <x-input name="meta_description" label="Meta description" :value="$interprete->meta_description ?? ''" />
    <small class="form-text text-muted">Si queda vacía se deriva de la bajada o biografía.</small>
  </div>
  <div class="col-md-12">
    <x-input name="image_alt" label="Texto alternativo de imagen" :value="$interprete->image_alt ?? ''" />
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <x-input name="correo" label="Correo" type="email" :value="$interprete->correo ?? ''" />
  </div>

  <div class="col-md-4">
    <x-input name="telefono" label="Telefono" :value="$interprete->telefono ?? ''" />
  </div>

  @if ($action == 'edit' && Auth::user()->hasRole('administrador'))
    <div class="col-md-4">
      <x-select name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="$interprete->estado ?? ''" required />
    </div>
  @endif
</div>

<div class="row">
  <div class="col-md-3">
    <x-input name="facebook" label="Facebook" :value="$interprete->facebook ?? ''" />
  </div>

  <div class="col-md-3">
    <x-input name="instagram" label="Instagram" :value="$interprete->instagram ?? ''" />
  </div>

  <div class="col-md-3">
    <x-input name="twitter" label="X" :value="$interprete->twitter ?? ''" />
  </div>

  <div class="col-md-3">
    <x-input name="youtube" label="YouTube" :value="$interprete->youtube ?? ''" />
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <x-input name="web" label="Sitio web oficial" :value="$interprete->web ?? ''" />
  </div>
</div>
