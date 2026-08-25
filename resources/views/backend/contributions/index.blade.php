@extends('adminlte::page')

@section('title', 'Moderacion de Contribuciones')

@section('content_header')
    <h1>Contribuciones Pendientes</h1>
@stop

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Listado de colaboraciones de usuarios</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <x-admin-listing-toolbar
            :action="route('backend.contributions.admin.index')"
            search-placeholder="Buscar por usuario, contenido, tipo o estado..."
            :sort-options="[
                'created_at' => 'Fecha',
                'status' => 'Estado',
                'contributable_type' => 'Tipo',
            ]"
        >
            <div class="col-md-3">
                <label for="status" class="small text-muted mb-1">Estado</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pendientes</option>
                    <option value="approved" @selected(request('status') === 'approved')>Aprobadas</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rechazadas</option>
                    <option value="auto-applied" @selected(request('status') === 'auto-applied')>Auto aplicadas</option>
                </select>
            </div>
        </x-admin-listing-toolbar>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Contenido</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $contribution)
                    <tr>
                        <td>{{ $contribution->user->name }} ({{ $contribution->user->points }} pts)</td>
                        <td>
                            {{ $contribution->payload['nombre'] ?? ($contribution->payload['titulo'] ?? ($contribution->payload['interprete'] ?? ($contribution->payload['cancion'] ?? ($contribution->payload['title'] ?? 'Sin titulo')))) }}
                        </td>
                        <td>
                            <span class="badge badge-info">{{ class_basename($contribution->contributable_type) }}</span>
                        </td>
                        <td>
                            @php
                                $badgeColors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'auto-applied' => 'primary'
                                ];
                            @endphp
                            <span class="badge badge-{{ $badgeColors[$contribution->status] ?? 'secondary' }}">
                                {{ strtoupper($contribution->status) }}
                            </span>
                        </td>
                        <td>{{ $contribution->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('backend.contributions.show', $contribution->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Revisar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay contribuciones para los filtros seleccionados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white px-0 pb-0 pt-3">
            {{ $contributions->links() }}
        </div>
    </div>
</div>
@stop
