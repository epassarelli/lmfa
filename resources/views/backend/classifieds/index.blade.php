@extends('adminlte::page')
@section('title', 'Moderacion de Avisos Clasificados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Avisos Clasificados</h1>
        <a href="{{ route('backend.classifieds.create') }}" class="btn btn-success btn-sm">+ Crear Aviso</a>
    </div>
@endsection

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <div class="rounded border bg-light p-3 h-100">
                    <div class="small text-muted text-uppercase">Pendientes</div>
                    <div class="h4 mb-0">{{ $statusCounts['pendiente'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="rounded border bg-light p-3 h-100">
                    <div class="small text-muted text-uppercase">Activos</div>
                    <div class="h4 mb-0">{{ $statusCounts['activo'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="rounded border bg-light p-3 h-100">
                    <div class="small text-muted text-uppercase">Rechazados</div>
                    <div class="h4 mb-0">{{ $statusCounts['rechazado'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <x-admin-listing-toolbar
            :action="route('backend.classifieds.index')"
            search-placeholder="Buscar por titulo, ubicacion, categoria o usuario..."
            :sort-options="[
                'created_at' => 'Fecha de alta',
                'title' => 'Titulo',
                'estado' => 'Estado',
                'expiration_date' => 'Vencimiento',
            ]"
        >
            <div class="col-md-3">
                <label for="status" class="small text-muted mb-1">Estado</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected(request('status') === 'pendiente')>Pendientes</option>
                    <option value="activo" @selected(request('status') === 'activo')>Activos</option>
                    <option value="rechazado" @selected(request('status') === 'rechazado')>Rechazados</option>
                </select>
            </div>
        </x-admin-listing-toolbar>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Titulo</th>
                        <th>Categoria</th>
                        <th>Ubicacion</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Vence</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classifieds as $aviso)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $aviso->title }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($aviso->description, 100) }}</div>
                            </td>
                            <td>{{ $aviso->category->name ?? '-' }}</td>
                            <td>{{ $aviso->location ?? '-' }}</td>
                            <td>{{ $aviso->user->name ?? 'Sin usuario' }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pendiente' => 'warning',
                                        'activo' => 'success',
                                        'rechazado' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusClasses[$aviso->estado] ?? 'secondary' }}">
                                    {{ ucfirst($aviso->estado) }}
                                </span>
                            </td>
                            <td>{{ $aviso->expiration_date ? $aviso->expiration_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $aviso->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('backend.classifieds.show', $aviso) }}" class="btn btn-xs btn-primary">Ver</a>
                                <a href="{{ route('backend.classifieds.edit', $aviso) }}" class="btn btn-xs btn-warning">Editar</a>

                                @if($aviso->estado === 'pendiente')
                                    <form action="{{ route('backend.classifieds.approve', $aviso) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success">Aprobar</button>
                                    </form>
                                    <button class="btn btn-xs btn-danger" data-toggle="collapse" data-target="#reject-{{ $aviso->id }}">
                                        Rechazar
                                    </button>
                                @endif

                                <form action="{{ route('backend.classifieds.destroy', $aviso) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">Eliminar</button>
                                </form>

                                @if($aviso->estado === 'pendiente')
                                    <div class="collapse mt-2" id="reject-{{ $aviso->id }}">
                                        <form action="{{ route('backend.classifieds.reject', $aviso) }}" method="POST">
                                            @csrf
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="motivo" class="form-control" placeholder="Motivo opcional">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-danger">Confirmar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay avisos para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white px-0 pb-0 pt-3">
            {{ $classifieds->links() }}
        </div>
    </div>
</div>
@endsection
