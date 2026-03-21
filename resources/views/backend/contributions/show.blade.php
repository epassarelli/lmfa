@extends('adminlte::page')

@section('title', 'Revisar Contribución')

@section('content_header')
    <h1>Revisar Colaboración #{{ $contribution->id }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Comparativa de Datos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($contribution->payload as $key => $value)
                    <div class="col-12 mb-4">
                        <h5 class="text-bold text-uppercase">{{ str_replace('_', ' ', $key) }}</h5>
                        <div class="row">
                            @if($original)
                            <div class="col-md-6">
                                <label class="text-danger">Actual (Producción):</label>
                                <div class="p-3 border bg-light" style="min-height: 200px; max-height: 500px; overflow-y: auto;">
                                    {!! $original->$key ?? 'Sin contenido' !!}
                                </div>
                            </div>
                            @endif
                            <div class="col-md-{{ $original ? '6' : '12' }}">
                                <label class="text-success">Sugerido (Usuario):</label>
                                <div class="p-3 border bg-white shadow-sm" style="min-height: 200px; max-height: 500px; overflow-y: auto; border-left: 4px solid #28a745;">
                                    {!! $value !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="w-100">
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Info del Colaborador</h3>
            </div>
            <div class="card-body">
                <p><strong>Usuario:</strong> {{ $contribution->user->name }}</p>
                <p><strong>Email:</strong> {{ $contribution->user->email }}</p>
                <p><strong>Rango Actual:</strong> {{ $contribution->user->rank }}</p>
                <p><strong>Puntos:</strong> {{ $contribution->user->points }}</p>
                <hr>
                <p><strong>Tipo:</strong> {{ class_basename($contribution->contributable_type) }}</p>
                <p><strong>Acción:</strong> {{ $contribution->contributable_id ? 'Edición de contenido' : 'Alta de contenido nuevo' }}</p>
            </div>
        </div>

        @if($contribution->status == 'pending')
        <div class="card card-success shadow">
            <div class="card-header">
                <h3 class="card-title">Acciones de Moderación</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('backend.contributions.approve', $contribution->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-check-circle"></i> APROBAR Y PUBLICAR
                    </button>
                </form>

                <hr>

                <form action="{{ route('backend.contributions.reject', $contribution->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Motivo de rechazo (opcional, lo verá el usuario):</label>
                        <textarea name="comment" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-times-circle"></i> RECHAZAR
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="alert alert-{{ $contribution->status == 'approved' ? 'success' : 'danger' }}">
            Esta contribución ya está <strong>{{ strtoupper($contribution->status) }}</strong>
            @if($contribution->moderator_comment)
                <p class="mt-2 border-top pt-1">Comentario: {{ $contribution->moderator_comment }}</p>
            @endif
        </div>
        @endif
    </div>
</div>
@stop
