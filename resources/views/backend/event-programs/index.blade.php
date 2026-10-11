@extends('adminlte::page')
@section('title', 'Eventos con jornadas')
@section('content_header')<h1>Eventos con jornadas</h1>@stop
@section('content')
<div class="card"><div class="card-header"><a href="{{ route('backend.event-programs.create') }}" class="btn btn-success">Nuevo evento con jornadas</a><a href="{{ route('backend.events.index') }}" class="btn btn-default">Eventos</a></div>
<div class="card-body"><form method="GET" class="form-inline mb-3"><label class="sr-only" for="search">Buscar evento</label><input name="search" id="search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Título del evento"><button class="btn btn-primary">Buscar</button></form>
<div class="table-responsive"><table class="table"><thead><tr><th>Evento</th><th>Lugar común</th><th>Jornadas</th><th>Estado de la ficha</th><th>Acciones</th></tr></thead><tbody>
@forelse($programs as $program)<tr><td>{{ $program->title }}</td><td>{{ $program->venue?->name }}</td><td>{{ $program->events_count }}</td><td>{{ ['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'][$program->editorial_status] ?? $program->editorial_status }}</td><td><a href="{{ route('backend.event-programs.edit', $program) }}" class="btn btn-sm btn-primary">Editar / Jornadas</a></td></tr>@empty<tr><td colspan="5">No hay eventos.</td></tr>@endforelse
</tbody></table></div>{{ $programs->links('vendor.pagination.bootstrap-4') }}</div></div>
@stop
