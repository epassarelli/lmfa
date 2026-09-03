@extends('adminlte::page')
@section('title', 'Programas de radio')
@section('content_header')<h1>Programas de radio</h1>@stop
@section('content')
<div class="card card-outline card-primary">
  <div class="card-header"><h3 class="card-title">Folklore en radios y streams</h3><div class="card-tools"><a class="btn btn-success btn-sm" href="{{ route('backend.radios.programs.create') }}">Crear programa</a></div></div>
  <div class="card-body">
    <form class="row mb-3" method="GET">
      <div class="col-md-3 mb-2"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Buscar programa"></div>
      <div class="col-md-3 mb-2"><select class="form-control select2" name="signal_id"><option value="">Cualquier señal</option>@foreach($signals as $signal)<option value="{{ $signal->id }}" @selected((int) request('signal_id') === $signal->id)>{{ $signal->title }}</option>@endforeach</select></div>
      <div class="col-md-2 mb-2"><select class="form-control" name="status"><option value="">Estado</option>@foreach(['draft','approved','published','archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
      <div class="col-md-2 mb-2"><select class="form-control" name="verification"><option value="">Verificación</option>@foreach(['pending','verified','outdated'] as $status)<option value="{{ $status }}" @selected(request('verification') === $status)>{{ $status }}</option>@endforeach</select></div>
      <div class="col-md-2 mb-2"><select class="form-control" name="quality"><option value="">Calidad</option><option value="missing_slots" @selected(request('quality') === 'missing_slots')>Sin grilla</option><option value="missing_seo" @selected(request('quality') === 'missing_seo')>Sin SEO</option></select></div>
      <div class="col-md-12"><button class="btn btn-primary">Filtrar</button> <a class="btn btn-default" href="{{ route('backend.radios.programs.index') }}">Limpiar</a></div>
    </form>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Programa</th><th>Señal</th><th>Plataforma</th><th>Franjas</th><th>Estado</th><th>Verificación</th><th></th></tr></thead><tbody>
      @forelse($programs as $program)<tr><td>{{ $program->title }}</td><td>{{ $program->signal?->title ?? 'Independiente' }}</td><td>{{ $program->platform ?? '-' }}</td><td>{{ $program->slots_count }}</td><td>{{ $program->editorial_status }}</td><td>{{ $program->verification_status }}</td><td class="text-right">@can('view', $program)<a class="btn btn-info btn-sm" href="{{ route('backend.radios.programs.preview', $program) }}">Vista previa</a>@endcan @can('update', $program)<a class="btn btn-warning btn-sm" href="{{ route('backend.radios.programs.edit', $program) }}">Editar</a>@endcan @can('publish', $program) @if($program->editorial_status !== 'published')<form class="d-inline" method="POST" action="{{ route('backend.radios.programs.publish', $program) }}">@csrf<button class="btn btn-success btn-sm">Publicar</button></form>@else<form class="d-inline" method="POST" action="{{ route('backend.radios.programs.unpublish', $program) }}">@csrf<button class="btn btn-secondary btn-sm">Despublicar</button></form>@endif @endcan</td></tr>@empty<tr><td colspan="7" class="text-center text-muted">No hay programas para los filtros seleccionados.</td></tr>@endforelse
    </tbody></table></div>{{ $programs->links() }}
  </div>
</div>
@stop
