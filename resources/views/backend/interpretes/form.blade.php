<div class="row">
  <div class="col-md-4">
    <x-input name="interprete" label="Interprete" :value="$interprete->interprete ?? ''" onkeyup="autocompleteSlug(this, '#slug')" required />
  </div>

  <div class="col-md-4">
    <x-input name="slug" label="Slug" :value="$interprete->slug ?? ''" required />
  </div>

  <div class="col-md-4">
    <x-file name="foto" label="Foto" :value="$interprete->foto ?? null" path="interpretes" :required="$action == 'create'" />
    <small class="form-text text-muted">Debe ser formato .jpg, 400 x 400px y no superar los 200 Kb.</small>
    @if (isset($interprete) && $interprete->images->isNotEmpty())
      <div class="mt-2 text-center">
        <label>Previsualizacion (Nueva):</label><br>
        <x-optimized-image :image="$interprete->images->first()" variant="main" style="width: 100px; height: auto;" class="img-thumbnail" />
      </div>
    @endif
  </div>
</div>

<x-textarea name="biografia" label="Biografia" :value="$interprete->biografia ?? ''" editor editor-profile="editorial-body" />

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
