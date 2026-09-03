@php
  $channels = old('channels', $signal->listeningChannels->map(fn ($channel) => $channel->only(['id','label','channel_type','platform','frequency_band','frequency','url','is_primary','is_active']))->all());
  $modes = old('transmission_modes', $signal->transmission_modes ?: ['streaming']);
@endphp

@if($errors->any())<div class="alert alert-danger"><strong>No se pudo guardar.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<h5 class="mb-3">Identidad editorial</h5>
<div class="row">
  <div class="col-md-8 form-group"><label>Nombre *</label><input class="form-control" name="title" value="{{ old('title', $signal->title) }}" required></div>
  <div class="col-md-4 form-group"><label>Slug</label><input class="form-control" name="slug" value="{{ old('slug', $signal->slug) }}"></div>
  <div class="col-md-12 form-group"><label>Bajada</label><textarea class="form-control" name="excerpt" rows="2" maxlength="1000">{{ old('excerpt', $signal->excerpt) }}</textarea></div>
  <div class="col-md-12 form-group"><label>Contenido *</label><textarea class="form-control" name="body" rows="12" required>{{ old('body', $signal->body) }}</textarea><small class="form-text text-muted">Usá H2/H3 y párrafos; nunca H1 dentro del cuerpo.</small></div>
  <div class="col-md-4 form-group"><label>Foco *</label><select class="form-control" name="editorial_focus"><option value="folklore" @selected(old('editorial_focus', $signal->editorial_focus ?: 'folklore') === 'folklore')>Folklore</option><option value="mixed" @selected(old('editorial_focus', $signal->editorial_focus) === 'mixed')>Generalista con propuesta folklórica</option></select></div>
  <div class="col-md-8 form-group"><label>Medios de emisión *</label><div class="pt-2">@foreach(['air'=>'Aire AM/FM','web'=>'Web','streaming'=>'Streaming'] as $value => $label)<label class="mr-3"><input type="checkbox" name="transmission_modes[]" value="{{ $value }}" @checked(in_array($value, $modes, true))> {{ $label }}</label>@endforeach</div></div>
</div>

<hr><h5 class="mb-3">Territorio y contacto</h5>
<div class="row">
  <div class="col-md-4 form-group"><label>Provincia</label><select class="form-control select2" id="radio-province" name="province_id"><option value="">Sin provincia</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) old('province_id', $signal->province_id) === $province->id)>{{ $province->nombre }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Localidad</label><select class="form-control select2" id="radio-locality" name="locality_id"><option value="">Sin localidad</option>@foreach($localities as $locality)<option value="{{ $locality->id }}" data-province="{{ $locality->province_id }}" @selected((int) old('locality_id', $signal->locality_id) === $locality->id)>{{ $locality->name }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Ciudad</label><input class="form-control" name="city" value="{{ old('city', $signal->city) }}"></div>
  <div class="col-md-6 form-group"><label>Dirección</label><input class="form-control" name="address" value="{{ old('address', $signal->address) }}"></div>
  <div class="col-md-3 form-group"><label>Latitud</label><input class="form-control" type="number" step="0.00000001" name="latitude" value="{{ old('latitude', $signal->latitude) }}"></div>
  <div class="col-md-3 form-group"><label>Longitud</label><input class="form-control" type="number" step="0.00000001" name="longitude" value="{{ old('longitude', $signal->longitude) }}"></div>
  <div class="col-md-4 form-group"><label>Alcance *</label><select class="form-control" name="coverage_scope">@foreach(['local'=>'Local','provincial'=>'Provincial','regional'=>'Regional','national'=>'Nacional','global'=>'Global'] as $value => $label)<option value="{{ $value }}" @selected(old('coverage_scope', $signal->coverage_scope ?: 'local') === $value)>{{ $label }}</option>@endforeach</select></div>
  <div class="col-md-8 form-group"><label>Detalle de cobertura</label><input class="form-control" name="coverage_notes" value="{{ old('coverage_notes', $signal->coverage_notes) }}"></div>
  <div class="col-md-4 form-group"><label>Teléfono</label><input class="form-control" name="phone" value="{{ old('phone', $signal->phone) }}"></div>
  <div class="col-md-4 form-group"><label>Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $signal->email) }}"></div>
  <div class="col-md-4 form-group"><label>Sitio oficial</label><input class="form-control" type="url" name="website" value="{{ old('website', $signal->website) }}"></div>
</div>

<hr><div class="d-flex align-items-center justify-content-between mb-3"><h5 class="mb-0">Canales de escucha</h5><button class="btn btn-outline-primary btn-sm" id="add-radio-channel" type="button">Agregar canal</button></div>
<p class="text-muted">Para aire indicá banda y frecuencia; para canales digitales, plataforma y URL oficial. Sólo un canal puede ser principal.</p>
<div id="radio-channels">@foreach($channels as $i => $channel)@include('backend.radios.signals.channel-row', ['i' => $i, 'channel' => $channel])@endforeach</div>

<hr><h5 class="mb-3">Fuentes, imagen y SEO</h5>
<div class="row">
  <div class="col-md-12 form-group"><label>Fuentes oficiales o verificables * (una URL por línea)</label><textarea class="form-control" name="source_urls" rows="3" required>{{ implode(PHP_EOL, old('source_urls', $signal->source_urls ?? [])) }}</textarea></div>
  <div class="col-md-6 form-group"><label>Ruta de imagen destacada</label><input class="form-control" name="featured_image_path" value="{{ old('featured_image_path', $signal->featured_image_path) }}"></div>
  <div class="col-md-6 form-group"><label>Texto alternativo de imagen</label><input class="form-control" name="image_alt" value="{{ old('image_alt', $signal->image_alt) }}"></div>
  <div class="col-md-6 form-group"><label>Título SEO</label><input class="form-control" name="seo_title" maxlength="255" value="{{ old('seo_title', $signal->seo_title) }}"></div>
  <div class="col-md-6 form-group"><label>Meta description</label><textarea class="form-control" name="meta_description" maxlength="320" rows="2">{{ old('meta_description', $signal->meta_description) }}</textarea></div>
</div>

@if($canManageEditorialState)
  <hr><h5 class="mb-3">Control editorial</h5><div class="row">
    <div class="col-md-3 form-group"><label>Estado</label><select class="form-control" name="editorial_status">@foreach(['draft','approved','published','archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $signal->editorial_status ?: 'draft') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Verificación</label><select class="form-control" name="verification_status">@foreach(['pending','verified','outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $signal->verification_status ?: 'pending') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Método</label><select class="form-control" name="verification_method"><option value="">Sin verificar</option>@foreach(['official_source'=>'Fuente oficial','direct_confirmation'=>'Confirmación directa','editorial_visit'=>'Visita editorial','manual'=>'Revisión manual'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $signal->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Responsable</label><select class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $signal->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-4 form-group"><label>Fecha de verificación</label><input type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($signal->last_verified_at)->format('Y-m-d\TH:i')) }}"></div>
  </div>
@else
  <div class="alert alert-info">Tu aporte se guardará como borrador pendiente. La verificación y publicación corresponden al equipo editorial.</div>
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
    if (event.target.matches('[data-remove-channel]')) event.target.closest('[data-channel-row]').remove();
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
