@php
  $channels = old('channels', $signal->listeningChannels->map(fn ($channel) => $channel->only(['id','label','channel_type','platform','frequency_band','frequency','url','is_primary','is_active']))->all());
  $modes = old('transmission_modes', $signal->transmission_modes ?: ['streaming']);
@endphp

@if($errors->any())<div class="alert alert-danger"><strong>No se pudo guardar.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2">Identidad editorial</h6>
<div class="row">
  {{-- Grilla de 12: Nombre 4 · Slug 2 · Foco 3 · Medios 3 --}}
  <div class="col-md-12 col-xl-4 form-group"><label for="title">Nombre <span class="text-danger">*</span></label><input id="title" class="form-control" name="title" value="{{ old('title', $signal->title) }}" required></div>
  <div class="col-md-4 col-xl-2 form-group"><label for="slug">Slug</label><x-backend.help-tip text="Opcional. Si queda vacío se genera desde el nombre. Es parte del enlace público." /><input id="slug" class="form-control" name="slug" value="{{ old('slug', $signal->slug) }}"></div>
  <div class="col-md-8 col-xl-3 form-group"><label for="editorial_focus">Foco <span class="text-danger">*</span></label><select id="editorial_focus" class="form-control" name="editorial_focus"><option value="folklore" @selected(old('editorial_focus', $signal->editorial_focus ?: 'folklore') === 'folklore')>Folklore</option><option value="mixed" @selected(old('editorial_focus', $signal->editorial_focus) === 'mixed')>Generalista con propuesta folklórica</option></select></div>
  <div class="col-md-12 col-xl-3 form-group"><label>Medios de emisión <span class="text-danger">*</span></label><div class="pt-1">@foreach(['air'=>'Aire AM/FM','web'=>'Web','streaming'=>'Streaming'] as $value => $label)<label class="mr-3 mb-0 font-weight-normal"><input type="checkbox" name="transmission_modes[]" value="{{ $value }}" @checked(in_array($value, $modes, true))> {{ $label }}</label>@endforeach</div></div>

  {{-- Bajada 4 · Contenido 8 --}}
  <div class="col-md-12 col-xl-4 form-group"><label for="excerpt">Bajada</label><textarea id="excerpt" class="form-control" name="excerpt" rows="8" maxlength="1000">{{ old('excerpt', $signal->excerpt) }}</textarea></div>
  <div class="col-md-12 col-xl-8 form-group"><label for="body">Contenido <span class="text-danger">*</span></label><x-backend.help-tip text="Usá H2/H3 y párrafos; nunca H1 dentro del cuerpo." /><textarea id="body" class="form-control" name="body" rows="8" required>{{ old('body', $signal->body) }}</textarea></div>
</div>

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Territorio y contacto</h6>
<div class="row">
  {{-- Provincia 3 · Localidad 3 · Ciudad 3 · Alcance 3 --}}
  <div class="col-md-6 col-xl-3 form-group"><label for="radio-province">Provincia</label><select class="form-control select2" id="radio-province" name="province_id"><option value="">Sin provincia</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) old('province_id', $signal->province_id) === $province->id)>{{ $province->nombre }}</option>@endforeach</select></div>
  <div class="col-md-6 col-xl-3 form-group"><label for="radio-locality">Localidad</label><select class="form-control select2" id="radio-locality" name="locality_id"><option value="">Sin localidad</option>@foreach($localities as $locality)<option value="{{ $locality->id }}" data-province="{{ $locality->province_id }}" @selected((int) old('locality_id', $signal->locality_id) === $locality->id)>{{ $locality->name }}</option>@endforeach</select></div>
  <div class="col-md-6 col-xl-3 form-group"><label for="city">Ciudad</label><input id="city" class="form-control" name="city" value="{{ old('city', $signal->city) }}"></div>
  <div class="col-md-6 col-xl-3 form-group"><label for="coverage_scope">Alcance <span class="text-danger">*</span></label><select id="coverage_scope" class="form-control" name="coverage_scope">@foreach(['local'=>'Local','provincial'=>'Provincial','regional'=>'Regional','national'=>'Nacional','global'=>'Global'] as $value => $label)<option value="{{ $value }}" @selected(old('coverage_scope', $signal->coverage_scope ?: 'local') === $value)>{{ $label }}</option>@endforeach</select></div>

  {{-- Dirección 4 · Latitud 2 · Longitud 2 · Detalle de cobertura 4 --}}
  <div class="col-md-12 col-xl-4 form-group"><label for="address">Dirección</label><input id="address" class="form-control" name="address" value="{{ old('address', $signal->address) }}"></div>
  <div class="col-md-6 col-xl-2 form-group"><label for="latitude">Latitud</label><input id="latitude" class="form-control" type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $signal->latitude) }}"></div>
  <div class="col-md-6 col-xl-2 form-group"><label for="longitude">Longitud</label><input id="longitude" class="form-control" type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $signal->longitude) }}"></div>
  <div class="col-md-12 col-xl-4 form-group"><label for="coverage_notes">Detalle de cobertura</label><input id="coverage_notes" class="form-control" name="coverage_notes" value="{{ old('coverage_notes', $signal->coverage_notes) }}"></div>

  {{-- Teléfono 4 · Email 4 · Sitio oficial 4 --}}
  <div class="col-md-4 form-group"><label for="phone">Teléfono</label><input id="phone" class="form-control" name="phone" value="{{ old('phone', $signal->phone) }}"></div>
  <div class="col-md-4 form-group"><label for="email">Email</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email', $signal->email) }}"></div>
  <div class="col-md-4 form-group"><label for="website">Sitio oficial</label><input id="website" class="form-control" type="url" name="website" value="{{ old('website', $signal->website) }}"></div>
