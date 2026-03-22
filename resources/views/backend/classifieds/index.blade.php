@extends('adminlte::page')
@section('title', 'Moderación de Avisos Clasificados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Avisos Clasificados</h1>
        <a href="{{ route('backend.classifieds.create') }}" class="btn btn-success btn-sm">+ Crear Aviso</a>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <ul class="nav nav-tabs mb-4" id="classified-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pendientes">
                    ⏳ Pendientes <span class="badge bg-warning text-dark">{{ $pendientes->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#activos">
                    ✅ Activos <span class="badge bg-success">{{ $activos->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#rechazados">
                    ❌ Rechazados <span class="badge bg-danger">{{ $rechazados->count() }}</span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- PENDIENTES --}}
            <div class="tab-pane fade show active" id="pendientes">
                @forelse($pendientes as $aviso)
                    <div class="card mb-3 border-warning">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <h5 class="mb-1">{{ $aviso->title }}</h5>
                                    <small class="text-muted">
                                        📂 {{ $aviso->category->name }}
                                        · 📍 {{ $aviso->location }}
                                        · 👤 {{ $aviso->user->name ?? 'Sin usuario' }}
                                        · 🕐 {{ $aviso->created_at->diffForHumans() }}
                                    </small>
                                    <p class="mt-2 mb-0 text-muted small">{{ Str::limit($aviso->description, 150) }}</p>
                                </div>
                                <div class="col-md-5 text-end mt-3 mt-md-0">
                                    <form action="{{ route('backend.classifieds.approve', $aviso) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success me-1">✅ Aprobar</button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="collapse" data-bs-target="#reject-{{ $aviso->id }}">
                                        ❌ Rechazar
                                    </button>
                                    <div class="collapse mt-2" id="reject-{{ $aviso->id }}">
                                        <form action="{{ route('backend.classifieds.reject', $aviso) }}" method="POST">
                                            @csrf
                                            <div class="input-group">
                                                <input type="text" name="motivo" class="form-control form-control-sm" placeholder="Motivo (opcional)">
                                                <button type="submit" class="btn btn-sm btn-danger">Confirmar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No hay avisos pendientes de revisión. 🎉</p>
                @endforelse
            </div>

            {{-- ACTIVOS --}}
            <div class="tab-pane fade" id="activos">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Usuario</th>
                            <th>Vence</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activos as $aviso)
                            <tr>
                                <td>{{ $aviso->title }}</td>
                                <td>{{ $aviso->category->name }}</td>
                                <td>{{ $aviso->user->name ?? '-' }}</td>
                                <td>{{ $aviso->expiration_date ? $aviso->expiration_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('backend.classifieds.edit', $aviso) }}" class="btn btn-xs btn-warning">Editar</a>
                                    <form action="{{ route('backend.classifieds.destroy', $aviso) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Sin avisos activos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- RECHAZADOS --}}
            <div class="tab-pane fade" id="rechazados">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rechazados as $aviso)
                            <tr>
                                <td>{{ $aviso->title }}</td>
                                <td>{{ $aviso->moderator_comment ?? '-' }}</td>
                                <td>{{ $aviso->user->name ?? '-' }}</td>
                                <td>{{ $aviso->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Sin avisos rechazados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
