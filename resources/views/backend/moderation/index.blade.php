@extends('adminlte::page')

@section('title', 'Moderación Editorial')

@section('content_header')
    <h1>Contenidos Pendientes de Revisión</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cola de moderación externa</h3>
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
                    <th>Fecha Envío</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Organización / Usuario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingContent as $content)
                <tr>
                    <td>{{ $content->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge badge-info">{{ $content->content_type }}</span>
                    </td>
                    <td>
                        {{ $content->title }}
                    </td>
                    <td>
                        {{ $content->organization ? $content->organization->name : 'N/A' }}
                        <br>
                        <small>por {{ $content->creator ? $content->creator->name : 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="badge badge-warning">
                            PENDIENTE
                        </span>
                    </td>
                    <td>
                        <!-- Acción de revisión simple (abre en la vista de edición actual) -->
                        <a href="{{ $content->edit_route }}" class="btn btn-sm btn-primary d-inline-block">
                            <i class="fas fa-eye"></i> Revisar
                        </a>

                        <!-- Actions  -->
                        <form action="{{ route('backend.moderation.action') }}" method="POST" class="d-inline-block" onsubmit="return confirm('¿Aprobar contenido?');">
                            @csrf
                            <input type="hidden" name="content_id" value="{{ $content->id }}">
                            <input type="hidden" name="content_type" value="{{ $content->content_type }}">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-sm btn-success" title="Aprobar">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                        </form>

                        <form action="{{ route('backend.moderation.action') }}" method="POST" class="d-inline-block" onsubmit="return confirm('¿Rechazar contenido?');">
                            @csrf
                            <input type="hidden" name="content_id" value="{{ $content->id }}">
                            <input type="hidden" name="content_type" value="{{ $content->content_type }}">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-sm btn-danger" title="Rechazar">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay contenidos pendientes de revisión.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
