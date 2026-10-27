@extends('adminlte::page')
@section('title', $program->exists ? 'Editar evento' : 'Nuevo evento con jornadas')
@section('content_header')<x-backend.form-header :mode="$program->exists ? 'edit' : 'create'">{{ $program->exists ? 'Editar evento' : 'Nuevo evento con jornadas' }}</x-backend.form-header>@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $program->exists ? route('backend.event-programs.update', $program) : route('backend.event-programs.store') }}">
@csrf @if($program->exists) @method('PUT') @endif
<div class="card"><div class="card-header"><h2 class="card-title">Datos comunes</h2></div><div class="card-body">
  <p class="text-muted">Cargá una vez lo común. Cada jornada conserva su fecha y artistas. Los cambios comunes se reflejan en las jornadas que heredan; se mantienen las excepciones.</p>
  <div class="form-group"><label for="title">Nombre del evento *</label><input id="title" name="title" value="{{ old('title', $program->title) }}" required maxlength="255" class="form-control" placeholder="Ej.: Encuentro folklórico — edición 2027"></div>
  @include('backend.events._venue', ['locationEntity' => $program])
  <div class="row">
    <div class="col-md-6 form-group"><label for="ticket_url">Entradas comunes</label><input type="url" id="ticket_url" name="ticket_url" value="{{ old('ticket_url', $program->ticket_url) }}" class="form-control"></div>
    <div class="col-md-4 form-group"><label for="price_text">Precio común</label><input id="price_text" name="price_text" value="{{ old('price_text', $program->price_text) }}" maxlength="100" class="form-control"></div>
    <div class="col-md-2 form-group"><input type="hidden" name="is_free" value="0"><label class="mt-4"><input type="checkbox" name="is_free" value="1" @checked(old('is_free', $program->is_free))> Gratuito</label></div>
  </div>
  <div class="form-group"><label for="excerpt">Resumen</label><textarea id="excerpt" name="excerpt" maxlength="500" rows="2" class="form-control">{{ old('excerpt', $program->excerpt) }}</textarea></div>
  <div class="form-group"><label for="editor">Detalle común *</label><textarea id="editor" name="body" rows="6" class="form-control" data-ckeditor-profile="editorial-body">{{ old('body', $program->body) }}</textarea></div>
  <div class="form-group"><label for="imagen_destacada">Imagen común</label><input type="file" id="imagen_destacada" name="imagen_destacada" accept="image/*" class="form-control-file"><small class="text-muted d-block">Se usa en las jornadas sin imagen propia.</small><x-backend.image-hint profile="event" />@if($program->images->isNotEmpty())<x-optimized-image :image="$program->images->first()" variant="card" class="img-thumbnail mt-2" style="max-height:180px" />@endif</div>
  @if(auth()->user()->canPublish())
  <div class="row"><div class="col-md-6 form-group"><label for="editorial_status">Estado de la ficha del evento</label><select id="editorial_status" name="editorial_status" class="form-control">@foreach(['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'] as $value => $label)<option value="{{ $value }}" @selected(old('editorial_status', $program->editorial_status ?? 'draft') === $value)>{{ $label }}</option>@endforeach</select></div>
  <div class="col-md-6 form-group"><label for="published_at">Publicar desde</label><input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $program->published_at?->format('Y-m-d\TH:i')) }}" class="form-control"></div></div>
  <p class="small text-muted">Al crear, las jornadas tienen este estado inicial. Al editar, no se cambia el estado de las jornadas existentes: revisalas y publicalas individualmente.</p>
  @else<p class="alert alert-info">El evento, sus jornadas y cualquier lugar nuevo requieren revisión editorial.</p>@endif
</div></div>
@if(!$program->exists)
<div class="card"><div class="card-header"><h2 class="card-title">Jornadas</h2></div><div class="card-body">
  <p>Agregá únicamente las jornadas confirmadas. El fin es opcional; una noche puede terminar al día siguiente.</p>
  <div id="sessions">@foreach(old('sessions', [['title' => '', 'start_at' => '']]) as $index => $session) @include('backend.event-programs._session') @endforeach</div>
  <button type="button" id="add-session" class="btn btn-outline-primary">Agregar jornada</button>
  <template id="session-template">@include('backend.event-programs._session', ['index' => '__INDEX__', 'session' => []])</template>
</div></div>
@endif
<div class="mb-4"><x-backend.form-actions :back="route('backend.events.index')" /></div>
</form>
@if($program->exists)
<div class="card"><div class="card-header"><h2 class="card-title">Jornadas del evento</h2><div class="card-tools"><a href="{{ route('backend.events.create', ['program_id' => $program->id]) }}" class="btn btn-success btn-sm">Agregar jornada</a>@if($program->isPublished()) <a href="{{ route('event-programs.show', $program->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Ver ficha pública</a>@endif</div></div>
<div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Jornada</th><th>Inicio</th><th>Artistas</th><th>Estado</th><th>Excepciones</th><th></th></tr></thead><tbody>
@forelse($sessions as $event)<tr><td>{{ $event->title }}</td><td>{{ $event->start_at?->format('d/m/Y H:i') }}</td><td>{{ $event->interpretes->pluck('interprete')->join(', ') }}</td><td>{{ ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'][$event->editorial_status] ?? $event->editorial_status }}</td><td>{{ implode(', ', array_intersect_key(['body' => 'Detalle', 'excerpt' => 'Resumen', 'venue_id' => 'Lugar', 'ticket_url' => 'Entradas', 'price_text' => 'Precio', 'is_free' => 'Gratuidad'], array_flip($event->program_overrides ?? []))) ?: 'Hereda datos comunes' }}</td><td><a href="{{ route('backend.events.edit', $event) }}" class="btn btn-sm btn-primary">Editar jornada</a></td></tr>@empty<tr><td colspan="6">Todavía no hay jornadas.</td></tr>@endforelse
</tbody></table></div>{{ $sessions->links('vendor.pagination.bootstrap-4') }}</div></div>
@endif
@stop
@section('js')
@include('backend.partials.scripts._select2')
@include('backend.partials.scripts._ckeditor')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rows = document.getElementById('sessions');
  if (!rows) return;
  function initialize(root) { $(root).find('.session-artists,.session-venue').select2({width: '100%', theme: 'bootstrap4'}); }
  initialize(rows);
  let next = Math.max(...Array.from(rows.children).map(row => Number(row.dataset.index)), -1) + 1;
  document.getElementById('add-session').addEventListener('click', function () {
    if (rows.children.length >= 60) { alert('Máximo 60 jornadas por carga.'); return; }
    const template = document.getElementById('session-template').innerHTML.replaceAll('__INDEX__', String(next++));
    rows.insertAdjacentHTML('beforeend', template); initialize(rows.lastElementChild);
  });
  rows.addEventListener('click', function (event) {
    if (event.target.closest('.remove-session') && rows.children.length > 1) event.target.closest('.session-row').remove();
  });
});
</script>
@stop
