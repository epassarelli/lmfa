@php($slots = old('slots', $program->slots->map(fn ($slot) => $slot->only(['id','weekday','starts_at','ends_at','timezone','is_active']))->all()))

@if($errors->any())<div class="alert alert-danger"><strong>No se pudo guardar.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<h5 class="mb-3">Identidad editorial</h5>
<div class="row">
  <div class="col-md-7 form-group"><label>Nombre *</label><input class="form-control" name="title" value="{{ old('title', $program->title) }}" required></div>
  <div class="col-md-5 form-group"><label>Slug</label><input class="form-control" name="slug" value="{{ old('slug', $program->slug) }}"></div>
  <div class="col-md-6 form-group"><label>Señal asociada</label><select class="form-control select2" name="radio_signal_id"><option value="">Programa independiente</option>@foreach($signals as $signal)<option value="{{ $signal->id }}" @selected((int) old('radio_signal_id', $program->radio_signal_id) === $signal->id)>{{ $signal->title }}</option>@endforeach</select></div>
  <div class="col-md-6 form-group d-flex align-items-center pt-3"><input type="hidden" name="is_folklore" value="0"><label><input type="checkbox" name="is_folklore" value="1" @checked((bool) old('is_folklore', $program->exists ? $program->is_folklore : true))> Es un programa dedicado al folklore</label></div>
  <div class="col-md-12 form-group"><label>Bajada</label><textarea class="form-control" name="excerpt" rows="2" maxlength="1000">{{ old('excerpt', $program->excerpt) }}</textarea></div>
  <div class="col-md-12 form-group"><label>Contenido *</label><textarea class="form-control" name="body" rows="12" required>{{ old('body', $program->body) }}</textarea><small class="form-text text-muted">Usá H2/H3 y párrafos; nunca H1 dentro del cuerpo.</small></div>
</div>

<hr><h5 class="mb-3">Escucha independiente</h5>
<p class="text-muted">Completá estos campos sólo si el programa no depende de una señal del directorio o tiene un canal propio.</p>
<div class="row">
  <div class="col-md-4 form-group"><label>Plataforma</label><select class="form-control" name="platform"><option value="">Usa la señal asociada</option>@foreach(['sitio_web','stream_directo','youtube','facebook','twitch','tunein','radio_garden','spotify','otra_oficial'] as $platform)<option value="{{ $platform }}" @selected(old('platform', $program->platform) === $platform)>{{ ucfirst(str_replace('_', ' ', $platform)) }}</option>@endforeach</select></div>
  <div class="col-md-8 form-group"><label>URL de escucha</label><input type="url" class="form-control" name="listening_url" value="{{ old('listening_url', $program->listening_url) }}"></div>
</div>

<hr><div class="d-flex align-items-center justify-content-between mb-3"><h5 class="mb-0">Grilla semanal</h5><button class="btn btn-outline-primary btn-sm" id="add-program-slot" type="button">Agregar franja</button></div>
<div id="program-slots">@foreach($slots as $i => $slot)@include('backend.radios.programs.slot-row', ['i' => $i, 'slot' => $slot])@endforeach</div>

<hr><h5 class="mb-3">Fuentes y SEO</h5>
<div class="row">
  <div class="col-md-12 form-group"><label>Fuentes oficiales o verificables * (una URL por línea)</label><textarea class="form-control" name="source_urls" rows="3" required>{{ implode(PHP_EOL, old('source_urls', $program->source_urls ?? [])) }}</textarea></div>
  <div class="col-md-6 form-group"><label>Título SEO</label><input class="form-control" name="seo_title" maxlength="255" value="{{ old('seo_title', $program->seo_title) }}"></div>
  <div class="col-md-6 form-group"><label>Meta description</label><textarea class="form-control" name="meta_description" maxlength="320" rows="2">{{ old('meta_description', $program->meta_description) }}</textarea></div>
</div>

@if($canManageEditorialState)
  <hr><h5 class="mb-3">Control editorial</h5><div class="row">
    <div class="col-md-3 form-group"><label>Estado</label><select class="form-control" name="editorial_status">@foreach(['draft','approved','published','archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $program->editorial_status ?: 'draft') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Verificación</label><select class="form-control" name="verification_status">@foreach(['pending','verified','outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $program->verification_status ?: 'pending') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Método</label><select class="form-control" name="verification_method"><option value="">Sin verificar</option>@foreach(['official_source'=>'Fuente oficial','direct_confirmation'=>'Confirmación directa','editorial_visit'=>'Visita editorial','manual'=>'Revisión manual'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $program->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Responsable</label><select class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $program->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-4 form-group"><label>Fecha de verificación</label><input type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($program->last_verified_at)->format('Y-m-d\TH:i')) }}"></div>
  </div>
@else
  <div class="alert alert-info">Tu aporte se guardará como borrador pendiente. La verificación y publicación corresponden al equipo editorial.</div>
@endif

<template id="program-slot-template">@include('backend.radios.programs.slot-row', ['i' => '__INDEX__', 'slot' => []])</template>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('program-slots');
  const template = document.getElementById('program-slot-template');
  let index = container.querySelectorAll('[data-slot-row]').length;
  document.getElementById('add-program-slot').addEventListener('click', function () { container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index++)); });
  container.addEventListener('click', function (event) { if (event.target.matches('[data-remove-slot]')) event.target.closest('[data-slot-row]').remove(); });
});
</script>
