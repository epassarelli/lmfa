@extends('adminlte::page')
@section('title', 'Salas / Lugares')
@section('content_header')<h1>Salas / Lugares</h1>@stop
@section('content')
@foreach (['success' => 'success', 'error' => 'danger'] as $key => $color)
  @if(session($key))<div class="alert alert-{{ $color }}">{{ session($key) }}</div>@endif
@endforeach
<div class="card">
  <div class="card-header"><a href="{{ route('backend.venues.create') }}" class="btn btn-success">Nuevo lugar</a></div>
  <div class="card-body">
    <form method="GET" class="form-inline mb-3">
      <label for="search" class="sr-only">Buscar lugares</label><input id="search" name="search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Nombre, localidad o dirección">
      <label for="status" class="sr-only">Estado</label><select id="status" name="status" class="form-control mr-2"><option value="">Todos los estados</option>@foreach(['active' => 'Activo', 'pending' => 'Pendiente', 'inactive' => 'Inactivo'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
      <button class="btn btn-primary">Buscar</button>
    </form>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Lugar</th><th>Ubicación</th><th>Estado</th><th>Eventos</th><th>Acciones</th></tr></thead><tbody>
    @forelse($venues as $venue)<tr>
      <td>{{ $venue->name }}</td><td>{{ $venue->address }}<br>{{ $venue->city }} — {{ $venue->provincia?->nombre }}</td>
      <td>{{ ['active' => 'Activo', 'pending' => 'Pendiente de revisión', 'inactive' => 'Inactivo'][$venue->status] ?? $venue->status }}</td><td>{{ $venue->events_count }}</td>
      <td><a class="btn btn-sm btn-primary" href="{{ route('backend.venues.edit', $venue) }}">Editar</a>
      <form method="POST" action="{{ route('backend.venues.destroy', $venue) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este lugar sin usos?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td>
    </tr>@empty<tr><td colspan="5">No hay lugares con estos filtros.</td></tr>@endforelse
    </tbody></table></div>{{ $venues->links('vendor.pagination.bootstrap-4') }}
  </div>
</div>
@stop
