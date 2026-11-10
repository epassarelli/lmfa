@extends('adminlte::page')
@section('title', 'Publicaciones en redes')
@section('content_header')<h1>Publicaciones en redes</h1>@stop
@section('content')
<div class="alert alert-info">Destino: {{ config('social_publication.destination') }}. Automatización: {{ config('social_publication.enabled') ? 'habilitada' : 'pausada' }}. Las fechas se muestran en hora argentina.</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card"><div class="card-header">Habilitar o retirar un contenido</div><div class="card-body">
<form method="POST" action="{{ route('social-publications.eligibility') }}">@csrf
<div class="row"><div class="col-md-3"><label for="kind">Tipo</label><select id="kind" name="content_kind" class="form-control"><option value="news">Noticia</option><option value="events">Evento</option></select></div>
<div class="col-md-3"><label for="content-id">ID del contenido</label><input id="content-id" type="number" name="content_id" min="1" required class="form-control"></div>
<div class="col-md-3"><label for="enabled">Acción</label><select id="enabled" name="enabled" class="form-control"><option value="1">Habilitar</option><option value="0">Retirar</option></select></div></div>
<label class="mt-3"><input type="checkbox" name="portal_only_override" value="1"> Autorizo expresamente publicar en redes aunque el contenido indique «solo portal».</label><br>
<button class="btn btn-primary">Aplicar habilitación</button>
</form></div></div>
<form method="GET" class="mb-3"><label>Tipo <select name="content_kind"><option value="">Todos</option>@foreach(['news','events'] as $kind)<option @selected(request('content_kind')===$kind)>{{ $kind }}</option>@endforeach</select></label>
<label>Estado <select name="status"><option value="">Todos</option>@foreach(['pending','reserved','processing','success','failed','uncertain','cancelled'] as $state)<option @selected(request('status')===$state)>{{ $state }}</option>@endforeach</select></label> <button class="btn btn-secondary btn-sm">Filtrar</button></form>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Contenido</th><th>Estado</th><th>Publicación</th><th>Evidencia</th><th>Acción supervisada</th></tr></thead><tbody>
@forelse($targets as $target)<tr><td>{{ $target->ledger_content_kind }} #{{ $target->ledger_content_id }}<br>{{ $target->request?->content?->title ?? 'Contenido retirado' }}</td>
<td>{{ $target->status }}<br>Envíos: {{ $target->attempts_count }}</td><td>{{ $target->published_at?->timezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') ?? 'Sin publicación confirmada' }}<br>
@if($target->external_post_id)<a href="{{ $target->external_url }}" target="_blank" rel="noopener noreferrer">{{ $target->external_post_id }}</a>@endif</td>
<td>{{ $target->template_id }}<br>{{ substr($target->content_hash ?? '',0,12) }}<br>{{ $target->last_error_code }} {{ $target->last_error_message }}</td>
<td>@if(in_array($target->status,['failed','uncertain']))<form method="POST" action="{{ route('social-publications.resolve',$target->id) }}">@csrf
<select name="action" class="form-control form-control-sm">@if($target->status==='failed')<option value="retry">Reintentar fallo seguro</option>@else<option value="published">Verifiqué que se publicó</option><option value="not_published">Verifiqué que NO se publicó</option>@endif</select>
<input name="external_post_id" class="form-control form-control-sm" aria-label="ID remoto" placeholder="ID remoto si se publicó">
<textarea name="reason" required minlength="10" maxlength="500" class="form-control form-control-sm" aria-label="Motivo y evidencia" placeholder="Motivo y evidencia de la verificación"></textarea>
<label><input type="checkbox" name="confirmed" value="1" required> Confirmo la verificación</label><button class="btn btn-secondary btn-sm">Registrar</button></form>@endif</td></tr>
@empty<tr><td colspan="5">No hay contenidos habilitados para este destino.</td></tr>@endforelse
</tbody></table></div>{{ $targets->links() }}
@stop
