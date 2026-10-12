<x-backend.form-help section="interprete" :open="$action !== 'edit'" />
<div class="row">
  <div class="col-md-4">
    <x-input name="interprete" label="Nombre del artista o grupo" :value="$interprete->interprete ?? ''" onkeyup="autocompleteSlug(this, '#slug')" required />
  </div>

  <div class="col-md-4">
    <x-input name="slug" label="Dirección web" :value="$interprete->slug ?? ''" required />
    <small class="form-text text-muted" style="margin-top:-12px">Se completa sola desde el nombre. Es parte del enlace público: cambiala solo si hace falta.</small>
  </div>

  <div class="col-md-4">
    <x-file name="foto" label="Foto" :value="$interprete->foto ?? null" path="interpretes" :required="false" />
    <x-backend.image-hint profile="artist" />
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

<x-textarea name="biografia" label="Biografía" :value="$interprete->biografia ?? ''" editor editor-profile="editorial-body" :required="true" />

<div class="row mt-3">
  <x-backend.seo-box noun="este artista">
    <div class="col-md-6">
      <x-input name="seo_title" label="Título en Google" :value="$interprete->seo_title ?? ''" />
      <small class="form-text text-muted">Si queda vacío se usa el nombre del artista.</small>
    </div>
    <div class="col-md-6">
      <x-input name="meta_description" label="Resumen en Google" :value="$interprete->meta_description ?? ''" />
      <small class="form-text text-muted">Si queda vacío se arma con la bajada o la biografía.</small>
    </div>
    <div class="col-md-12">
      <x-input name="image_alt" label="Descripción de la foto" :value="$interprete->image_alt ?? ''" />
      <small class="form-text text-muted">Dice qué se ve en la foto (para lectores de pantalla y Google). Si queda vacío se usa el nombre.</small>
    </div>
  </x-backend.seo-box>
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