</div>

<div class="d-flex align-items-center justify-content-between border-bottom pb-1 mb-2 mt-2">
  <h6 class="text-muted text-uppercase font-weight-bold mb-0">Canales de escucha <x-backend.help-tip text="Para aire indicá banda y frecuencia; para canales digitales, plataforma y URL oficial. Sólo un canal puede ser principal." /></h6>
  <button class="btn btn-outline-primary btn-xs" id="add-radio-channel" type="button"><i class="fas fa-plus"></i> Agregar canal</button>
</div>
<div id="radio-channels">@foreach($channels as $i => $channel)@include('backend.radios.signals.channel-row', ['i' => $i, 'channel' => $channel])@endforeach</div>

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Fuentes, imagen y SEO</h6>
<div class="row">
  {{-- Fuentes 6 · Imagen 3 · Texto alternativo 3 --}}
  <div class="col-md-12 col-xl-6 form-group"><label for="source_urls">Fuentes oficiales o verificables <span class="text-danger">*</span></label><x-backend.help-tip text="Una URL por línea." /><textarea id="source_urls" class="form-control" name="source_urls" rows="3" required>{{ implode(PHP_EOL, old('source_urls', $signal->source_urls ?? [])) }}</textarea></div>
  <div class="col-md-6 col-xl-3 form-group"><label for="featured_image_path">Ruta de imagen destacada</label><input id="featured_image_path" class="form-control" name="featured_image_path" value="{{ old('featured_image_path', $signal->featured_image_path) }}"></div>
  <div class="col-md-6 col-xl-3 form-group"><label for="image_alt">Texto alternativo de imagen</label><input id="image_alt" class="form-control" name="image_alt" value="{{ old('image_alt', $signal->image_alt) }}"></div>

  <x-backend.seo-box noun="esta radio">
    <div class="col-md-4 form-group"><label for="seo_title">Título SEO</label><input id="seo_title" class="form-control" name="seo_title" maxlength="255" value="{{ old('seo_title', $signal->seo_title) }}"></div>
    <div class="col-md-8 form-group"><label for="meta_description">Meta description</label><textarea id="meta_description" class="form-control" name="meta_description" maxlength="320" rows="2">{{ old('meta_description', $signal->meta_description) }}</textarea></div>
  </x-backend.seo-box>
</div>

@if($canManageEditorialState)
  <h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Control editorial</h6><div class="row">
    {{-- Estado 2 · Verificación 2 · Método 3 · Responsable 3 · Fecha 2 --}}
    <div class="col-md-6 col-xl-2 form-group"><label for="editorial_status">Estado</label><select id="editorial_status" class="form-control" name="editorial_status">@foreach(['draft','approved','published','archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $signal->editorial_status ?: 'draft') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-2 form-group"><label for="verification_status">Verificación</label><select id="verification_status" class="form-control" name="verification_status">@foreach(['pending','verified','outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $signal->verification_status ?: 'pending') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="verification_method">Método</label><select id="verification_method" class="form-control" name="verification_method"><option value="">Sin verificar</option>@foreach(['official_source'=>'Fuente oficial','direct_confirmation'=>'Confirmación directa','editorial_visit'=>'Visita editorial','manual'=>'Revisión manual'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $signal->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="verified_by_user_id">Responsable</label><select id="verified_by_user_id" class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $signal->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-12 col-xl-2 form-group"><label for="last_verified_at">Fecha de verificación</label><input id="last_verified_at" type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($signal->last_verified_at)->format('Y-m-d\TH:i')) }}"></div>
  </div>
@else
  <div class="alert alert-info py-2">Tu aporte se guardará como borrador pendiente. La verificación y publicación corresponden al equipo editorial.</div>
@endif

<template id="radio-channel-template">@include('backend.radios.signals.channel-row', ['i' => '__INDEX__', 'channel' => []])</template>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('radio-channels');
  const template = document.getElementById('radio-channel-template');
  let index = container.querySelectorAll('[data-channel-row]').length;
  document.getElementById('add-radio-channel').addEventListener('click', function () {
    container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index++));
  });
  container.addEventListener('click', function (event) {
    if (event.target.closest('[data-remove-channel]')) event.target.closest('[data-channel-row]').remove();
  });
  container.addEventListener('change', function (event) {
    if (!event.target.matches('[data-primary-channel]') || !event.target.checked) return;
    container.querySelectorAll('[data-primary-channel]').forEach(function (checkbox) { if (checkbox !== event.target) checkbox.checked = false; });
  });
  const province = document.getElementById('radio-province');
  const locality = document.getElementById('radio-locality');
  const filterLocalities = function () { const selected = province.value; Array.from(locality.options).forEach(function (option) { if (!option.value) return; option.hidden = selected && option.dataset.province !== selected; }); if (locality.selectedOptions[0]?.hidden) locality.value = ''; };
  province.addEventListener('change', filterLocalities); filterLocalities();
});
</script>
