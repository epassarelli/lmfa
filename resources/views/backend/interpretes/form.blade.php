<x-backend.form-help section="interprete" />
@php
  $isAdminEdit = $action == 'edit' && Auth::user()->hasRole('administrador');
  $currentImage = isset($interprete) ? $interprete->images->first() : null;
  $legacyPhoto = isset($interprete) && ! $currentImage && filled($interprete->foto ?? null) ? $interprete->foto : null;
@endphp
<div class="row">
  {{-- Fila 1 (grilla de 12): Nombre 4 · Slug 3 · Tipo 2 · Foto 3 --}}
  <div class="col-md-6 col-xl-4">
    <x-input name="interprete" label="Nombre del artista o grupo" :value="$interprete->interprete ?? ''" onkeyup="autocompleteSlug(this, '#slug')" required />
  </div>

  <div class="col-md-6 col-xl-3">
    <x-input name="slug" label="Slug" :value="$interprete->slug ?? ''" help="Opcional. Se completa sola desde el nombre. Es parte del enlace público: cambiala solo si hace falta." />
  </div>

  <div class="col-md-6 col-xl-2">
    <x-select name="artist_type" label="Tipo de artista" :options="['soloist' => 'Solista', 'group' => 'Grupo']" :selected="$interprete->artist_type ?? ''" />
  </div>

  <div class="col-md-6 col-xl-3">
    <div class="form-group">
      <label for="foto">Foto</label>
      <x-backend.image-hint profile="artist" mode="tip" />
      <div class="d-flex align-items-start">
        @if($currentImage)
          <x-optimized-image :image="$currentImage" variant="main" style="height: 36px; width: auto;" class="img-thumbnail mr-2" />
        @elseif($legacyPhoto)
          <img src="{{ asset('storage/interpretes/'.$legacyPhoto) }}" alt="Foto actual" style="height: 36px; width: auto;" class="img-thumbnail mr-2">
        @endif
        <div class="custom-file flex-grow-1">
          <input type="file" name="foto" class="custom-file-input" id="foto" accept="image/jpeg,image/png,image/webp">
          <label class="custom-file-label" for="foto">{{ ($currentImage || $legacyPhoto) ? 'Elegir nueva foto...' : 'Elegir foto...' }}</label>
        </div>
      </div>
      <x-backend.image-hint profile="artist" mode="warnings" />
      @error('foto') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
  </div>

  {{-- Fila 2: Bajada 6 · Correo 3 · Teléfono 3 (con Estado para el administrador: 4 · 3 · 3 · 2) --}}
  <div class="col-md-12 {{ $isAdminEdit ? 'col-xl-4' : 'col-xl-6' }}">
    <x-input name="excerpt" label="Bajada / resumen editorial" :value="$interprete->excerpt ?? ''" />
  </div>

  <div class="col-md-6 col-xl-3">
    <x-input name="correo" label="Correo" type="email" :value="$interprete->correo ?? ''" />
  </div>

  <div class="col-md-6 col-xl-3">
    <x-input name="telefono" label="Teléfono" :value="$interprete->telefono ?? ''" />
  </div>

  @if ($isAdminEdit)
    <div class="col-md-6 col-xl-2">
      <x-select name="estado" label="Estado" :options="['1' => 'Activo', '0' => 'Inactivo']" :selected="$interprete->estado ?? ''" />
    </div>
  @endif

  {{-- Fila 3: Facebook 2 · Instagram 2 · X 2 · YouTube 2 · Sitio web 4 --}}
  <div class="col-md-6 col-xl-2">
    <x-input name="facebook" label="Facebook" :value="$interprete->facebook ?? ''" />
  </div>

  <div class="col-md-6 col-xl-2">
    <x-input name="instagram" label="Instagram" :value="$interprete->instagram ?? ''" />
  </div>

  <div class="col-md-6 col-xl-2">
    <x-input name="twitter" label="X" :value="$interprete->twitter ?? ''" />
  </div>

  <div class="col-md-6 col-xl-2">
    <x-input name="youtube" label="YouTube" :value="$interprete->youtube ?? ''" />
  </div>

  <div class="col-md-12 col-xl-4">
    <x-input name="web" label="Sitio web oficial" :value="$interprete->web ?? ''" />
  </div>

  {{-- Fila 4: Biografía 12 --}}
  <div class="col-12">
    <x-textarea name="biografia" label="Biografía" :value="$interprete->biografia ?? ''" editor editor-profile="editorial-body" :required="true" />
  </div>
</div>

<div class="row">
  <x-backend.seo-box noun="este artista">
    <div class="col-md-6">
      <x-input name="seo_title" label="Título en Google" :value="$interprete->seo_title ?? ''" help="Si queda vacío se usa el nombre del artista." />
    </div>
    <div class="col-md-6">
      <x-input name="meta_description" label="Resumen en Google" :value="$interprete->meta_description ?? ''" help="Si queda vacío se arma con la bajada o la biografía." />
    </div>
    <div class="col-md-12">
      <x-input name="image_alt" label="Descripción de la foto" :value="$interprete->image_alt ?? ''" help="Dice qué se ve en la foto (para lectores de pantalla y Google). Si queda vacío se usa el nombre." />
    </div>
  </x-backend.seo-box>
</div>
