@extends('adminlte::page')

@section('title', 'Moderación de Contribuciones')

@section('content_header')
    <h1>Contribuciones Pendientes</h1>
@stop

@section('content')
<div class="card">
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

        <table class="table table-bordered table-striped">
            <thead>
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
                @foreach($contributions as $contribution)
                <tr>
                    <td>{{ $contribution->user->name }} ({{ $contribution->user->points }} pts)</td>
                    <td>
                        {{ $contribution->payload['nombre'] ?? ($contribution->payload['titulo'] ?? ($contribution->payload['interprete'] ?? ($contribution->payload['cancion'] ?? 'Sin título'))) }}
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
                        <span class="badge badge-{{ $badgeColors[$contribution->status] }}">
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
