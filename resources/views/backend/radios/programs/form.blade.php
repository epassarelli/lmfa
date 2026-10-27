@php($slots = old('slots', $program->slots->map(fn ($slot) => $slot->only(['id','weekday','starts_at','ends_at','timezone','is_active']))->all()))

@if($errors->any())<div class="alert alert-danger"><strong>No se pudo guardar.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2">Identidad editorial</h6>
<div class="row">
  {{-- Grilla de 12: Nombre 4 · Slug 2 · Señal asociada 3 · Folklore 3 --}}
  <div class="col-md-12 col-xl-4 form-group"><label for="title">Nombre <span class="text-danger">*</span></label><input id="title" class="form-control" name="title" value="{{ old('title', $program->title) }}" required></div>
  <div class="col-md-4 col-xl-2 form-group"><label for="slug">Slug</label><x-backend.help-tip text="Opcional. Si queda vacío se genera desde el nombre. Es parte del enlace público." /><input id="slug" class="form-control" name="slug" value="{{ old('slug', $program->slug) }}"></div>
  <div class="col-md-8 col-xl-3 form-group"><label for="radio_signal_id">Señal asociada</label><select id="radio_signal_id" class="form-control select2" name="radio_signal_id"><option value="">Programa independiente</option>@foreach($signals as $signal)<option value="{{ $signal->id }}" @selected((int) old('radio_signal_id', $program->radio_signal_id) === $signal->id)>{{ $signal->title }}</option>@endforeach</select></div>
  <div class="col-md-12 col-xl-3 form-group d-flex align-items-end"><input type="hidden" name="is_folklore" value="0"><label class="mb-2 font-weight-normal"><input type="checkbox" name="is_folklore" value="1" @checked((bool) old('is_folklore', $program->exists ? $program->is_folklore : true))> Es un programa dedicado al folklore</label></div>

  {{-- Bajada 4 · Contenido 8 --}}
  <div class="col-md-12 col-xl-4 form-group"><label for="excerpt">Bajada</label><textarea id="excerpt" class="form-control" name="excerpt" rows="8" maxlength="1000">{{ old('excerpt', $program->excerpt) }}</textarea></div>
  <div class="col-md-12 col-xl-8 form-group"><label for="body">Contenido <span class="text-danger">*</span></label><x-backend.help-tip text="Usá H2/H3 y párrafos; nunca H1 dentro del cuerpo." /><textarea id="body" class="form-control" name="body" rows="8" required>{{ old('body', $program->body) }}</textarea></div>
</div>

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Escucha independiente <x-backend.help-tip text="Completá estos campos sólo si el programa no depende de una señal del directorio o tiene un canal propio." /></h6>
<div class="row">
  {{-- Plataforma 4 · URL de escucha 8 --}}
  <div class="col-md-5 col-xl-4 form-group"><label for="platform">Plataforma</label><select id="platform" class="form-control" name="platform"><option value="">Usa la señal asociada</option>@foreach(['sitio_web','stream_directo','youtube','facebook','twitch','tunein','radio_garden','spotify','otra_oficial'] as $platform)<option value="{{ $platform }}" @selected(old('platform', $program->platform) === $platform)>{{ ucfirst(str_replace('_', ' ', $platform)) }}</option>@endforeach</select></div>
  <div class="col-md-7 col-xl-8 form-group"><label for="listening_url">URL de escucha</label><input id="listening_url" type="url" class="form-control" name="listening_url" value="{{ old('listening_url', $program->listening_url) }}"></div>
</div>

<div class="d-flex align-items-center justify-content-between border-bottom pb-1 mb-2 mt-2">
  <h6 class="text-muted text-uppercase font-weight-bold mb-0">Grilla semanal</h6>
  <button class="btn btn-outline-primary btn-xs" id="add-program-slot" type="button"><i class="fas fa-plus"></i> Agregar franja</button>
</div>
<div id="program-slots">@foreach($slots as $i => $slot)@include('backend.radios.programs.slot-row', ['i' => $i, 'slot' => $slot])@endforeach</div>

<h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Fuentes y SEO</h6>
<div class="row">
  <div class="col-md-12 form-group"><label for="source_urls">Fuentes oficiales o verificables <span class="text-danger">*</span></label><x-backend.help-tip text="Una URL por línea." /><textarea id="source_urls" class="form-control" name="source_urls" rows="3" required>{{ implode(PHP_EOL, old('source_urls', $program->source_urls ?? [])) }}</textarea></div>

  <x-backend.seo-box noun="este programa">
    <div class="col-md-4 form-group"><label for="seo_title">Título SEO</label><input id="seo_title" class="form-control" name="seo_title" maxlength="255" value="{{ old('seo_title', $program->seo_title) }}"></div>
    <div class="col-md-8 form-group"><label for="meta_description">Meta description</label><textarea id="meta_description" class="form-control" name="meta_description" maxlength="320" rows="2">{{ old('meta_description', $program->meta_description) }}</textarea></div>
  </x-backend.seo-box>
</div>

@if($canManageEditorialState)
  <h6 class="text-muted text-uppercase font-weight-bold border-bottom pb-1 mb-2 mt-2">Control editorial</h6><div class="row">
    {{-- Estado 2 · Verificación 2 · Método 3 · Responsable 3 · Fecha 2 --}}
    <div class="col-md-6 col-xl-2 form-group"><label for="editorial_status">Estado</label><select id="editorial_status" class="form-control" name="editorial_status">@foreach(['draft','approved','published','archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $program->editorial_status ?: 'draft') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-2 form-group"><label for="verification_status">Verificación</label><select id="verification_status" class="form-control" name="verification_status">@foreach(['pending','verified','outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $program->verification_status ?: 'pending') === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="verification_method">Método</label><select id="verification_method" class="form-control" name="verification_method"><option value="">Sin verificar</option>@foreach(['official_source'=>'Fuente oficial','direct_confirmation'=>'Confirmación directa','editorial_visit'=>'Visita editorial','manual'=>'Revisión manual'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $program->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="verified_by_user_id">Responsable</label><select id="verified_by_user_id" class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $program->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-12 col-xl-2 form-group"><label for="last_verified_at">Fecha de verificación</label><input id="last_verified_at" type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($program->last_verified_at)->format('Y-m-d\TH:i')) }}"></div>
  </div>
@else
  <div class="alert alert-info py-2">Tu aporte se guardará como borrador pendiente. La verificación y publicación corresponden al equipo editorial.</div>
@endif

<template id="program-slot-template">@include('backend.radios.programs.slot-row', ['i' => '__INDEX__', 'slot' => []])</template>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('program-slots');
  const template = document.getElementById('program-slot-template');
  let index = container.querySelectorAll('[data-slot-row]').length;
  document.getElementById('add-program-slot').addEventListener('click', function () { container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index++)); });
  container.addEventListener('click', function (event) { if (event.target.closest('[data-remove-slot]')) event.target.closest('[data-slot-row]').remove(); });
});
</script>
